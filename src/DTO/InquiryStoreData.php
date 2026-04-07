<?php

namespace Systha\Core\DTO;

class InquiryStoreData
{
    public readonly string $fname;
    public readonly ?string $lname;

    public function __construct(
        public readonly string $vendorCode,
        public readonly array $selectedItems,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $addressLine1,
        public readonly ?string $addressLine2,
        public readonly string $city,
        public readonly string $state,
        public readonly string $zip,
        public readonly ?string $note,
    ) {
        $this->fname = $firstName;
        $this->lname = $lastName;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            vendorCode: $data['vendor_code'],
            selectedItems: $data['selected_items'] ?? [],
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

    // Add methods as needed for your handler
}
