<?php

namespace Systha\Core\Services;

use Stripe\Stripe;
use Stripe\Invoice;
use Stripe\Customer;
use Stripe\StripeClient;
use Stripe\Subscription;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Systha\Core\Models\Client;
use Systha\Core\Models\ClientModel;
use Systha\Core\Models\StripeCustomer;
use Systha\Core\Models\VendorModel;

class StripeService
{
    protected string $secret;
    protected StripeClient $stripe;

    public function __construct(VendorModel $vendor)
    {
        $this->secret = $vendor->defaultPaymentCredential->val2;

        // For static methods (e.g. \Stripe\Customer::create())
        Stripe::setApiKey($this->secret);

        // For object-oriented methods
        $this->stripe = new StripeClient($this->secret);
    }

    public function findStripeCustomerForVendor(ClientModel $client, VendorModel $vendor): ?Customer
    {
        $stripeCustomer = StripeCustomer::where([
            'client_id' => $client->id,
            'vendor_id' => $vendor->id,
        ])->first();

        if ($stripeCustomer) {
            return $this->stripe->customers->retrieve($stripeCustomer->stripe_customer_id, []);
        }

         $payload = [
            'name' => trim($client->fname . ' ' . $client->lname),
            'email' => $client->email,
            'phone' => $client->phone1,
            'metadata' => [
                'client_id' => (int) $client->id,
                'vendor_id' => (int) $vendor->id,
            ],
        ];

        $customer = $this->stripe->customers->create($payload);
        StripeCustomer::create([
            'client_id' => $client->id,
            'vendor_id' => $vendor->id,
            'stripe_customer_id' => $customer->id,
            'email' => $client->email,
            'name' => trim($client->fname . ' ' . $client->lname),
            'phone' => $client->phone1,
        ]);
        return $customer;
    }


    public function stripeCustomer(array $params): ?StripeCustomer
    {
        $stripeCustomer = StripeCustomer::where('email', $params["customer_email"])->first();

        if ($stripeCustomer && $stripeCustomer->stripe_customer_id) {

            $customer = Customer::retrieve($stripeCustomer->stripe_customer_id);
            return $stripeCustomer;
        }

        // Find or create the client
        $client = Client::firstOrCreate(
            ['email' => $params["customer_email"]],
            [
                'fname' => $params["name"] ?? '',
                'lname' => $params["lname"] ?? 'Guest',
                'phone_no' => $params["phone"] ?? null,
            ]
        );

        $customer = Customer::create([
            'email' => $params["customer_email"],
            'name' => $params["customer_name"],
            'phone' => $params["customer_phone"]
        ]);

        if (!$stripeCustomer) {
            $stripeCustomer = new StripeCustomer();

            $stripeCustomer->client_id = $client->id;
            $stripeCustomer->email = $params["customer_email"];
            $stripeCustomer->name = $params["customer_name"];
            $stripeCustomer->phone = $params["customer_phone"];
            $stripeCustomer->default_payment_method_id = $params["payment_method_id"];
        }

        $stripeCustomer->stripe_customer_id = $customer->id;
        $stripeCustomer->save();

        return $stripeCustomer;
    }

