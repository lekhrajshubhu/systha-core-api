<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class InquiryListResource extends JsonResource
{
    public function toArray($request): array
    {
        $rawStatus = strtolower((string) $this->status);
        $status = $this->mapStatus($rawStatus);

        $iconMap = [
            'new' => ['icon' => 'mdi-message-badge-outline', 'iconTone' => 'card-icon--amber'],
            'quoted' => ['icon' => 'mdi-file-document-edit-outline', 'iconTone' => 'card-icon--blue'],
            'accepted' => ['icon' => 'mdi-wrench', 'iconTone' => 'card-icon--peach'],
        ];
        $iconMeta = $iconMap[$status] ?? $iconMap['new'];

        $title = $this->resolveTitle();

        return [
            'id' => (int) $this->id,
            'inquiry_no' => (string) $this->enq_no,
            'title' => $title,
            'vendor' => (string) (optional($this->vendor)->name ?? 'Unknown Vendor'),
            'logo' => (string) (optional($this->vendor)->logo),
            'attachment_url' => $this->resolveFirstAttachmentUrl(),
            'attachment_urls' => $this->resolveAttachmentUrls(),
            'date' => $this->formatDateLabel(),
            'quotes_count' => (int) ($this->quotes_count ?? 0),
            'status' => $status,
            'icon' => $iconMeta['icon'],
            'iconTone' => $iconMeta['iconTone'],
        ];
    }

    private function mapStatus(string $status): string
    {
        $map = [
            'new' => 'new',
            'quoted' => 'quoted',
            'accepted' => 'accepted',
        ];

        return $map[$status] ?? 'new';
    }

    private function formatDateLabel(): string
    {
        $dateValue = $this->created_at;
        if (!$dateValue) {
            return 'No date';
        }

        return Carbon::parse($dateValue)->format('M j, Y g:i A');
    }

    private function resolveTitle(): string
    {
        $requestType = $this->resolveRequestType();

        if ($requestType === 'inspection') {
            return 'Inspection Request';
        }

        $serviceName = optional($this->inquiryService?->service)->service_name ?? $this->inquiryService?->item_name;

        if (!empty($serviceName)) {
            return (string) $serviceName;
        }
        return (string) ($this->enq_no ?: ('Inquiry #' . $this->id));
    }

    private function resolveRequestType(): ?string
    {
        if (! empty($this->type)) {
            return (string) $this->type;
        }

        $inquiryInfo = $this->inquiry_info;

        if (is_string($inquiryInfo)) {
            $inquiryInfo = json_decode($inquiryInfo, true);
        }

        if (is_array($inquiryInfo)) {
            return isset($inquiryInfo['request_type'])
                ? (string) $inquiryInfo['request_type']
                : null;
        }

        return null;
    }

    private function resolveFirstAttachmentUrl(): ?string
    {
        return $this->attachments
            ?->map(fn($usage) => $usage->attachment?->url)
            ->first(fn($url) => ! empty($url));
    }

    private function resolveAttachmentUrls(): array
    {
        if (! $this->attachments) {
            return [];
        }

        return $this->attachments
            ->map(fn($usage) => $usage->attachment?->url)
            ->filter(fn($url) => ! empty($url))
            ->values()
            ->all();
    }
}
