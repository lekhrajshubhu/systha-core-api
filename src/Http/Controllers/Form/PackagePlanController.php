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

namespace Systha\Core\Http\Controllers\Form;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Payment;
use Systha\Core\Models\PackageType;
use Systha\Core\Services\ClientService;
use Systha\Core\Services\StripeService;
use Systha\Core\Services\MessageService;
use Systha\Core\Lib\Subscription\StripeSub;
use Systha\Core\Models\PackageSubscription;
use Systha\Core\Models\StripePaymentMethod;
use Systha\Core\Http\Controllers\BaseController;
use Systha\Core\Services\SubscriptionServiceContainer;

class PackagePlanController extends BaseController
{
    protected $clientService;

    protected $messageService;

    public function __construct(ClientService $clientService, MessageService $messageService)
    {
        parent::__construct();
        $this->clientService = $clientService;
        $this->messageService = $messageService;
    }

    public function createPaymentIntent(Request $request, $planId)
    {
        $validated = $request->validate([
            'customer_email' => 'required|email',
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'stripe_customer_id' => 'nullable|string',
            'payment_method_id' => 'nullable|string',
            'preferred_date' => 'required',
            'preferred_time' => 'required',
            'address' => 'required',
            'contact' => 'required',
            'vendor_code' => 'nullable|string|exists:vendors,vendor_code',
        ]);


        if($validated['vendor_code']){
            $vendor = Vendor::where('vendor_code', $validated['vendor_code'])->first();
        }else{
            $vendor = $this->vendor;
        }
        
        if (!$vendor) {
            throw new \Exception("Vendor not found.");
        }


        // Instantiate StripeService with vendor for API key
        $stripeService = app(StripeService::class, ['vendor' => $vendor]);

        $plan = PackageType::find($planId);

        // Create PaymentIntent using appointment total amount and customer email
        $validated['amount'] = $plan['amount'];

        $param = [
            'priceId' => $plan->stripe_price_id,
            'customerId' => $validated['stripe_customer_id'],
            'paymentMethodId' => $validated['payment_method_id'],
        ];

        DB::beginTransaction();
        try {

            $subscription = $stripeService->createSubscription($param);
            $packageSubscription = $this->storePackageSubscription($request, $plan, $subscription);

            if (! $subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create subscription. Check logs for details.',
                ], 500);
            }

            // Extract payment intent to check for SCA requirement
            $paymentIntent = $subscription->latest_invoice->payment_intent ?? null;
            $packageSubscription->status = $subscription->status;
            $packageSubscription->save();

            $requiredAction = $paymentIntent && $paymentIntent->status === 'requires_action';

            if ($paymentIntent && $paymentIntent->status === 'succeeded') {
                $this->storeCardPayment($packageSubscription, $validated['payment_method_id'], $paymentIntent->id);
            }


            DB::commit();

            $packagePlan = PackageType::find($packageSubscription->package_type_id);
            $packagePlan->load('package');


            $temp = view($this->viewPath . '::components._form_partials._subscription_success', compact('packageSubscription','packagePlan'))->render();

