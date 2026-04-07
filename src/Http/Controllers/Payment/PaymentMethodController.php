<?php

namespace Systha\Core\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Systha\Core\Models\Vendor;
use Systha\Core\Services\StripeService;
use Systha\Core\Http\Controllers\BaseController;

class PaymentMethodController extends BaseController
{

    public function addPaymentMethod(Request $request)
    {
        $validated = $request->validate([
            "customer_name" => "required",
            "customer_phone" => "required",
            "customer_email" => "required",
            "payment_method_id" => "required|string",
            "vendor_code" =>"nullable|string|exists:vendors,vendor_code"
        ]);
        try {
            
            
            if($validated['vendor_code']){
                $vendor = Vendor::where('vendor_code', $validated['vendor_code'])->first();
            }else{
                $vendor = $this->vendor;
            }
            
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
}
