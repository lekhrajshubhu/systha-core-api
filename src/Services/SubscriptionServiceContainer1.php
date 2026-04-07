<?php

namespace Systha\Core\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Systha\Core\Helpers\Formatter;
use Systha\Core\Lib\Subscription\StripeSub;
use Systha\Core\Models\Address;
use Systha\Core\Models\Appointment;
use Systha\Core\Models\AppointmentService;
use Systha\Core\Models\ClientModel;
use Systha\Core\Models\EmailTemplate;
use Systha\Core\Models\PackageSubscription;
use Systha\Core\Models\PackageType;
use Systha\Core\Models\SubscriptionCart;
use Systha\Core\Models\SubscriptionPayment;


class SubscriptionServiceContainer1
{
    protected $stripe;
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        // $this->stripe = $stripe;
        $this->messageService = $messageService;
    }

    public function subscribe(PackageType $packageType, ClientModel $client, Address $address, $stripeToken, $startDate, $startTime)
    {


        DB::beginTransaction();
        try {

            // local package subscription
            $packageSubscription = $this->createPackageSubscription($packageType, $client, $startDate, $startTime);


            // dd($packageSubscription);
            // intialize stripe
            $this->stripe = new StripeSub($packageType->vendor);


            // dd($this->stripe);

            // create stripe customer
            $customer = $this->stripe->createCustomer([
                'name' => $client->fname . " " . $client->lname,
                'email' => $client->email,
                'phone' => $client->phone_no,
            ]);

            // dd($customer);
            // stripe attach stripeToken to stripe customer
            $card = $this->stripe->createCard($stripeToken, $customer->id);
            dd($card);
            // prepare data form stripe subscription payment
            $sub_data = [
                'customer_id' => $customer->id,
                'card_id' => $card->id,
                'plan_id' => $packageType->stripe_price_id,
            ];

            dd($sub_data);
            // save before payment for logs 
            $subscriptionCart = $packageSubscription->cart()->create($sub_data);
            $subscriptionCart->package_subscription_id = $packageSubscription->id;
            $subscriptionCart->save();

            dd($subscriptionCart);
            $stripeSubscription = $this->stripe->createPackageSubscription($sub_data, $startDate, $packageType);
   
            dd($stripeSubscription);
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

            $this->createSubscriptionPayment($packageSubscription, $packageType, $subscriptionCart, $card);

            $packageServices = $packageType->package->services;

            // header('Access-Control-Allow-Origin:*');

            $formattedServices = [];
            foreach ($packageServices as $service) {
                $formattedServices[] = [
                    "service_id" => $service->service_id,
                    "service_name" => $service->service_name,
                    "price" => $service->price,
                ];
            }

            // $sentEmail = $this->sendEmail($packageSubscription, $stripeSubscription);

            // $message = "<div>
            //         <p style='margin-bottom:0;'>Thank you for your subscription</p>
            //         <p> <span class='subs' data-id='" . e($packageSubscription->id) . "'>#" . e($packageSubscription->subs_no) . "</span> - View Subscription Details</p>
            //     </div>";
            // $this->messageService->sendMessage($packageSubscription, $packageSubscription->client, $packageSubscription->vendor, $message);

            DB::commit();

            return [
                'info' => $stripeSubscription,
                'data' => $packageSubscription,
                'card' => $card,
                'message' => 'Package Subscribbed Successfully.',
                // "email" => $sentEmail,
                // "appointment" => $appointment,
            ];

        } catch (Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            return response()->json([
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine()
            ], 500);
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
                "state" => "publish",
            ]);
        }
        $subscriptionPayment->appointment_id = $appointment->id;
        $subscriptionPayment->save();

        $payment = $this->payment($appointment, $subscriptionPayment);
        $this->invoice($packageSubscription, $appointment);
        return $appointment;
    }

    public function payment($appointment, $paymentDetails)
    {
        return $appointment->payment()->create([
            'table_id' => $appointment['id'],
            'table_name' => 'appointments',
            'payment_type' => $paymentDetails->payment_type,
            'gateway' => $paymentDetails->gateway,
            'cr_last_4' => $paymentDetails->cr_last_4,
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
        $invoice = $appointment->invoices()->create(
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
            $invoice->items()->create([
                "service_id" => $service->service_id,
            ]);
        }
    }

    private function createSubscriptionPayment(PackageSubscription $subscription, PackageType $packageType, SubscriptionCart $cart, $card)
    {
        // $payment =  $subscription->payments()->save(new SubscriptionPayment([
        //     'gateway' => 'stripe',
        //     'cart_id' => $cart->id,
        //     'payment_id' => $card->id,
        //     'payment_type' => $card->brand,
        //     'cr_last_4' => $card->last4,
        //     'cr_exp_month' => $card->exp_month,
        //     'cr_exp_year' => $card->exp_year,
        //     'cr_cardholder_name' => $card->name ?? $subscription->client->fname . " " . $subscription->client->lname,
        //     'cr_billing_zip' => $card->address_zip,
        //     'convenience_fee' => $subscription->convenience_fee,
        //     'price' => $subscription->price,
        //     'amount' => $subscription->price,
        //     'description' => 'subscription payment',
        //     'is_active' => 1,
        //     'stripe_customer_id' => $cart->customer_id,
        //     'stripe_product_id' => $cart->packageSubscription->package->stripe_product_id,
        //     'stripe_plan_id' => $cart->plan_id,
        //     'status' => 'success',
        // ]));
        $payment =  $subscription->payments()->create([
            'gateway' => 'stripe',
            'cart_id' => $cart->id,
            'payment_id' => $card->id,
            'payment_type' => $card->brand,
            'cr_last_4' => $card->last4,
            'cr_exp_month' => $card->exp_month,
            'cr_exp_year' => $card->exp_year,
            // 'transaction_id' => $card->id,
            'ref_no' => $card->id,
            'cr_cardholder_name' => $card->name ?? $subscription->client->fname . " " . $subscription->client->lname,
            'cr_billing_zip' => $card->address_zip,
            // 'convenience_fee' => $subscription->convenience_fee,
            'price' => $subscription->price,
            'amount' => $subscription->price,
            'description' => 'subscription payment',
            'is_active' => 1,
            // 'stripe_customer_id' => $cart->customer_id,
            // 'stripe_product_id' => $cart->packageSubscription->package->stripe_product_id,
            // 'stripe_plan_id' => $cart->plan_id,
            // 'status' => 'success',
        ]);


        $this->createInvoice($subscription, $packageType, $payment);
    }

    public function createInvoice(PackageSubscription $packageSubscription, PackageType $packageType, SubscriptionPayment $payment)
    {
        $invoice = $packageSubscription->invoices()->create([
            "paid_amount" => $packageSubscription->price,
            "client_id" => $packageSubscription->client_id,
            "vendor_id" => $packageSubscription->vendor_id,
            "amount" => $packageSubscription->price,
            "package_amount" => $packageSubscription->price,
            "is_fully_paid" => 1,
            "type" => "credit",
            "payment_id" => $payment->id,
        ]);

        $invoice->items()->create([
            "item_name" => $packageType->package->name . " : " . $packageType->description,
            "item_price" => $packageType->amount,
            "quantity" => 1,
            "amount" => $packageType->amount,
            "vendor_id" => $packageType->vendor_id,
        ]);
    }



    public function sendEmail(PackageSubscription $packageSubscription, $subscription)
    {

        // $templateService = app(EmailTemplateService::class);
        // $client = $packageSubscription->client;
        // $vendor = $packageSubscription->vendor;

        // if (!$client || empty($client->email)) {
        //     Log::warning('Missing client or email for packageSubscription ID: ' . $packageSubscription->id);
        //     return;
        // }

        // // Prepare service names
        // $serviceNames = $packageSubscription->package->services->pluck('service_name')->toArray();
        // $joinedServiceNames = implode(', ', $serviceNames);

        // // Stripe price object
        // $stripePrice = $subscription->items->data[0]->price ?? null;

        // // Prepare template variables (local first, Stripe fallback)
        // $data = [
        //     'customer_name'        => $client->fullName,
        //     'customer_phone'       => $client->phone_no,
        //     'customer_email'       => $client->email,
        //     'plan_name'            => $stripePrice->metadata->product_name ?? $packageSubscription->package->name,
        //     'start_date'           => \Carbon\Carbon::createFromTimestamp($subscription->current_period_start)->format('M d, Y'),
        //     'next_billing_date'    => \Carbon\Carbon::createFromTimestamp($subscription->current_period_end)->format('M d, Y'),
        //     'price'                => $stripePrice ? Formatter::priceFormat($stripePrice->unit_amount / 100) : Formatter::priceFormat($packageSubscription->price),
        //     'billing_cycle'        => $stripePrice->recurring->interval ?? $packageSubscription->billing_cycle,
        //     'payment_method'       => $packageSubscription->payment_method ?? 'Stripe',
        //     'company_name'         => $vendor->name,
        //     'company_website'      => env('APP_WEBSITE', config('app.url')),
        //     'company_email'        => $vendor->contact->email,
        // ];

        // $emailTemplate = EmailTemplate::where([
            
        //     'code' => 'subscription_confirmed',
        //     "vendor_id" => $vendor->id,
        // ])->first();

        // if (!$emailTemplate) {
        //     Log::warning('Email template not found: subscription_confirmed');
        //     return;
        // }

        // try {
        //     $rendered = $templateService->load($emailTemplate, $data)->render();

        //     $msgService = new MessageService();

        //     $msgService->sendMessage($packageSubscription, $vendor, $client, $rendered['msgContent']);

        //     $mailService =  app(VendorMailService::class, ['vendor' => $vendor]);

        //     // Email to customer
        //     $emailToCustomer = $mailService->send([
        //         'from_email'  => $vendor->contact->email,
        //         'from_name'   => $vendor->name,
        //         'to_email'    => $client->email,
        //         'to_name'     => $client->fname,
        //         'subject'     => $rendered['subject'],
        //         'message'     => $rendered['content'],
        //         'table_name'  => 'package_subscriptions',
        //         'table_id'    => $packageSubscription->id,
        //     ]);

        //     // Email to vendor
        //     $emailToVendor = $mailService->send([
        //         'from_email'  => 'noreply@email.com',
        //         'from_name'   => $vendor->name,
        //         'to_email'    => $vendor->contact->email ?? 'sales@example.com',
        //         'to_name'     => $vendor->name,
        //         'subject'     => $rendered['vendor_subject'],
        //         'message'     => $rendered['vendor_content'],
        //         'table_name'  => 'package_subscriptions',
        //         'table_id'    => $packageSubscription->id,
        //     ]);


        //     return [
        //         'emailToCustomer' => $emailToCustomer,
        //         'emailToVendor'   => $emailToVendor,
        //     ];
        // } catch (\Throwable $e) {
        //     // header('Access-Control-Allow-Origin:*');
        //     dd($e);

        //     return response([
        //         'error' => $e->getMessage(),
        //         'line'  => $e->getLine(),
        //         'file'  => $e->getFile(),
        //     ], 500);
        // }
    }
}
