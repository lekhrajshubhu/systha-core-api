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


namespace Systha\Core\Http\Controllers\Api\V1\Tenant\Appointment;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Payment;
use Systha\Core\Models\Appointment;
use Systha\Core\Services\StripeService;
use Systha\Core\Models\StripePaymentMethod;
use Systha\tempcleaning\ServiceContainer\AppointmentServiceContainer;


/**
 * @group Tenant
 * @subgroup Appointments
 */
class AppointmentPaymentController extends Controller
{
    public function createPaymentIntent(Request $request, $appointmentId)
    {
        // Validate request if needed (e.g. email)
        $validated = $request->validate([
            'customer_email' => 'required|email',
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'stripe_customer_id' => 'nullable|string',
            'payment_method_id' => 'nullable|string',
        ]);


        // Find appointment
        $appointment = Appointment::findOrFail($appointmentId);

        // Load related vendor (assuming appointment has vendor_id)
        $vendor = Vendor::findOrFail($appointment->vendor_id);

        // Instantiate StripeService with vendor for API key
        $stripeService = app(StripeService::class, ['vendor' => $vendor]);


        // Create PaymentIntent using appointment total amount and customer email
        $validated["amount"] = $appointment->total_amount;
        // dd($validated);

        $paymentIntent = $stripeService->createPaymentIntent($validated);


        // Return client secret for frontend Stripe.js payment confirmation
        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
            'message' => 'Payment intent created successfully',
        ]);
    }

    public function storeCardPayment(Request $request, $appointmentId)
    {
        $validated = $request->validate([
            "appointment_id" => 'required|integer',
            "payment_intent_id" => "required|string",
            "payment_method_id" => "required|string",
            "amount" =>  "required|numeric",
        ]);


        try {
            $appointment = Appointment::findOrFail($appointmentId);


            $stripeService = app(StripeService::class, ["vendor" => $appointment->vendor]);


            $paymentMethod = $stripeService->getCardInfo($validated["payment_method_id"]);


            $card = $paymentMethod->card;

            $cardName = $paymentMethod->billing_details->name;


            $paymentMethod = StripePaymentMethod::where('payment_method_id', $validated["payment_method_id"])->first();


            $payment = Payment::create([
                'table_name'       => 'appointments',
                'table_id'         => $appointmentId,
                'transaction_id'   => $validated['payment_intent_id'],
                'ref_no'   => $validated['payment_intent_id'],
                "amount" => $validated["amount"],
                'gateway'          => 'stripe', // payment gateway name
                'card_last_name'         => $cardName,
                'cr_last4'         => $card['last4'] ?? null,
                'cr_exp_month'     => $card['exp_month'] ?? null,
                'cr_exp_year'      => $card['exp_year'] ?? null,
                'payment_type'       => $card['brand'] ?? null,  // if you have brand column
                'client_id'        => $appointment->client_id,
                'vendor_id'        => $appointment->vendor_id,
                'stripe_payment_method_id' => $paymentMethod->id,
                'created_at'       => now(),
            ]);

            $appointment->is_paid = 1;
            $appointment->save();

            $appointmentService = app(AppointmentServiceContainer::class);
            $appointmentService->sendPaymentConfirmationEmail($appointment, $payment);

            return response()->json(['success' => true, 'payment_info' => $card]);
        } catch (\Exception $e) {
            Log::error('storeCardPayment error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
