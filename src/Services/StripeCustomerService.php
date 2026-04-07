<?php

namespace Systha\Core\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Systha\Core\Lib\Subscription\StripeSub;
use Systha\Core\Models\ClientModel;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\StripeCustomer;

class StripeCustomerService
{
    /**
     * Get or create a Stripe customer for the given vendor and client.
     *
     * @param Vendor $vendor
     * @param ClientModel $client
     * @param StripeSub $stripe
     * @return StripeCustomer
     * @throws Exception
     */
    public function getStripeCustomer(Vendor $vendor, ClientModel $client, StripeSub $stripe): StripeCustomer
    {
        try {
            // 1. Check for existing StripeCustomer record
            $profile = StripeCustomer::where('client_id', $client->id)
                ->where('vendor_id', $vendor->id)
                ->first();

            if ($profile && $profile->stripe_customer_id) {
                // Try to retrieve the customer from Stripe
                $customer = $stripe->retrieveActiveCustomer($profile->stripe_customer_id);
                if ($customer) {
                    return $profile;
                }
            }

            // 2. Create a new Stripe customer in Stripe
            $customer = $stripe->createCustomer([
                'name'  => trim($client->fname . ' ' . $client->lname),
                'email' => $client->email,
                'phone' => $client->phone_no,
                'metadata'=> [
                    'client_id' => $client->id,
                    'vendor_id' => $vendor->id,
                ],
            ]);

            try {
                // 3. Save locally in StripeCustomer table
                $profile = StripeCustomer::updateOrCreate(
                    [
                        'client_id' => $client->id,
                        'vendor_id' => $vendor->id,
                        'email' => $client->email,
                    ],
                    [
                        'stripe_customer_id' => $customer['id'],
                    ]
                );
            } catch (\Throwable $e) {
                // Rollback: Delete the Stripe customer if local save fails
                try {
                    $stripe->deleteCustomer($customer['id']);
                } catch (\Throwable $ex) {
                    Log::error('StripeCustomerService: Failed to delete Stripe customer after local save error', [
                        'stripe_customer_id' => $customer['id'],
                        'error' => $ex->getMessage(),
                    ]);
                }
                throw $e;
            }

            return $profile;
        } catch (Exception $e) {
            Log::error('StripeCustomerService: Failed to get or create Stripe customer', [
                'vendor_id' => $vendor->id,
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
