<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InquiryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'inquiry_no'    => $this->enq_no,

            'client'        => [
                "id"       => optional($this->client)->id,
                "name"     => optional($this->client)->fullName,
                "email"    => optional($this->client)->email,
                "phone_no" => optional($this->client)->phone_no,
            ],

            'preferred_date'   => $this->preferred_date,
            'preferred_time'   => $this->preferred_time,

            'service_address' => [
                "add1"    => optional($this->address)->add1,
                "add2"    => optional($this->address)->add2,
                "city"    => optional($this->address)->city,
                "state"   => optional($this->address)->state,
                "zip"     => optional($this->address)->zip,
                "country" => optional($this->address)->country,
            ],

            'vendor' => [
                "id"          => optional($this->vendor)->id,
                "name"        => optional($this->vendor)->name,
                "vendor_code" => optional($this->vendor)->vendor_code,
            ],

            // 👇 Multiple services
            // 'services' => $this->enqServices->map(function ($service) {
            //     return [
            //         'id'   => $service->id,
            //         'name' => $service->item_name,
            //         'price' => $service->price ?? 0, // optional if pivot exists
            //     ];
            // }),
            "service_count" => count($this->enqServices),
            "quotes_count" => count($this->quotes),

            'description'  => $this->description,
            'is_emergency' => (bool) $this->is_emergency,

            'status'       => $this->status,

            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
        ];
    }
}
