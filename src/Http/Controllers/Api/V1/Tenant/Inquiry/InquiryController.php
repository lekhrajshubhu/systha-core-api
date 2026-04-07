<?php

namespace Systha\Core\Http\Controllers\Api\V1\Tenant\Inquiry;

use Illuminate\Http\Request;
use Systha\Core\Http\Controllers\Api\V1\Tenant\BaseController;
use Systha\Core\Models\QuoteEnq;
use Systha\Core\Http\Resources\InquiryResource;

/**
 * @group Tenant
 * @subgroup Inquiries
 */
class InquiryController extends BaseController
{

    public function index(Request $request)
    {
        $auth = auth('vendor_client')->user();
        $client = $auth?->client;
        $vendorId = $auth?->vendor_id ?? $this->vendor?->id;

        if (!$client || !$vendorId) {
            return response()->json([
                'message' => 'Unable to resolve vendor client context.',
            ], 403);
        }

        $query = QuoteEnq::where([
            'client_id' => $client->id,
            'vendor_id' => $vendorId,
        ])
            ->with('address', 'vendor');

        // Filter by date (exact date or preferred date)
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->filled('preferred_date')) {
            $query->whereDate('preferred_date', $request->preferred_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $query->where('status', '!=', 'converted');

        // Search keyword
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('enq_no', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortable = [
            'inquiry_no' => 'enq_no',
            'created_at' => 'created_at',
            'preferred_date' => 'preferred_date',
            'status' => 'status',
        ];

        if ($sortBy && array_key_exists($sortBy, $sortable)) {
            $query->orderBy($sortable[$sortBy], $sortOrder);
        } else {
            $query->latest();
        }

        $perPage = (int) $request->get('per_page', 15);
        if ($perPage < 1) {
            $perPage = 15;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $inquiries = $query->paginate($perPage);

        return response()->json([
            'data' => InquiryResource::collection($inquiries->items()),
            'meta' => [
                'current_page' => $inquiries->currentPage(),
                'from' => $inquiries->firstItem(),
                'last_page' => $inquiries->lastPage(),
                'path' => $inquiries->path(),
                'per_page' => $inquiries->perPage(),
                'to' => $inquiries->lastItem(),
                'total' => $inquiries->total(),
            ],
        ]);
    }


    public function show(Request $request, $id)
    {
        $auth = auth('vendor_client')->user();
        $clientId = $auth?->client_id;
        $vendorId = $auth?->vendor_id ?? $this->vendor?->id;

        $quote = QuoteEnq::where('id', $id)
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->firstOrFail();

        $quote->load('quotes', 'address', 'services');

        return response([
            'data' => new InquiryResource($quote)
        ], 200);
    }
}
