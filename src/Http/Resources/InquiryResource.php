<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        $service = $this->inquiryService;
        $serviceModel = $service?->service;
        $quotes = $this->quotes ?? collect();

        return [
            'id' => (int) $this->id,
            'inquiry_no' => (string) $this->enq_no,
            'created_at' => optional($this->created_at)?->format('M j, Y g:i A'),

            // service
            'service' => [
                'category_name' => (string) ($serviceModel?->category?->name ?? ''),
                'name' => (string) ($serviceModel?->service_name ?? $service?->item_name ?? ''),
                'priority' => $this->is_emergency ? 'Urgent' : 'Normal',
                'description' => (string) ($this->desc ?? ''),
                'schedule_label' => $this->resolveScheduleLabel(),
            ],

            // client 
            'client' => [
                'name' => trim((string) ((optional($this->client)->fname ?? '') . ' ' . (optional($this->client)->lname ?? ''))),
                'phone' => (string) (optional($this->client)->phone_no ?? ''),
                'avatar' => (string) (optional($this->client)->avatar ?? ''),
            ],

            // service area
            'service_area' => [
                'address_line' => $this->serviceAddress?->add1,
                'city' => $this->serviceAddress?->city,
                'state' => $this->serviceAddress?->state,
                'zip' => $this->serviceAddress?->zip,
            ],

            // quotations
            'quotes' => $quotes->map(function ($quote) {
                return [
                    'id' => $quote->id,
                    'quote_no' => (string) $quote->quote_number,
                    'vendor_name' => (string) ($quote->vendor?->name ?? $this->vendor?->name ?? ''),
                    'price' => '$' . number_format((float) ($quote->total), 2),
                    'date' => optional($quote->created_at)?->format('M j, Y'),
                    'status' => Str::title((string) $quote->status),
                ];
            })->values(),

            // attachments
            'attachments' => collect($this->attachments)->map(function ($usage) {
                $attachment = $usage->attachment;
                return [
                    'url' => (string) ($attachment?->url ?? ''),
                    'description' => (string) ($usage->meta['description'] ?? ''),
                ];
            })->values(),

            'type' => $this->type,

            // responses

            'responses' => $this->buildResponses(),

            // progress tiimneline
            'timeline' => $this->buildTimeline($quotes),
        ];
    }

    private function resolveScheduleLabel(): string
    {
        if ($this->preferred_date && $this->preferred_time) {
            return Carbon::parse($this->preferred_date . ' ' . $this->preferred_time)->format('M j, Y g:i A');
        }

        if ($this->preferred_date) {
            return Carbon::parse($this->preferred_date)->format('M j, Y');
        }

        return 'ASAP';
    }

    private function buildResponses(): array
    {
        $responses = $this->inquiry_info;

        if (is_string($responses)) {
            $responses = json_decode($responses, true);
        }

        if (!is_array($responses)) {
            return [];
        }

        return collect($responses)->map(function ($response) {
            return [
                'label' => $response['question'] ?? '',
                'value' => $response['value'] ?? '',
            ];
        })->filter(fn($response) => $response['label'] !== '' && $response['value'] !== '')->values()->all();
    }

    private function buildTimeline($quotes): array
    {
        $timeline = [
            [
                'title' => 'Inquiry Submitted',
                'time' => optional($this->created_at)?->format('g:i A'),
                'state' => $quotes->isEmpty() ? 'active' : 'done',
            ],
        ];

        $latestQuote = $quotes->sortByDesc('created_at')->first();

        if ($latestQuote) {
            $timeline[] = [
                'title' => 'Quotation Received',
                'time' => optional($latestQuote->created_at)?->format('g:i A'),
                'state' => 'active',
            ];
        }

        return $timeline;
    }
}
