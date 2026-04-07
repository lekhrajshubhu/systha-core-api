<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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

    public function listResponse(): array
    {
        return collect($this->paymentMethods ?? [])->map(function ($method) {
            $expiry = $method->exp_month && $method->exp_year
                ? sprintf('%02d/%02d', $method->exp_month, $method->exp_year % 100)
                : null;

            $status = 'active';
            if ($method->exp_month && $method->exp_year) {
                $expiryDate = Carbon::createFromDate($method->exp_year, $method->exp_month, 1)->endOfMonth();
                $status = $expiryDate->lt(Carbon::now()) ? 'expired' : 'active';
            }

            return [
                'id' => $method->id,
                'label' => $method->is_default ? 'Primary Card' : 'Card',
                'name_on_card' => $method->card_name,
                'brand' => $method->card_brand,
                'last4' => $method->card_last4,
                'expiry' => $expiry,
                'status' => $status,
                'icon' => 'mdi-credit-card-outline',
                'is_default' => (bool) $method->is_default,
            ];
        })->values()->all();
    }
}