    public function attachStripeMethod(string $paymentMethodId, StripeCustomer $stripeCustomer)
    {
        try {
            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);

            $card = $paymentMethod->card;
            $cardName = $paymentMethod->billing_details->name;

            $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $stripeCustomer->stripe_customer_id
            ]);

            // // Store locally if not already stored
            $existing = $stripeCustomer->paymentMethods()->where('payment_method_id', $paymentMethodId)->first();
            if (!$existing) {
                $existing = $stripeCustomer->paymentMethods()->create([
                    'client_id' => $stripeCustomer->client_id,
                    'payment_method_id' => $paymentMethodId,
                    'card_name' => $cardName,
                    'card_brand' => $card->brand,
                    'card_last4' => $card->last4,
                    'exp_month' => $card->exp_month,
                    'exp_year' => $card->exp_year,
                    'funding' => $card->funding,
                    'country' => $card->country,
                ]);
            }

            $stripeCustomer->default_payment_method_id = $existing->payment_method_id;
            $stripeCustomer->save();
            // Set all other payment methods to is_default = 0
            $stripeCustomer->paymentMethods()->where('id', '!=', $existing->id)->update(['is_default' => 0]);

            // Set current one to is_default = 1
            $existing->is_default = 1;
            $existing->save();


            // // Return the local stored payment method record for further use
            return $existing;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error("Stripe API error in attachStripeMethod: " . $e->getMessage(), [
                'payment_method_id' => $paymentMethodId,
                'customer_id' => $stripeCustomer->stripe_customer_id,
                'stripe_code' => $e->getStripeCode(),
                'http_status' => $e->getHttpStatus(),
            ]);
            dd($e->getMessage());
            return null;
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error("General error in attachStripeMethod: " . $e->getMessage());
            return null;
        }
    }

    public function addPaymentMethod(array $params)
    {
        try {
            $customer = $this->stripeCustomer($params);
            $card = $this->attachStripeMethod($params["payment_method_id"], $customer);

            return response(["message" => "New card added", "data" => $card], 200);
        } catch (\Throwable $th) {
            return response(["error" => $th->getMessage()], 422);
        }
    }

    public function createPaymentIntent(array $params): ?PaymentIntent
    {

        DB::beginTransaction();
        try {
            // Ensure amount and payment method are provided
            if (!isset($params['amount'], $params['payment_method_id'], $params['customer_email'])) {
                throw new \InvalidArgumentException('Missing required parameters.');
            }

            // If stripe_customer_id is already available
            if (!empty($params["stripe_customer_id"])) {
                return PaymentIntent::create([
                    'amount' => intval($params['amount'] * 100),
                    'currency' => $params['currency'] ?? 'usd',
                    'customer' => $params['stripe_customer_id'],
                    'payment_method' => $params['payment_method_id'],
                    'receipt_email' => $params['customer_email'],
                    'confirmation_method' => 'automatic',
                    'setup_future_usage' => $params['setup_future_usage'] ?? 'off_session',
                    'metadata' => $params['metadata'] ?? [],
                ]);
            }

            // Otherwise, create or get customer and attach method
            $customer = $this->stripeCustomer($params);

            if (!$customer || !isset($customer->id)) {
                throw new \Exception('Failed to retrieve or create Stripe customer.');
            }

            $this->attachStripeMethod($params["payment_method_id"], $customer);

            $paymentIntent = PaymentIntent::create([
                'amount' => intval($params['amount'] * 100),
                'currency' => $params['currency'] ?? 'usd',
                'customer' => $customer->stripe_customer_id,
                'payment_method' => $params['payment_method_id'],
                'receipt_email' => $params['customer_email'],
                'confirmation_method' => 'automatic',
                // 'confirm' => true,
                'setup_future_usage' => $params['setup_future_usage'] ?? 'off_session',
                'metadata' => $params['metadata'] ?? [],
            ]);
            DB::commit();
            return $paymentIntent;
        } catch (\Throwable $e) {
            Log::error("Stripe PaymentIntent Error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'params' => $params,
            ]);

            dd($e->getMessage());
            return null;
        }
    }


    // public function createSubscription(string $customerId, string $priceId, string $paymentMethodId = null): ?Subscription
    // {
    //     try {
    //         $subscription = Subscription::create([
    //             'customer' => $customerId,
    //             'items' => [
    //                 ['price' => $priceId],
    //             ],
    //             'payment_settings' => [
    //                 'payment_method_types' => ['card'],
    //                 'save_default_payment_method' => 'on_subscription',
    //                 'payment_method_options' => [],
    //             ],
    //             'default_payment_method' => $paymentMethodId,
    //             'expand' => ['latest_invoice.payment_intent'],
    //         ]);

    //         return $subscription;
    //     } catch (\Exception $e) {
    //         Log::error("Stripe subscription error: " . $e->getMessage());
    //         return null;
    //     }
    // }
    public function createSubscription(array $data): ?Subscription
    {
        try {
            $subscription = Subscription::create([
                'customer' => $data["customerId"],
                'items' => [
                    ['price' => $data["priceId"]],
                ],
                'payment_settings' => [
                    'payment_method_types' => ['card'],
                    'save_default_payment_method' => 'on_subscription',
                    'payment_method_options' => [],
                ],
                'default_payment_method' => $data["paymentMethodId"],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            return $subscription;
        } catch (\Exception $e) {
            Log::error("Stripe subscription error: " . $e->getMessage());
            return null;
        }
    }

    public function getSubscription(string $subscriptionId): ?Subscription
    {
        try {

            $subscription = Subscription::retrieve($subscriptionId);

            return $subscription;
        } catch (\Exception $e) {
            Log::error("Stripe subscription retrieval error: " . $e->getMessage());
            return null;
        }
    }


    public function retrieveInvoicePaymentIntent(string $invoiceId): ?PaymentIntent
    {
        try {
            $invoice = Invoice::retrieve([
                'id' => $invoiceId,
                'expand' => ['payment_intent']
            ]);
            return $invoice->payment_intent;
        } catch (\Exception $e) {
            Log::error("Retrieve Invoice PaymentIntent error: " . $e->getMessage());
            return null;
        }
    }

    public function getCardInfo(string $paymentMethodId)
    {
        if (!$paymentMethodId) return;

        return PaymentMethod::retrieve($paymentMethodId);
    }
}




//One-time appointment payment:
// $customer = $stripe->findOrCreateCustomer($email, $name, $phone);

// $stripe->createAndAttachPaymentMethod($paymentMethodId, $customer->id);

// $intent = $stripe->createPaymentIntent([
//     'amount' => 150, // in dollars
//     'currency' => 'usd',
//     'customer_id' => $customer->id,
//     'payment_method_id' => $paymentMethodId,
//     'email' => $email,
//     'setup_future_usage' => 'off_session',
//     'metadata' => [
//         'appointment_id' => $appointment->id,
//     ],
// ]);


//  Create subscription:
// $customer = $stripe->findOrCreateCustomer($email, $name, $phone);
// $stripe->createAndAttachPaymentMethod($paymentMethodId, $customer->id);
// $subscription = $stripe->createSubscription($customer->id, $priceId, $paymentMethodId);
