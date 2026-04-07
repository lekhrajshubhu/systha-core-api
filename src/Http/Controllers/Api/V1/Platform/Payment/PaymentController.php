<?php

/**
 * THIS INTELLECTUAL PROPERTY IS COPYRIGHT Ⓒ 2020
 * SYSTHA TECH LLC. ALL RIGHT RESERVED
 * -----------------------------------------------------------
 * SALES@SYSTHATECH.COM 
 * 512 903 2202
 * WWW.SYSTHATECH.COM
 * -----------------------------------------------------------
 */


namespace Systha\Core\Http\Controllers\Api\V1\Platform\Payment;


use Error;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Payment;
use Systha\Core\Models\InvoiceHead;
use Systha\Core\Services\StripeService;
use Systha\Core\Models\StripePaymentMethod;

/**
 * @group Platform
 * @subgroup Payments
 */
class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $client = auth('platform')->user();
        try {
            $payments = Payment::where('client_id', $client->id)->latest()->get();
            return response([
                "data" => $payments
            ]);
        } catch (\Throwable $th) {
            return response(["error" => $th->getMessage()], 422);
        }
    }

    public function addPaymentMethod(Request $request)
    {
        $validated = $request->validate([
            "customer_name" => "required",
            "customer_phone" => "required",
            "customer_email" => "required",
            "payment_method_id" => "required|string"
        ]);
        try {
            $client = auth('platform')->user();

            if (!$client || !isset($client->vendor)) {
                throw new \Exception("Unauthorized or invalid contact.");
            }

            $vendor = Vendor::where('vendor_code', $client->vendor->vendor_code)->first();

            if (!$vendor) {
                throw new \Exception("Vendor not found.");
            }

            // You can now safely call your StripeService
            // Assuming StripeService handles storing the customer and attaching the payment method
            $stripeService = app(StripeService::class, [
                'vendor' => $vendor
            ]);

            return $stripeService->addPaymentMethod($validated);
        } catch (\Throwable $th) {
            //throw $th;
            dd($th->getMessage());
        }
    }

    public function makeDefaultPaymentMethod(Request $request, $id)
    {

        try {

            $client = auth('platform')->user();
            StripePaymentMethod::where('client_id', $client->id)->update(["is_default" => 0]);

            $paymentMethod = StripePaymentMethod::find($id);
            $paymentMethod->is_default = 1;
            $paymentMethod->save();

            $paymentMethod->customer()->update([
                "default_payment_method_id" => $paymentMethod->payment_method_id,
            ]);

            return response(["data" => $paymentMethod, "message" => "Default card changed to : " . $paymentMethod->card_last4], 200);
        } catch (\Throwable $th) {
            return response(["message" => $th->getMessage()], 200);
            //throw $th;
        }
    }

    public function updatePaymentMethod(Request $request, $id)
    {
        $validated = $request->validate([
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $client = auth('platform')->user();

            $paymentMethod = StripePaymentMethod::where('id', $id)
                ->where('client_id', $client->id)
                ->where('is_deleted', 0)
                ->firstOrFail();

            if (array_key_exists('is_active', $validated)) {
                $paymentMethod->is_active = (int) $validated['is_active'];
            }

            if (array_key_exists('is_default', $validated)) {
                $isDefault = (bool) $validated['is_default'];

                if ($isDefault) {
                    StripePaymentMethod::where('client_id', $client->id)->update(['is_default' => 0]);
                    $paymentMethod->is_default = 1;
                } elseif ((int) $paymentMethod->is_default === 1) {
                    $paymentMethod->is_default = 0;
                }
            }

            $paymentMethod->save();

            $defaultPaymentMethod = StripePaymentMethod::where('client_id', $client->id)
                ->where('is_deleted', 0)
                ->where('is_default', 1)
                ->first();

            if ($paymentMethod->customer) {
                $paymentMethod->customer()->update([
                    'default_payment_method_id' => $defaultPaymentMethod?->payment_method_id,
                ]);
            }

            return response([
                'data' => $paymentMethod->fresh(),
                'message' => 'Card updated successfully.',
            ], 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 422);
        }
    }

    public function deletePaymentMethod(Request $request, $id)
    {
        try {
            $client = auth('platform')->user();

            $paymentMethod = StripePaymentMethod::where('id', $id)
                ->where('client_id', $client->id)
                ->firstOrFail();

            $stripeCustomer = $paymentMethod->customer;
            $wasDefault = (int) $paymentMethod->is_default === 1;

            $paymentMethod->is_deleted = 1;
            $paymentMethod->deleted_at = now();
            $paymentMethod->save();
            

            if ($wasDefault) {
                $nextDefault = StripePaymentMethod::where('client_id', $client->id)->first();

                StripePaymentMethod::where('client_id', $client->id)->update(['is_default' => 0]);
                if ($nextDefault) {
                    $nextDefault->update(['is_default' => 1]);
                }

                if ($stripeCustomer) {
                    $stripeCustomer->update([
                        'default_payment_method_id' => $nextDefault?->payment_method_id,
                    ]);
                }
            }

            return response([
                'message' => 'Card deleted successfully.',
            ], 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 422);
        }
    }
}
