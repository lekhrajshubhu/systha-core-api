<?php

namespace Systha\Core\Handler;

use Illuminate\Support\Facades\DB;
use Systha\Core\DTO\SubscriptionStoreData;
use Systha\Core\Models\Address;
use Systha\Core\Models\ClientModel;
use Systha\Core\Models\PackageSubscription;
use Systha\Core\Models\PackageType;
use Systha\Core\Models\SubscriptionModel;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\VendorClient;
use Systha\Core\Services\MessageService;
use Systha\Core\Services\SubscriptionService;
use Systha\Core\Services\SubscriptionServiceContainer;

class SubscriptionStoreHandler
{
    protected $messageService;
    protected $subscriptionService;
    public function __construct(MessageService $messageService, SubscriptionServiceContainer $subscriptionService)
    {
        $this->messageService = $messageService;
        $this->subscriptionService = $subscriptionService;
    }
    public function handle(SubscriptionStoreData $data)
    {
        return DB::transaction(function () use ($data) {
            $vendorId = Vendor::query()
                ->where('vendor_code', $data->vendorCode)
                ->value('id');

            if (!$vendorId) {
                abort(422, 'Invalid vendor code.');
            }

            // Create or update client
            $client = ClientModel::firstOrCreate(
                [
                    'email' => $data->email,
                ],
                [
                    'phone_no' => $data->phone,
                    'fname' => $data->firstName,
                    'lname' => $data->lastName,
                ]
            );

            if (! $client->wasRecentlyCreated) {
                $client->update([
                    'fname' => $data->firstName,
                    'lname' => $data->lastName,
                ]);
            }

            $vendorClient = VendorClient::firstOrCreate([
                'vendor_id' => $vendorId,
                'email' => $data->email,
                'client_id' => $client->id,
            ], [
                'is_active' => 1,
                'vendor_code' => $data->vendorCode,
            ]);

            $packageType = PackageType::query()
                ->where('id', $data->planId)
                ->where('vendor_id', $vendorId)
                ->first();

   
            $address = Address::create([
                'table_name' => 'clients',
                'table_id' => $client->id,
                'add1' => $data->addressLine1,
                'add2' => $data->addressLine2,
                'city' => $data->city,
                'state' => $data->state,
                'zip' => $data->zip,
            ]);

            $info = $this->subscriptionService->subscribe($packageType, $client,$address, $data->stripeToken, $data->startDate, $data->startTime);
            return $info;
        });
    }
}
