<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        if (!$this->resource) {
            return [
                'id' => null,
                'stripe_customer_id' => null,
                'default_payment_method_id' => null,
                'default_payment_method' => null,
                'payment_methods' => [],
            ];
        }

        return [
            'id' => $this->id,
            'stripe_customer_id' => $this->stripe_customer_id,
            'default_payment_method_id' => $this->default_payment_method_id,
            'default_payment_method' => $this->defaultPaymentMethod,
            'payment_methods' => $this->paymentMethods ?? [],
        ];
    }
}
