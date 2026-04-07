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


namespace Systha\Core\Http\Controllers\Api\V1\ContactClient\Payment;


use Error;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Payment;
use Systha\Core\Models\InvoiceHead;
use Systha\Core\Services\StripeService;
use Systha\Core\Models\StripePaymentMethod;

/**
 * @group Contacts
 * @subgroup Payments
 */
class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $contact = auth('contacts')->user();
        try {
            $payments = Payment::where('client_id', $contact->table_id)->latest()->get();
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
            $contact = auth('contacts')->user();

            if (!$contact || !isset($contact->vendor_code)) {
                throw new \Exception("Unauthorized or invalid contact.");
            }

            $vendor = Vendor::where('vendor_code', $contact->vendor_code)->first();

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

            $contact = auth('contacts')->user();
            StripePaymentMethod::where('client_id', $contact->table_id)->update(["is_default" => 0]);

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
}
