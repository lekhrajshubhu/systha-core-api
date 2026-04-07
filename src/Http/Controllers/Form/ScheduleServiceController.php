<?php

namespace Systha\Core\Http\Controllers\Form;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Payment;
use Systha\Core\Models\Appointment;
use Systha\Core\Services\ClientService;
use Systha\Core\Services\StripeService;
use Systha\Core\Services\InquiryService;
use Systha\Core\Models\StripePaymentMethod;
use Systha\Core\Services\ServiceRequestBuilder;
use Systha\Core\Http\Controllers\BaseController;
use Systha\Core\Http\Requests\ScheduleServiceRequest;
use Systha\Core\Services\AppointmentServiceContainer;

class ScheduleServiceController extends BaseController
{
    protected $inquiryService;
    protected $clientService;
    protected $appointmentServiceContainer;
    protected $serviceRequestBuilder;
    public $template, $vendor;

    public function __construct(
        InquiryService $inquiryService,
        ClientService $clientService,
        AppointmentServiceContainer $appointmentServiceContainer,
        ServiceRequestBuilder $serviceRequestBuilder
    ) {
        parent::__construct(); // ✅ Call BaseController constructor
        $this->inquiryService = $inquiryService;
        $this->clientService = $clientService;
        $this->appointmentServiceContainer = $appointmentServiceContainer;
        $this->serviceRequestBuilder = $serviceRequestBuilder;
    }

    public function storeSchedule(ScheduleServiceRequest $request)
    {
        $validated = $request->validated();


        try {
            $appointment = DB::transaction(function () use ($validated, $request) {

                if($validated['vendor_code']){
                    $vendor = Vendor::where('vendor_code', $validated['vendor_code'])->first();
                }else{
                    $vendor = $this->vendor;
                }

                if (!$vendor) {
                    throw new \Exception("Vendor not found.");
                }


                $result = $this->serviceRequestBuilder->build($request, $validated, $vendor);


                return $this->appointmentServiceContainer->storeAppointment([
                    'client_id' => $result['client']->id,
                    'vendor_id' => $result['vendor']->id,
                    'address_id' => $result['address']->id,
                    'preferred_date' => $validated['preferred_date'],
                    'preferred_time' => $validated['preferred_time'],
                    'is_emergency' => $validated['is_emergency'],
                    'plan_id' => null,
                    'is_recurring' => 0,
                    'recurring_frequency' => null,
                    'service_list' => $result['selectedServices'],
                ]);
            });

            // Load related vendor (assuming appointment has vendor_id)
            $vendor = Vendor::findOrFail($appointment->vendor_id);

            // Instantiate StripeService with vendor for API key
            $stripeService = app(StripeService::class, ['vendor' => $vendor]);

            // Create PaymentIntent using appointment total amount and customer email
            $validated["amount"] = $appointment->total_amount;

            // If you want to use the StripeService to create a payment intent
            $paymentIntent = $stripeService->createPaymentIntent($validated);



            // Extract payment intent to check for SCA requirement
            // $paymentIntent = $subscription->latest_invoice->payment_intent ?? null;
            $requiredAction = $paymentIntent && $paymentIntent->status === 'requires_confirmation';
            // $appointment->status = $paymentIntent->status;
            // $appointment->save();

            if (!$requiredAction) {
                $this->storeCardPayment($appointment, $validated["payment_method_id"], $paymentIntent->id);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'appointment' => $appointment,
                'status' => $appointment->status,
                'client_secret' => $paymentIntent->client_secret ?? null,
                'requires_action' => $paymentIntent && $paymentIntent->status === 'requires_confirmation',
                'message' => 'Successful',
            ]);


            // $temp = view('core::website.forms._schedule_service_success', compact('appointment'))->render();

            // return response()->json([
            //     "message" => "Appointment Created",
            //     "appointment_id" => $appointment->id,
            //     'paymentIntent' => $paymentIntent,
            //     // "temp" => $temp,
            // ], 200);
        } catch (\Throwable $th) {

            return response()->json([
                "error" => true,
                "message" => $th->getMessage(),
                "line" => $th->getLine(),
                "file_name" => $th->getFile(),
            ], 422);
        }
    }

    public function storeCardPayment(Appointment $appointment, $paymentMethodId, $paymentIntentId)
    {

        try {



            $stripeService = app(StripeService::class, ["vendor" => $appointment->vendor]);


            $paymentMethod = $stripeService->getCardInfo($paymentMethodId);


            $card = $paymentMethod->card;

            $cardName = $paymentMethod->billing_details->name;


            $paymentMethod = StripePaymentMethod::where('payment_method_id', $paymentMethodId)->first();


            $payment = Payment::create([
                'table_name'       => $appointment->getTable(),
                'table_id'         => $appointment->id,
                'transaction_id'   => $paymentIntentId,
                'ref_no'   => $paymentIntentId,
                "amount" => $appointment["total_amount"],
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
            // $appointment->status = 'active';
            $appointment->is_paid = 1;
            $appointment->save();

            $temp = view($this->viewPath . '::components._form_partials._schedule_service_success', compact('appointment'))->render();

            return response()->json(['success' => true, 'payment_info' => $card, "payment" => $payment,"temp"=>$temp], 200);
        } catch (\Exception $e) {
            Log::error('storeCardPayment error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeAppointmentPayment(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|integer',
            'payment_intent_id' => 'required|string',
            'payment_method_id' => 'required|string',
            'amount' =>  'required|numeric',
        ]);

        $appointment = Appointment::findOrFail($validated['appointment_id']);

        return $this->storeCardPayment($appointment, $validated['payment_method_id'], $validated['payment_intent_id']);
    }
}
