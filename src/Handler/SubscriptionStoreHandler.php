<?php

namespace Systha\Core\Handler;

use Illuminate\Support\Facades\DB;
use Systha\Core\DTO\SubscriptionStoreDto;
use Systha\Core\Models\VendorModel;
use Systha\Core\ServiceContainer\AddressService;
use Systha\Core\ServiceContainer\AppointmentService;
use Systha\Core\ServiceContainer\ClientOnboardingService;
use Systha\Core\ServiceContainer\ClientService;
use Systha\Core\ServiceContainer\StripeCustomerService;
use Systha\Core\ServiceContainer\StripeSubscriptionService;
use Systha\Core\Services\StripeService;

class SubscriptionStoreHandler
{
    protected $stripe;

    public function __construct(
        protected ClientService $clientService,
        protected ClientOnboardingService $clientOnboardingService,
        protected AddressService $addressService,
        protected StripeCustomerService $stripeCustomerService,
        protected StripeSubscriptionService $stripeSubscriptionService,
        protected AppointmentService $appointmentService,
    ) {}

    public function handle(SubscriptionStoreDto $data): array
    {


        return DB::transaction(function () use ($data) {
            /*
             |------------------------------------------------------------
             | 1. Create or update client
             |------------------------------------------------------------
             */

            $vendor = VendorModel::where('vendor_code', $data->vendorCode)->firstOrFail();

            $this->stripe = app(StripeService::class, ['vendor' => $vendor]);


            $clientResult = $this->clientService->createOrUpdate($data->client);

            $client = $clientResult['client'];

            $plainPassword = $clientResult['plain_password'] ?? null;
            $wasCreated = $clientResult['was_created'] ?? false;

            /*
             |------------------------------------------------------------
             | 2. Create or update default address
             |------------------------------------------------------------
             */
            if ($wasCreated) {

                $this->addressService->createOrUpdateDefault($client, $data->address);
                $this->clientOnboardingService->sendWelcomeEmail($client, $plainPassword);
            }

            // dd($data->stripe['id']);
            $stripeToken = $data->stripe['id'] ?? null;
            /*
             |------------------------------------------------------------
             | 4. Create or fetch Stripe customer
             |------------------------------------------------------------
             */
            $stripeCustomer = $this->stripe->findStripeCustomerForVendor($client, $vendor);

            $stripeCard = $this->stripe->getCard($stripeCustomer, $stripeToken);
            dd($stripeCard);
            /*
             |------------------------------------------------------------
             | 5. Create Stripe subscription
             |------------------------------------------------------------
             */
            $stripeSubscription = $this->stripeSubscriptionService->create([
                'customer_id'       => $stripeCustomer->id,
                'price_id'          => $data['stripePriceId'],
                'payment_method_id' => $data['paymentMethodId'],
            ]);

            /*
             |------------------------------------------------------------
             | 6. Save local subscription
             |------------------------------------------------------------
             */
            $subscription = $client->subscriptions()->create([
                'stripe_customer_id'     => $stripeCustomer->id,
                'stripe_subscription_id' => $stripeSubscription['id'],
                'stripe_price_id'        => $data['stripePriceId'],
                'status'                 => $stripeSubscription['status'],
            ]);

            /*
             |------------------------------------------------------------
             | 7. Create appointment
             |------------------------------------------------------------
             */
            $appointment = $this->appointmentService->createForClient($client, [
                'subscription_id'        => $subscription->id,
                'appointment_date'       => $data['appointmentDate'],
                'appointment_time'       => $data['appointmentTime'],
                'notes'                  => $data['appointmentNotes'],
                'status'                 => 'pending',
                'stripe_subscription_id' => $stripeSubscription['id'],
            ]);

            return [
                'client' => $client,
                'stripe_customer' => $stripeCustomer,
                'subscription' => $subscription,
                'stripe_subscription' => $stripeSubscription,
                'appointment' => $appointment,
            ];
        });
    }
}