            return response()->json([
                'success' => true,
                'stripeSubscription' => $subscription,
                'packageSubscription' => $packageSubscription,
                'temp' => $temp,
                'status' => $subscription->status,
                'client_secret' => $paymentIntent->client_secret ?? null,
                'requires_action' => $paymentIntent && $paymentIntent->status === 'requires_action',
                'message' => 'Subscription is successful',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file_name' => $th->getFile(),
            ], 500);
        }
    }
    // public function storePackageSubscription(Request $request, $packagePlan, $subscription)
    // {

    //     $result = $this->clientService->createClient([
    //         'contact' => $request['contact'],
    //         'address' => $request['address'],
    //     ], $this->vendor);

    //     $client = $result["client"];
    //     $address = $result["address"];

    //     return PackageSubscription::create([
    //         'state' => 'publish',
    //         'start_date' => $request->preferred_date,
    //         'preferred_time' => $request->preferred_time,
    //         'status' => 'new',
    //         'package_id' => $packagePlan->package_id,
    //         'package_type_id' => $packagePlan->id,
    //         'vendor_id' => $packagePlan->vendor_id,
    //         'address_id' => $address->id,
    //         'price' => $packagePlan->amount,
    //         'client_id' => $client->id,
    //         'is_active' => 1,
    //     ]);
    // }

    public function storePackageSubscription(Request $request, $packagePlan, $subscription)
    {
        $result = $this->clientService->createClient([
            'contact' => $request['contact'],
            'address' => $request['address'],
        ], $this->vendor);

        $client = $result['client'];
        $address = $result['address'];

        return PackageSubscription::create([
            'state' => 'publish',
            'start_date' => $request->preferred_date,
            'preferred_time' => $request->preferred_time,
            'status' => 'new',
            'package_id' => $packagePlan->package_id,
            'package_type_id' => $packagePlan->id,
            'vendor_id' => $packagePlan->vendor_id,
            'address_id' => $address->id,
            'price' => $packagePlan->amount,
            'client_id' => $client->id,
            'is_active' => 1,

            // Stripe subscription related fields
            'stripe_subs_id' => $subscription->id ?? null,
            'stripe_customer_id' => $subscription->customer ?? null,
            'stripe_payment_method_id' => $subscription->default_payment_method ?? null,
            'stripe_invoice_id' => $subscription->latest_invoice->id ?? null,
            'stripe_subscription_status' => $subscription->status ?? null,

            'stripe_plan_id' => $subscription->plan->product ?? null,
            'stripe_price_id' => $subscription->plan->id ?? null,

            // Convert timestamps using Carbon or null
            'trial_start' => isset($subscription->trial_start) ? Carbon::createFromTimestamp($subscription->trial_start) : null,
            'trial_end' => isset($subscription->trial_end) ? Carbon::createFromTimestamp($subscription->trial_end) : null,
            'current_period_start' => isset($subscription->current_period_start) ? Carbon::createFromTimestamp($subscription->current_period_start) : null,
            'current_period_end' => isset($subscription->current_period_end) ? Carbon::createFromTimestamp($subscription->current_period_end) : null,
        ]);
    }

    public function storeSubscriptionPayment(Request $request)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|integer',
            'payment_intent_id' => 'required|string',
            'payment_method_id' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        $packageSubscription = PackageSubscription::findOrFail($validated['subscription_id']);

        return $this->storeCardPayment($packageSubscription, $validated['payment_method_id'], $validated['payment_intent_id']);
    }

    public function storeCardPayment(PackageSubscription $packageSubscription, $paymentMethodId, $paymentIntentId)
    {

        try {

            $stripeService = app(StripeService::class, ['vendor' => $packageSubscription->vendor]);

            $paymentMethod = $stripeService->getCardInfo($paymentMethodId);

            $card = $paymentMethod->card;

            $cardName = $paymentMethod->billing_details->name;

            $paymentMethod = StripePaymentMethod::where('payment_method_id', $paymentMethodId)->first();

            $payment = Payment::create([
                'table_name' => $packageSubscription->getTable(),
                'table_id' => $packageSubscription->id,
                'transaction_id' => $paymentIntentId,
                'ref_no' => $paymentIntentId,
                'amount' => $packageSubscription['price'],
                'gateway' => 'stripe', // payment gateway name
                'card_last_name' => $cardName,
                'cr_last4' => $card['last4'] ?? null,
                'cr_exp_month' => $card['exp_month'] ?? null,
                'cr_exp_year' => $card['exp_year'] ?? null,
                'payment_type' => $card['brand'] ?? null,  // if you have brand column
                'client_id' => $packageSubscription->client_id,
                'vendor_id' => $packageSubscription->vendor_id,
                'stripe_payment_method_id' => $paymentMethod->id,
                'created_at' => now(),
            ]);
            $packageSubscription->status = 'active';
            $packageSubscription->is_paid = 1;
            $packageSubscription->save();

            // $subscriptionService = app(SubscriptionServiceContainer::class);

            // dd($packageSubscription);

            $subscription = $stripeService->getSubscription($packageSubscription->stripe_subs_id);

            $stripe = new StripeSub($packageSubscription->vendor);

            $subscriptionService = new SubscriptionServiceContainer($stripe, $this->messageService);

            $email = $subscriptionService->sendEmail($packageSubscription, $subscription);

            $temp = view($this->viewPath.'::forms._subscription_success', compact('packageSubscription'))->render();

            return response()->json(['success' => true, 'payment_info' => $card, 'payment' => $payment, 'temp' => $temp], 200);
        } catch (\Exception $e) {
            Log::error('storeCardPayment error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function subscribe(Request $request, $planId)
    {
        $validated = $request->validate([
            'plan_id' => 'required|integer',
            'stripeToken' => 'required|string',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|string',
            'description' => 'required|string',

            'contact' => 'required|array',
            'contact.fname' => 'required|string',
            'contact.lname' => 'required|string',
            'contact.email' => 'required|email',
            'contact.phone_no' => 'required|string',
            'contact.password' => 'nullable|string',
            'contact.confirm_password' => 'nullable|string',

            'address' => 'required|array',
            'address.add1' => 'required|string',
            'address.add2' => 'nullable|string',
            'address.city' => 'required|string',
            'address.state' => 'required|string',
            'address.zip' => 'required|string',
            'address.country' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {

            $vendor = $this->vendor;
            $stripe = new StripeSub($vendor);

            $subsService = new SubscriptionServiceContainer($stripe, $this->messageService);

            $result = $this->clientService->createClient([
                'contact' => $validated['contact'],
                'address' => $validated['address'],
            ], $vendor);

            $client = $result['client'];
            $address = $result['address'];

            $packageType = PackageType::find($request->plan_id);

            $stripeToken = $request->stripeToken;

            // dd($request->prefe);
            $startDate = $request->preferred_date;
            $startTime = $request->preferred_time;

            // dd($packageType, $client, $stripeToken, $startDate, $startTime);

            $info = $subsService->subscribe($packageType, $client, $address, $stripeToken, $startDate, $startTime);

            $subscription = $info['data'];

            DB::commit();

            $temp = view($this->viewPath.'::frontend.forms._subscription_success', compact('subscription'))->render();

            return response(['message' => 'Subscription success', 'data' => $info, 'temp' => $temp], 200);
        } catch (\Throwable $th) {
            header('Access-Control-Allow-Origin:*');
            dd($th->getMessage());

            DB::rollback();

            return response(['error' => $th->getLine()], 422);
        }
    }
}
