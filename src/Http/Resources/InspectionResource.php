<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InspectionResource extends JsonResource
{
    public function toArray($request): array
    {
        $meta = is_array($this->inquiry_info) ? $this->inquiry_info : [];

        return [
            'id' => (int) $this->id,
            'inspection_no' => (string) $this->enq_no,
            'description' => (string) ($this->desc ?? ''),
            'note' => (string) ($meta['note'] ?? ''),
            'status' => (string) ($this->status ?? 'new'),
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'vendor' => [
                'id' => optional($this->vendor)->id,
                'name' => (string) (optional($this->vendor)->name ?? ''),
                'vendor_code' => (string) (optional($this->vendor)->vendor_code ?? ''),
            ],
            'contact' => [
                'name' => trim((string) ((optional($this->client)->fname ?? '') . ' ' . (optional($this->client)->lname ?? ''))),
                'phone' => (string) (optional($this->client)->phone_no ?? ''),
                'email' => (string) (optional($this->client)->email ?? ''),
            ],
            'service_area' => [
                'add1' => $this->serviceAddress?->add1,
                'city' => $this->serviceAddress?->city,
                'state' => $this->serviceAddress?->state,
                'zip' => $this->serviceAddress?->zip,
            ],
            'photos' => $this->attachments->values()->map(function ($usage) {
                $attachment = $usage->attachment;
                $path = (string) ($attachment?->file_name ?? '');

                return [
                    'path' => $path,
                    'url' => $attachment?->url,
                    'description' => (string) ($usage->meta['description'] ?? ''),
                ];
            }),
        ];
    }
}
