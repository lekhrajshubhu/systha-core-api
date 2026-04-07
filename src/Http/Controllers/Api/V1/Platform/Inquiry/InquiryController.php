<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Inquiry;

use Illuminate\Http\Request;
use Systha\Core\Http\Controllers\Api\V1\Platform\PlatformBaseController;
use Systha\Core\Models\QuoteEnq;
use Systha\Core\Http\Resources\InquiryListResource;
use Systha\Core\Http\Resources\InquiryResource;
use Systha\Core\Models\InquiryModel;

/**
 * @group Platform
 * @subgroup Inquiries
 */
class InquiryController extends PlatformBaseController
{
    public function inquiryList(Request $request)
    {

        // header('Access-Control-Allow-Origin: *');
        // dd($this->user);
        $query = QuoteEnq::where('client_id', $this->user->id)
            ->with(['address', 'vendor', 'inquiryService.service', 'attachments.attachment'])
            ->withCount(['quotes']);

        // Filter by date (exact date or preferred date)
        // if ($request->filled('date')) {
        //     $query->whereDate('created_at', $request->date);
        // } elseif ($request->filled('preferred_date')) {
        //     $query->whereDate('preferred_date', $request->preferred_date);
        // }

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
            'data' => InquiryListResource::collection($inquiries),
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
        $quote = InquiryModel::query()
            ->with([
                'serviceAddress',
                'vendor',
                'attachments',
                'quotes',
                'client',
                'inquiryService',
            ])
            ->findOrFail($id);

        return response([
            'data' => new InquiryResource($quote)
        ], 200);
    }
}
