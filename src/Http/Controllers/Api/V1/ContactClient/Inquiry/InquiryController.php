<?php

namespace Systha\Core\Http\Controllers\Api\V1\ContactClient\Inquiry;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Systha\Core\Models\QuoteEnq;
use Systha\Core\Http\Resources\InquiryResource;

/**
 * @group Contacts
 * @subgroup Inquiries
 */
class InquiryController extends Controller
{

    public function index(Request $request)
    {
        $contact = auth('contacts')->user();

        $query = QuoteEnq::where('client_id', $contact->table_id)->with('address');

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

        $inquiries = $query->latest()->get();

        return InquiryResource::collection($inquiries);
    }


    public function show(Request $request, $id)
    {
        $quote = QuoteEnq::find($id);
        $quote->load('quotes', 'address', 'services');

        return response([
            'data' => new InquiryResource($quote)
        ], 200);
    }
}
