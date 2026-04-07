<?php

namespace Systha\Core\ServiceContainer;

use Stripe\StripeClient;
use Systha\Core\Models\ClientModel;
use Systha\Core\Models\StripeCustomer;
use Systha\Core\Models\VendorModel;

class StripeCustomerService
{
    public function __construct(protected StripeClient $stripe) {}

    public function createOrFetch(ClientModel $client, VendorModel $vendor)
    {

        $stripeCustomer = StripeCustomer::where([
            'client_id' => $client->id,
            'vendor_id' => $vendor->id,
        ])->first();

        if($stripeCustomer) {
            return $this->stripe->customers->retrieve($stripeCustomer->stripe_customer_id, []);
        }
        if ($client->stripe_customer_id) {
            return $this->stripe->customers->retrieve($client->stripe_customer_id, []);
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

        $client->update([
            'stripe_customer_id' => $customer->id,
        ]);

        return $customer;
    }
}