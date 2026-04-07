<?php

namespace Systha\Core\DTO;

class SubscriptionStoreDto
{
    public function __construct(
        public string $vendorCode,
        public int $packageId,
        public int $planId,
        public ClientDto $client,
        public AddressDto $address,
        public array $stripe,
        public ?string $note = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vendorCode: $data['vendor_code'],
            packageId: (int) $data['package_id'],
            planId: (int) $data['plan_id'],
            client: ClientDto::fromArray($data['contact']),
            address: AddressDto::fromArray($data['address']),
            stripe: $data['stripe'],
            note: $data['note'] ?? null,
        );
    }

    public function stripePaymentMethodId(): string
    {
        return $this->stripe['id'];
    }

    public function stripeMeta(): array
    {
        return [
            'id' => $this->stripe['id'],
            'brand' => $this->stripe['brand'],
            'last4' => $this->stripe['last4'],
            'exp_month' => $this->stripe['expMonth'],
            'exp_year' => $this->stripe['expYear'],
        ];
    }
}