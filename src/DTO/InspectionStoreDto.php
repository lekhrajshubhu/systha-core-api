<?php

namespace Systha\Core\DTO;

use Illuminate\Http\UploadedFile;

class InspectionStoreDto
{
    public readonly string $fname;
    public readonly ?string $lname;

    /**
     * @param  UploadedFile[]  $photos
     * @param  string[]  $photoDescriptions
     */
    public function __construct(
        public readonly string $vendorCode,
        public readonly string $firstName,
        public readonly ?string $lastName,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $addressLine1,
        public readonly ?string $addressLine2,
        public readonly string $city,
        public readonly string $state,
        public readonly string $zip,

        public readonly ?string $note,
        public readonly array $photos,
        public readonly array $photoDescriptions,
    ) {
        $this->fname = $firstName;
        $this->lname = $lastName;
    }


    /**
     * @param  array<string, mixed>  $data
     * @param  UploadedFile[]  $photos
     */
    public static function fromArray(array $data, array $photos = []): self
    {

        $firstName = $data['contact']['first_name'] ?? '';
        $lastName = $data['contact']['last_name'] ?? null;
        return new self(
            vendorCode: $data['vendorCode'],
            firstName: $firstName,
            lastName: $lastName,
            
            note: $data['note'] ?? null,
            phone: $data['contact']['phone'],
            email: strtolower(trim($data['contact']['email'])),
            addressLine1: $data['service_area']['line_1'],
            addressLine2: $data['service_area']['line_2'] ?? null,
            city: $data['service_area']['city'],
            state: $data['service_area']['state'],
            zip: $data['service_area']['zip'],
            photos: $photos,
            photoDescriptions: array_values($data['photo_descriptions'] ?? []),
        );
    }

    private static function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [
            $parts[0] ?? '',
            $parts[1] ?? null,
        ];
    }

    public function toClientLookupArray(): array
    {
        return [
            'email' => $this->email,
        ];
    }

    public function toClientCreateArray(): array
    {
        return [
            'fname' => $this->fname,
            'lname' => $this->lname,
            'email' => $this->email,
            'phone_no' => $this->phone,
        ];
    }

    public function toClientUpdateArray(): array
    {
        return [
            'fname' => $this->fname,
            'lname' => $this->lname,
            'email' => $this->email,
            'phone_no' => $this->phone,
        ];
    }

    public function toInspectionArray(int $clientId, int $vendorId): array
    {
        return [
            'client_id' => $clientId,
            'vendor_id' => $vendorId,
            'category_id' => null,
            'service_id' => null,
            'preferred_date' => null,
            'preferred_time' => null,
            'desc' => $this->note,
            'state' => 'publish',
            'status' => 'new',
            'type' => 'inspection',
            'requested_date' => now()->toDateString(),
            'is_active' => 1,
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function toServiceAddressArray(): array
    {
        return [
            'address_type' => 'inquiries',
            'add1' => $this->addressLine1,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
        ];
    }
}
