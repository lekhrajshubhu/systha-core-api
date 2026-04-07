<?php

namespace Systha\Core\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Systha\Core\Models\Client;
use Systha\Core\Models\Appointment;
use Systha\Core\Models\InvoiceHead;
use Systha\Core\Models\PackageType;
use Systha\Core\Models\SubscriptionCart;
use Systha\Core\Services\MessageService;
use Systha\Core\Models\AppointmentService;
use Systha\Core\Lib\Subscription\StripeSub;
use Systha\Core\Models\PackageSubscription;
use Systha\Core\Models\SubscriptionPayment;


class SubscriptionService
{
    protected $stripe;
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function subscribe(PackageType $packageType, Client $client, $stripeToken, $startDate, $startTime)
    {

        DB::beginTransaction();
        try {


            // local package subscription
            $packageSubscription = $this->createPackageSubscription($packageType, $client, $startDate, $startTime);

            $this->stripe = new StripeSub($packageType->vendor);
   

            $customer = $this->stripe->createCustomer([
                'name' => $client->fname . " " . $client->lname,
                'email' => $client->email,
                'phone' => $client->phone_no,
            ]);

            // stripe attach stripeToken to stripe customer
            $card = $this->stripe->createCard($stripeToken, $customer->id);

            // prepare data form stripe subscription payment
            $sub_data = [
                'customer_id' => $customer->id,
                'card_id' => $card->id,
                'plan_id' => $packageType->stripe_price_id,
            ];

            // save before payment for logs 
            $subscriptionCart = $packageSubscription->cart()->create($sub_data);
            $subscriptionCart->package_subscription_id = $packageSubscription->id;
            $subscriptionCart->save();

            $stripeSubscription = $this->stripe->createPackageSubscription($sub_data, $startDate, $packageType);

            if (!isset($stripeSubscription["id"])) {
                $subscriptionCart->subscription = $stripeSubscription->id;
                $subscriptionCart->status = "failed";
                $subscriptionCart->remarks = "Payment Failed";
                $subscriptionCart->save();
                throw new Exception("Oops!! Failed to checkout!!");
            }

            $subscriptionCart->subscription = $stripeSubscription->id;
            $subscriptionCart->status = "success";
            $subscriptionCart->remarks = "Payment Successful";
            $subscriptionCart->save();

            $subscriptionPayment = $this->createSubscriptionPayment($packageSubscription, $subscriptionCart, $card);

            $appointment = $this->createAppoinment($packageSubscription, $subscriptionPayment);

            $message = "<div>
                    <p style='margin-bottom:0;'>Thank you for your subscription</p>
                    <p class='subs' data-id='" . e($packageSubscription->id) . "'>#" . e($packageSubscription->subs_no) . " - View Subscription Details</p>
                </div>";
            $this->messageService->sendMessage($packageSubscription, $packageSubscription->client, $packageSubscription->vendor, $message);

            DB::commit();

            return [
                'info' => $stripeSubscription,
                'data' => $packageSubscription,
                'card' => $card,
                'message' => 'Package Subscribbed Successfully.'
            ];

        } catch (Exception $e) {
            DB::rollBack();
        
            throw $e;
            // return response()->json([
            //     "message" => $e->getMessage(),
            //     "file" => $e->getFile(),
            //     "line" => $e->getLine()
            // ], 500);
        }
    }


    private function createPackageSubscription($packageType, $client, $startDate, $startTime)
    {
        return PackageSubscription::create([
            'state' => 'publish',
            'start_date' => $startDate,
            'preferred_time' => $startTime,
            'status' => 'new',

            'package_id' => $packageType->package_id,
            'package_type_id' => $packageType->id,
            'vendor_id' => $packageType->vendor_id,
            'price' => $packageType->amount,
            'client_id' => $client->id,

            'is_active' => 1,
        ]);
    }

    public function createAppoinment(PackageSubscription $packageSubscription, SubscriptionPayment $subscriptionPayment)
    {
        $appointment = Appointment::create([
            'client_id' => $packageSubscription->client_id,
            'vendor_id' => $packageSubscription->vendor_id,
            'start_date' => Carbon::parse($packageSubscription->start_date)->format('Y-m-d'),
            'start_time' => $packageSubscription->preferred_time,
            'status' => 'booked',
            'state' => 'publish',
            'subscription_id' => $packageSubscription->id,
            'is_paid' => 1,
            'address_id' => $packageSubscription->client->address->id,
        ]);
        foreach ($packageSubscription->package->packageServices as $service) {
            AppointmentService::create([
                "appointment_id" => $appointment->id,
                "service_id" => $service->service_id,
                "price" => $service->service->price,
                "state" =>"publish",
            ]);
        }
        $subscriptionPayment->appointment_id = $appointment->id;
        $subscriptionPayment->save();

        $payment = $this->payment($appointment,$subscriptionPayment);
        $invoice = $this->invoice($packageSubscription, $appointment);
        return $appointment;
    }

    public function payment($appointment, $paymentDetails){
        return $appointment->payment()->create([
            'table_id' => $appointment['id'],
            'table_name' => 'appointments',
            'payment_type' => $paymentDetails->payment_type,
            'gateway' => $paymentDetails->gateway,
            'cr_last4' => $paymentDetails->cr_last_4,
            'cr_exp_month' => $paymentDetails->cr_exp_month,
            'cr_exp_year' => $paymentDetails->cr_exp_year,
            'transaction_id' => $paymentDetails['id'],
            'amount' => $paymentDetails->amount,
            'ref_no' => $paymentDetails->payment_id,
            'card_last_name' => $paymentDetails->cr_cardholder_name,
            'is_partial' => 0,
        ]);
    }


    protected function invoice(PackageSubscription $packageSubscription, $appointment)
    {
        $invoice = InvoiceHead::create(
            [
                'table_id' => $appointment->id,
                'table_name' => 'appointments',
                'client_id' => $appointment->client_id,
                'vendor_id' => $appointment->vendor_id,
                'amount' => $packageSubscription->price,
                'paid_amount' => $packageSubscription->price,
                'type' => "debit"
            ]
        );
        $this->invoiceItems($invoice, $packageSubscription);
        return $invoice;
    }
    protected function invoiceItems($invoice, $packageSubscription)
    {
        foreach ($packageSubscription->package->packageServices as $service) {
            $test = $invoice->items()->create([
                "service_id" => $service->service_id,
                "amount" => $service->price,
                "quantity"=>1,
            ]);
        }
    }

    private function createSubscriptionPayment(PackageSubscription $subscription, SubscriptionCart $cart, $card)
    {
        return $subscription->payments()->save(new SubscriptionPayment([
            'gateway' => 'stripe',
            'cart_id' => $cart->id,
            // 'invoice_id' => $invoice->id,
            // 'invoice_number' => $invoice->invoice_no,
            'payment_id' => $card->id,
            'payment_type' => $card->brand,
            'cr_last_4' => $card->last4,
            'cr_exp_month' => $card->exp_month,
            'cr_exp_year' => $card->exp_year,
            'cr_cardholder_name' => $card->name ?? $subscription->client->fname . " " . $subscription->client->lname,
            'cr_billing_zip' => $card->address_zip,
            'convenience_fee' => $subscription->convenience_fee,
            'price' => $subscription->price,
            'amount' => $subscription->price,
            'description' => 'subscription payment',
            'is_active' => 1,
            'stripe_customer_id' => $cart->customer_id,
            'stripe_product_id' => $subscription->package->stripe_product_id,
            'stripe_plan_id' => $cart->plan_id,
            'status' => 'success',
        ]));
    }
}
