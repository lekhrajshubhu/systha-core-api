<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Quotation;

use Illuminate\Http\Request;
use Systha\Core\Models\Quote;
use App\Http\Controllers\Controller;
use Systha\Core\Models\ChatConversation;
use Systha\Core\Services\QuotationService;
use Systha\Core\Http\Resources\QuotationListResource;
use Systha\Core\Http\Resources\QuotationResource;
use Systha\Core\Models\QuoteModel;
use Systha\Core\Models\VendorModel;

/**
 * @group Platform
 * @subgroup Quotations
 */
class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $client = auth('platform')->user();


        $query = Quote::where('client_id', $client->id)->with(['vendor', 'quoteEnq.vendor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->filled('preferred_date')) {
            $query->whereDate('preferred_date', $request->preferred_date);
        }

        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortable = [
            'quote_number' => 'quote_number',
            'created_at' => 'created_at',
            'preferred_date' => 'preferred_date',
            'status' => 'status',
            'amount' => 'total',
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

        $quotations = $query->paginate($perPage);


        return response()->json([
            'data' => QuotationListResource::collection($quotations->items()),
            'meta' => [
                'current_page' => $quotations->currentPage(),
                'from' => $quotations->firstItem(),
                'last_page' => $quotations->lastPage(),
                'path' => $quotations->path(),
                'per_page' => $quotations->perPage(),
                'to' => $quotations->lastItem(),
                'total' => $quotations->total(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $quote = QuoteModel::with(['client', 'sections'])->findOrFail($id);
        $vendor = VendorModel::with('address')->find($quote->vendor_id);
        $quote->setRelation('vendor', $vendor);

        return response([
            "data" => new QuotationResource($quote)
        ], 200);
    }

    public function confirmQuotation(Request $request, $quotationId)
    {
        $quotation = Quote::find($quotationId);
        $quotation->status = "confirmed";
        $quotation->save();

        if ($quotation->quoteEnq) {
            $quotation->quoteEnq->update([
                "status" => "confirmed",
            ]);

            $message = 'Quotation Accepted : <span class="quote" data-id="' . $quotation->id . '">' . $quotation->quote_number . '</span>';
            $conversation = ChatConversation::where(['table_name' => 'enquiries', 'table_id' => $quotation->enq_id])->first();
            if ($conversation) {
                $conversation->messages()->create([
                    "message" => $message,
                    "table_from" => "clients",
                    "table_from_id" => $quotation->quoteEnq->client_id,
                    "table_to" => "vendors",
                    "table_to_id" => $quotation->quoteEnq->vendor_id,
                ]);
            }
        }

        // $quotation->emailToVendor("quotation_accepted", "Quotation Accepted");
        $quoteService = app(QuotationService::class);

        // dd($quoteService);
        // $quoteService->quotationConfirmationEmail($quotation);
        // dd("test");
        return response([
            "message" => "Quotation ". $quotation->quote_number." confirmed",
            "data" => $quotation,
        ], 200);
    }
    public function acceptQuotation(Request $request, $quotationId)
    {
        $quotation = Quote::find($quotationId);
        $quotation->status = "accepted";
        $quotation->save();

        if ($quotation->quoteEnq) {
            $quotation->quoteEnq->update([
                "status" => "accepted",
            ]);

            $message = 'Quotation Accepted : <span class="quote" data-id="' . $quotation->id . '">' . $quotation->quote_number . '</span>';
            $conversation = ChatConversation::where(['table_name' => 'enquiries', 'table_id' => $quotation->enq_id])->first();
            if ($conversation) {
                $conversation->messages()->create([
                    "message" => $message,
                    "table_from" => "clients",
                    "table_from_id" => $quotation->quoteEnq->client_id,
                    "table_to" => "vendors",
                    "table_to_id" => $quotation->quoteEnq->vendor_id,
                ]);
            }
        }

        // $quotation->emailToVendor("quotation_accepted", "Quotation Accepted");
        $quoteService = app(QuotationService::class);

        // dd($quoteService);
        // $quoteService->quotationConfirmationEmail($quotation);
        // dd("test");
        return response([
            "message" => "Quotation ". $quotation->quote_number." confirmed",
            "data" => $quotation,
        ], 200);
    }
    public function rejectQuotation(Request $request, $quotationId)
    {
        $quotation = Quote::find($quotationId);
        $quotation->status = "rejected";
        $quotation->save();

        if ($quotation->quoteEnq) {
            $quotation->quoteEnq->update([
                "status" => "rejected",
            ]);

            $message = 'Quotation Rejected : <span class="quote" data-id="' . $quotation->id . '">' . $quotation->quote_number . '</span>';
            $conversation = ChatConversation::where(['table_name' => 'enquiries', 'table_id' => $quotation->enq_id])->first();
            if ($conversation) {
                $conversation->messages()->create([
                    "message" => $message,
                    "table_from" => "clients",
                    "table_from_id" => $quotation->quoteEnq->client_id,
                    "table_to" => "vendors",
                    "table_to_id" => $quotation->quoteEnq->vendor_id,
                ]);
            }
        }

        // $quotation->emailToVendor("quotation_accepted", "Quotation Accepted");
        $quoteService = app(QuotationService::class);

        // dd($quoteService);
        // $quoteService->quotationConfirmationEmail($quotation);
        // dd("test");
        return response([
            "message" => "Quotation ". $quotation->quote_number." rejected",
            "data" => $quotation,
        ], 200);
    }
}
