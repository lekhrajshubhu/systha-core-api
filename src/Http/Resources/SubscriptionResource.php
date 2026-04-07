<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            'subs_no'    => $this->subs_no,

            'package'        => [
                "id"       => optional($this->package)->id,
                "name"     => optional($this->package)->package_name,

            ],
            'is_cancelled' => $this->is_cancelled,
            // 'services'        => $this->package->packageServices,
            'services' => $this->package->packageServices->map(function ($service) {
                return [
                    'id'   => $service->id,
                    'name' => $service->service_name,
                    'price' => $service->price ?? 0, // optional if pivot exists
                ];
            }),
            'payments' => $this->payments,
            'package_type'        => [
                "id"       => optional($this->packageType)->id,
                "duration"     => optional($this->packageType)->duration > 1 ? optional($this->packageType)->duration : '',
                "type_name"     => optional($this->packageType)->type_name,
                "name"     => optional($this->packageType)->description,

            ],


            'address' => [
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

            // "service_count" => count($this->services),

            'description'  => $this->description,


            'status'       => $this->status,
            'amount'       => $this->price,

            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
        ];
    }
}
