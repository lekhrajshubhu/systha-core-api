<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorTypeResource extends JsonResource
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
            'label' => ucwords(str_replace('-', ' ', $this->value)),
            'subtitle' => $this->description,
            'routeName' => $this->mobile_route_name,
            'meta' => [
                'icon' => $this->icon_md,
                'color' => $this->color,
            ],
        ];
    }
}
