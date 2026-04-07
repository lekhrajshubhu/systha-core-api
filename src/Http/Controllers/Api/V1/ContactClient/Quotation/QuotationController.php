<?php

namespace Systha\Core\Http\Controllers\Api\V1\ContactClient\Quotation;

use Illuminate\Http\Request;
use Systha\Core\Models\Quote;
use App\Http\Controllers\Controller;
use Systha\Core\Models\ChatConversation;
use Systha\Core\Services\QuotationService;
use Systha\Core\Http\Resources\QuotationResource;


/**
 * @group Contacts
 * @subgroup Quotations
 */
class QuotationController extends Controller
{

    public function show(Request $request, $id)
    {
        $quote = Quote::find($id);
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
        $quoteService->quotationConfirmationEmail($quotation);
        // dd("test");
        return response([
            "message" => "Quotation ". $quotation->quote_number." confirmed",
            "data" => $quotation,
        ], 200);
    }
}
