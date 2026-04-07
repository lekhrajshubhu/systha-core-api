<?php

namespace Systha\Core\DTO;

use Illuminate\Http\Request;

class AddressDto
{
    public function __construct(
        public string $line1,
        public ?string $line2,
        public string $city,
        public ?string $state,
        public ?string $zip,
        public ?float $lat,
        public ?float $lng,
        public ?string $country,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            line1: $request->line1,
            line2: $request->line2 ?? null,
            city: $request->city,
            state: $request->state ?? null,
            zip: $request->zip ?? null,
            lat: $request->lat ?? null,
            lng: $request->lng ?? null,
            country: $request->country ?? null,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            line1: $data['line_1'],
            line2: $data['line_2'] ?? null,
            city: $data['city'],
            state: $data['state'] ?? null,
            zip: $data['zip'] ?? null,
            lat: isset($data['lat']) ? (float) $data['lat'] : null,
            lng: isset($data['lng']) ? (float) $data['lng'] : null,
            country: $data['country'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'add1' => $this->line1,
            'add2' => $this->line2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'lat' => $this->lat,
            'lon' => $this->lng,
            'country' => $this->country,
        ];
    }
}
