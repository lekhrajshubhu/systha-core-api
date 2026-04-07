<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorPackageResource extends JsonResource
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
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'package_name' => $this->package_name ?? $this->name,
            'name' => $this->name ?? $this->package_name,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'lowest_price' => $this->lowest_price,
            'plan_recurring' => $this->plan_recurring,
            'thumbnail' => $this->thumbnail_url,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
