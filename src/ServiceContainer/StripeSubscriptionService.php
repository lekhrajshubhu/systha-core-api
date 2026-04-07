<?php

namespace Systha\Core\ServiceContainer;

use Stripe\StripeClient;

class StripeSubscriptionService
{
    public function __construct(protected StripeClient $stripe) {}

    public function create(array $data): array
    {
        if (!empty($data['payment_method_id'])) {
            $this->stripe->paymentMethods->attach(
                $data['payment_method_id'],
                ['customer' => $data['customer_id']]
            );

            $this->stripe->customers->update($data['customer_id'], [
                'invoice_settings' => [
                    'default_payment_method' => $data['payment_method_id'],
                ],
            ]);
        }

        $subscription = $this->stripe->subscriptions->create([
            'customer' => $data['customer_id'],
            'items' => [
                ['price' => $data['price_id']],
            ],
            'payment_behavior' => 'default_incomplete',
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        return [
            'id' => $subscription->id,
            'status' => $subscription->status,
            'client_secret' => $subscription->latest_invoice?->payment_intent?->client_secret,
        ];
    }
}