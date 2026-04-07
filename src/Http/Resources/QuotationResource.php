<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class QuotationResource extends JsonResource
{
    public function toArray($request): array
    {
        return $this->shouldUseDetailResponse($request)
            ? $this->detailResponse()
            : $this->listResponse();
    }

    public function listResponse(): array
    {
        $status = $this->mapStatus((string) $this->status);
        $iconMap = [
            'Received' => ['icon' => 'mdi-flash-outline', 'iconTone' => 'quote-icon--peach'],
            'Accepted' => ['icon' => 'mdi-check-decagram', 'iconTone' => 'quote-icon--green'],
            'Declined' => ['icon' => 'mdi-close-octagon-outline', 'iconTone' => 'quote-icon--red'],
        ];
        $iconMeta = $iconMap[$status] ?? $iconMap['Received'];

        return [
            'id' => (int) $this->id,
            'vendor' => $this->resolveVendorName(),
            'vendor_logo' => $this->resolveVendorLogo(),
            'service' => $this->resolveServiceLabel(),
            'amount' => $this->formatAmount((float) ($this->total ?? 0)),
            'date' => $this->formatDate(),
            'status' => $status,
            'icon' => $iconMeta['icon'],
            'iconTone' => $iconMeta['iconTone'],
        ];
    }

    public function detailResponse(): array
    {
        $client = $this->resolveClientRelation();
        $vendor = $this->resolveVendorRelation();
        $vendorAddress = $vendor?->address;

        return [
            'id'            => $this->id,
            'quote_number' => $this->quote_number,
            'expiry_date'    => $this->expiry_date,
            'description' => $this->desc,

            'client'        => [
                "id"       => optional($client)->id,
                "name"     => trim((string) optional($client)->fname . ' ' . (string) optional($client)->lname),
                "email"    => optional($client)->email,
                "phone_no" => optional($client)->phone_no,
            ],

            'schedule_label'   => $this->resolveScheduleLabel(),

            'vendor' => [
                "name" => optional($vendor)->name,
                "vendor_code" => optional($vendor)->vendor_code,
                "logo" => optional($vendor)->logo,
                "address" => [
                    "add1" => optional($vendorAddress)->add1,
                    "add2" => optional($vendorAddress)->add2,
                    "city" => optional($vendorAddress)->city,
                    "state" => optional($vendorAddress)->state,
                    "zip" => optional($vendorAddress)->zip,
                ],
            ],
            "sections" => $this->sections()->with('items')->get()->map(function ($section) {
                return [
                    'title' => $section->title,
                    'description' => $section->description,
                    'items' => $section->items->map(function ($item) {
                        return [
                            'title' => $item->title,
                            'price' => (float) ($item->unit_price ?? 0),
                            'qty' => (float) ($item->qty ?? 0),
                            'line_total' => (float) ($item->line_total ?? 0),
                        ];
                    })->values(),
                ];
            })->values(),
            'status'       => $this->status,
            'created_at'   => optional($this->created_at)->format('M d, Y h:i A'),
            'total_info' => [
                "sub_total" => (float) $this->sub_total,
                "tax_amount" => (float) $this->tax,
                "total_amount" => (float) $this->total,
            ],
        ];
    }

    protected function resolveServiceLabel(): string
    {
        $firstService = $this->quoteServices->first();
        $serviceName = $firstService->service_name ?? $firstService->item_name ?? 'Service';
        $inquiryNo = optional($this->quoteEnq)->enq_no;

        // if (!empty($inquiryNo)) {
        //     return $serviceName . ' · ' . $inquiryNo;
        // }

        // return (string) $serviceName;
        return (string) $inquiryNo;
    }

    protected function resolveVendorName(): string
    {
        $name = optional($this->resolveVendorRelation())->name
            ?? optional(optional($this->quoteEnq)->vendor)->name;

        return (string) ($name ?: 'Unknown Vendor');
    }

    protected function resolveVendorLogo(): ?string
    {
        return optional($this->resolveVendorRelation())->logo
            ?? optional(optional($this->quoteEnq)->vendor)->logo;
    }

    protected function resolveVendorRelation()
    {
        return $this->resource->getRelationValue('vendor')
            ?? $this->resource->vendor()->first();
    }

    protected function resolveClientRelation()
    {
        return $this->resource->getRelationValue('client')
            ?? (method_exists($this->resource, 'client') ? $this->resource->client()->first() : null);
    }

    protected function formatAmount(float $amount): string
    {
        $formatted = number_format($amount, 2, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return '$' . $formatted;
    }

    protected function formatDate(): string
    {
        $dateValue = $this->preferred_date ?: optional($this->created_at)->toDateString();
        if (empty($dateValue)) {
            return 'No date';
        }

        return Carbon::parse($dateValue)->format('M d, Y');
    }

    protected function resolveScheduleLabel(): string
    {
        $date = $this->preferred_date ? Carbon::parse($this->preferred_date)->format('M d, Y') : null;
        $time = $this->preferred_time ? Carbon::parse($this->preferred_time)->format('h:i A') : null;

        if ($date && $time) {
            return $date . ' at ' . $time;
        }

        if ($date) {
            return $date;
        }

        if ($time) {
            return $time;
        }

        return 'No schedule';
    }

    protected function mapStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        $map = [
            'new' => 'Received',
            'received' => 'Received',
            'pending' => 'Received',
            'quoted' => 'Received',
            'accepted' => 'Accepted',
            'confirmed' => 'Accepted',
            'declined' => 'Declined',
            'rejected' => 'Declined',
            'cancelled' => 'Declined',
        ];

        return $map[$normalized] ?? 'Received';
    }

    protected function shouldUseDetailResponse(Request $request): bool
    {
        $route = $request->route();
        if (!$route) {
            return true;
        }

        $routeName = $route->getName();
        $detailRoutes = [
            'platform.quotation.detail',
        ];

        if (in_array($routeName, $detailRoutes, true)) {
            return true;
        }

        return $route->getActionMethod() === 'show';
    }
}
