<?php

namespace Systha\Core\DTO;

use Illuminate\Support\Carbon;

class SubscriptionStoreDto1
{
    public function __construct(


        public readonly string $vendorCode,
        public readonly string $planId,
        public readonly string $packageId,
        public readonly string $stripeToken,

        public readonly string $startDate,
        public readonly string $startTime,
        public readonly ?string $note = null,

        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $addressLine1,
        public readonly ?string $addressLine2,
        public readonly string $city,
        public readonly string $state,
        public readonly string $zip,

    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vendorCode: $data['vendor_code'] ?? '',

            planId: $data['plan_id'] ?? '',
            packageId: $data['package_id'] ?? '',
            stripeToken: $data['stripe']['id'] ?? '',

            startDate: $data['start_date'] ?? Carbon::now()->format('Y-m-d'),
            startTime: $data['start_time'] ?? Carbon::now()->format('H:i'),
            
            
            firstName: $data['contact']['first_name'] ?? '',
            lastName: $data['contact']['last_name'] ?? '',
            email: strtolower(trim($data['contact']['email'] ?? '')),
            phone: $data['contact']['phone'] ?? '',
            
            addressLine1: $data['address']['line_1'] ?? '',
            addressLine2: $data['address']['line_2'] ?? null,
            city: $data['address']['city'] ?? '',
            state: $data['address']['state'] ?? '',
            zip: $data['address']['zip'] ?? '',

            note: $data['note'] ?? null,
        );
    }
}
