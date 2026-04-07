<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AppointmentResource extends JsonResource
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
            'appointment_no'    => $this->appointment_no,

            'client'        => [
                "id"       => optional($this->client)->id,
                "name"     => optional($this->client)->fullName,
                "email"    => optional($this->client)->email,
                "phone_no" => optional($this->client)->phone_no,
            ],

            'appointment_date'   => $this->start_date,
            'appointment_time'   => $this->start_time,

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
                "logo"       => optional($this->vendor)->logo,
                "publishable_key" => optional(optional($this->vendor)->paymentCredential)->val1,
            ],
            'provider_name' => optional($this->technician()->orderByDesc('created_at')->first())->name,
            "service_count" => count($this->services),

            'description'  => $this->description,
            'is_emergency' => (bool) $this->is_emergency,
            'is_paid' => $this->is_paid,
            'total_info' => [
                'sub_total' => $this->sub_total,
                'tax_amount' => $this->tax_amount,
                'total_amount' => $this->total_amount,
            ],
            'quotation' => $this->quotation ? [
                'id' => $this->quotation->id,
                'quote_number' => $this->quotation->quote_number,
                'sections' => $this->quotation->sections()->with('items')->get(),
            ] : null,
            'payment' => $this->payment ? [
                'id' => $this->payment->id,
                'payment_code' => $this->payment->payment_code,
                'amount' => $this->payment->amount,
                'gateway' => $this->payment->gateway,
                'payment_type' => $this->payment->payment_type,
                'card_last4' => $this->payment->cr_last4,
                'cardholder_name' => $this->payment->card_last_name,
                'transaction_id' => $this->payment->transaction_id,
                'ref_no' => $this->payment->ref_no,
                'created_at' => optional($this->payment->created_at)->toDateTimeString(),
            ] : null,
            'payments' => $this->payments ? $this->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_code' => $payment->payment_code,
                    'amount' => $payment->amount,
                    'gateway' => $payment->gateway,
                    'payment_type' => $payment->payment_type,
                    'card_last4' => $payment->cr_last4,
                    'cardholder_name' => $payment->card_last_name,
                    'transaction_id' => $payment->transaction_id,
                    'ref_no' => $payment->ref_no,
                    'created_at' => optional($payment->created_at)->toDateTimeString(),
                ];
            }) : [],

            'status'       => $this->status,

            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
        ];
    }

    public function toTodayArray(): array
    {
        $status = strtolower((string) $this->status);
        $time = $this->start_time
            ? 'Today at ' . Carbon::parse($this->start_time)->format('g:i A')
            : 'Today';

        return [
            'id' => (string) $this->id,
            'title' => $this->appointment_no,
            'vendor' => optional($this->vendor)->name,
            'time' => $time,
            'status' => $this->status,
            'icon' => 'mdi-broom',
            'iconTone' => 'icon-bg',
            'statusTone' => 'appointment-status--' . ($status ?: 'pending'),
        ];
    }
}
