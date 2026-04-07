<?php

namespace Systha\Core\Services;

use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\Subscription;
use Stripe\Stripe;
use Stripe\StripeClient;
use Systha\Core\Models\ClientModel;
use Systha\Core\Models\StripeCustomer;
use Systha\Core\Models\VendorModel;

class StripeService
{
    protected string $secret;
    protected StripeClient $stripe;

    public function __construct(VendorModel $vendor)
    {
        $this->secret = optional($vendor->defaultPaymentCredential)->val2
            ?? config('services.stripe.secret');

        Stripe::setApiKey($this->secret);
        $this->stripe = new StripeClient($this->secret);
    }

    public function findStripeCustomerForVendor(ClientModel $client, VendorModel $vendor): Customer
    {
        $stripeCustomer = StripeCustomer::where([
            'client_id' => $client->id,
            'vendor_id' => $vendor->id,
        ])->first();

        if ($stripeCustomer) {
            return $this->stripe->customers->retrieve($stripeCustomer->stripe_customer_id, []);
        }

        $payload = [
            'name' => trim(($client->fname ?? '') . ' ' . ($client->lname ?? '')),
            'email' => $client->email,
            'phone' => $client->phone1 ?? $client->phone_no ?? null,
            'metadata' => [
                'client_id' => (string) $client->id,
                'vendor_id' => (string) $vendor->id,
            ],
        ];

        try {
            $customer = $this->stripe->customers->create($payload);
            StripeCustomer::create([
                'client_id' => $client->id,
                'vendor_id' => $vendor->id,
                'stripe_customer_id' => $customer->id,
                'email' => $client->email,
                'name' => $payload['name'],
                'phone' => $payload['phone'],
            ]);
            return $customer;
        } catch (ApiErrorException $e) {
            Log::error('Stripe create customer failed', [
                'client_id' => $client->id,
                'vendor_id' => $vendor->id,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function createPaymentIntentForVendorClient(
        ClientModel $client,
        VendorModel $vendor,
        int $amount,
        string $currency = 'usd',
        array $metadata = [],
        bool $saveForFuture = true,
        ?string $idempotencyKey = null
    ): PaymentIntent {
        $customer = $this->findStripeCustomerForVendor($client, $vendor);
        $metadata = $this->withBaseMetadata($metadata, $client, $vendor);

        $payload = [
            'customer' => $customer->id,
            'amount' => $amount,
            'currency' => $currency,
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => $metadata,
        ];

        if ($saveForFuture) {
            $payload['setup_future_usage'] = 'off_session';
        }

        return $this->stripe->paymentIntents->create($payload, $this->idempotencyArray($idempotencyKey));
    }

    public function createSetupIntentForVendorClient(
        ClientModel $client,
        VendorModel $vendor,
        array $metadata = [],
        ?string $idempotencyKey = null
    ): SetupIntent {
        $customer = $this->findStripeCustomerForVendor($client, $vendor);
        $metadata = $this->withBaseMetadata($metadata, $client, $vendor);

        return $this->stripe->setupIntents->create([
            'customer' => $customer->id,
            'payment_method_types' => ['card'],
            'metadata' => $metadata,
        ], $this->idempotencyArray($idempotencyKey));
    }

    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId, []);
    }

    public function retrieveSetupIntent(string $setupIntentId): SetupIntent
    {
        return $this->stripe->setupIntents->retrieve($setupIntentId, []);
    }

    public function retrievePaymentMethod(string $paymentMethodId): PaymentMethod
    {
        return $this->stripe->paymentMethods->retrieve($paymentMethodId, []);
    }

    public function chargeSavedPaymentMethod(
        ClientModel $client,
        VendorModel $vendor,
        string $paymentMethodId,
        int $amount,
        string $currency = 'usd',
        array $metadata = [],
        ?string $idempotencyKey = null
    ): PaymentIntent {
        $customer = $this->findStripeCustomerForVendor($client, $vendor);
        $metadata = $this->withBaseMetadata($metadata, $client, $vendor);

        return $this->stripe->paymentIntents->create([
            'customer' => $customer->id,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => $paymentMethodId,
            'off_session' => true,
            'confirm' => true,
            'metadata' => $metadata,
        ], $this->idempotencyArray($idempotencyKey));
    }

    public function createSubscriptionWithPrice(
        string $customerId,
        string $priceId,
        ?string $paymentMethodId = null,
        array $metadata = [],
        ?string $idempotencyKey = null
    ): Subscription {
        $payload = [
            'customer' => $customerId,
            'items' => [
                ['price' => $priceId],
            ],
            'metadata' => $metadata,
            'expand' => ['latest_invoice.payment_intent'],
        ];

        if ($paymentMethodId) {
            $payload['default_payment_method'] = $paymentMethodId;
        }

        return $this->stripe->subscriptions->create($payload, $this->idempotencyArray($idempotencyKey));
    }

    public function storePaymentMethodDetails(
        StripeCustomer $stripeCustomer,
        PaymentMethod $paymentMethod,
        bool $isDefault = false
    ): void {
        if (!method_exists($stripeCustomer, 'paymentMethods')) {
            return;
        }

        $card = $paymentMethod->card;
        $existing = $stripeCustomer->paymentMethods()
            ->where('payment_method_id', $paymentMethod->id)
            ->first();

        $record = $existing ?? $stripeCustomer->paymentMethods()->create([
            'client_id' => $stripeCustomer->client_id,
            'payment_method_id' => $paymentMethod->id,
            'card_name' => $paymentMethod->billing_details->name,
            'card_brand' => $card->brand,
            'card_last4' => $card->last4,
            'exp_month' => $card->exp_month,
            'exp_year' => $card->exp_year,
            'funding' => $card->funding,
            'country' => $card->country,
        ]);

        if ($isDefault) {
            $stripeCustomer->paymentMethods()->where('id', '!=', $record->id)->update(['is_default' => 0]);
            $record->is_default = 1;
            $record->save();
            $stripeCustomer->default_payment_method_id = $record->payment_method_id;
            $stripeCustomer->save();
        }
    }

    private function withBaseMetadata(array $metadata, ClientModel $client, VendorModel $vendor): array
    {
        $base = [
            'client_id' => (string) $client->id,
            'vendor_id' => (string) $vendor->id,
        ];

        foreach ($metadata as $key => $value) {
            $base[$key] = is_scalar($value) ? (string) $value : json_encode($value);
        }

        return $base;
    }

    private function idempotencyArray(?string $key): array
    {
        return $key ? ['idempotency_key' => $key] : [];
    }

    /**
     * Backwards-compatible helper: retrieve a Stripe PaymentMethod by id.
     */
    public function getCardInfo(string $paymentMethodId): PaymentMethod
    {
        return $this->retrievePaymentMethod($paymentMethodId);
    }

    /**
     * Backwards-compatible helper to create a basic PaymentIntent from loose params.
     */
    public function createPaymentIntent(array $params): PaymentIntent
    {
        $amountCents = (int) round(($params['amount'] ?? 0) * 100);
        $customerId = $params['stripe_customer_id'] ?? null;
        $paymentMethodId = $params['payment_method_id'] ?? null;

        $payload = [
            'amount' => $amountCents,
            'currency' => $params['currency'] ?? 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'receipt_email' => $params['customer_email'] ?? null,
        ];

        if ($customerId) {
            $payload['customer'] = $customerId;
        }
        if ($paymentMethodId) {
            $payload['payment_method'] = $paymentMethodId;
        }

        return $this->stripe->paymentIntents->create($payload);
    }

    /**
     * Legacy signature shim; prefers payment_method id, falls back to token lookup.
     */
    public function getCard($customerOrToken, $stripeToken = null)
    {
        $id = $stripeToken ?? $customerOrToken;
        if (str_starts_with($id, 'pm_')) {
            return $this->retrievePaymentMethod($id);
        }
        // legacy token retrieval
        return $this->stripe->tokens->retrieve($id, []);
    }
}
