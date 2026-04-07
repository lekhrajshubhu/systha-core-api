<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class InspectionListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'inspection_no' => (string) $this->enq_no,
            'title' => (string) ($this->desc ?: 'Inspection Request'),
            'vendor' => (string) (optional($this->vendor)->name ?? 'Unknown Vendor'),
            'logo' => (string) (optional($this->vendor)->logo ?? ''),
            'date' => $this->created_at
                ? Carbon::parse($this->created_at)->format('M j, Y g:i A')
                : 'No date',
            'status' => (string) ($this->status ?: 'new'),
            'photo_count' => (int) $this->files->count(),
        ];
    }
}
