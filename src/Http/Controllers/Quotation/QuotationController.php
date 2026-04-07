<?php
namespace Systha\Core\Http\Controllers\Quotation;

use Illuminate\Http\Request;
use Systha\Core\Models\Quote;
use Illuminate\Support\Facades\Mail;
use Systha\Core\Models\ChatConversation;
use Systha\Core\Services\MessageService;
use Systha\Core\Http\Controllers\BaseController;

class QuotationController extends BaseController
{
    public function accept(Request $request, $id){
        $quote = Quote::find($id);
        $quote->status = "confirmed";
        $quote->save();

        $inquiry = $quote->quoteEnq;
        $message = "
            <div>
                <p><strong>Quotation: </strong>" . $quote->quote_number . " <strong>has been confirmed.</strong></p>
            </div>
        ";
        $messageService = new MessageService();
        $this->sendEmailAccepted($quote);
        $chatConversation = $messageService->sendMessage($inquiry, $inquiry->vendor, $inquiry->client, $message);
        return view($this->viewPath . '::frontend.dashboard.quotation.quotation-detail', compact('quote', 'chatConversation'));

    }
    public function reject(Request $request, $id)
    {
        try {
            $quote = Quote::find($id);
            $quote->status = "cancelled";
            $quote->cancel_reason = $request->reason ?? '';
            $quote->cancelled_at = now();
            $quote->save();
          

            $inquiry = $quote->quoteEnq;
            $message = "
                <div>
                    <p><strong>Quotation: </strong>" . $quote->quote_number . " <strong>has been cancelled.</strong></p>
                    <p><strong>Reason: </strong>" . $request->reason . "</p>
                </div>
            ";
            $messageService = new MessageService();
            $chatConversation = $messageService->sendMessage($inquiry, $inquiry->vendor, $inquiry->client, $message);
            $this->sendEmail($quote);
            return view($this->viewPath . '::frontend.dashboard.quotation.quotation-detail', compact('quote', 'chatConversation'));
        
        } catch (\Throwable $th) {
            return response(["data" =>$quote, "error" => "Failed to cancel the quotation. " . $th->getMessage()], 200);
        }
    }
    public function sendEmailAccepted($quotation){

        $inquiry = $quotation->quoteEnq;

        $logoPath = $this->template->storage_path . "/venndors/attachments/" . $inquiry->vendor->profile_pic;

        if (file_exists($logoPath)) {
            $imageData = base64_encode(file_get_contents($logoPath));
            $mimeType = mime_content_type($logoPath);
        } else {
            $defaultPath = public_path('images/noimage.png');
            $imageData = base64_encode(file_get_contents($defaultPath));
            $mimeType = mime_content_type($defaultPath);
        }
        // Return Base64 image string
        $logo = "data:$mimeType;base64," . $imageData;
        Mail::send('core::mail.quotation_accepted', [
            'client' => $inquiry->client,
            'vendor' => $inquiry->vendor,
            'quotation' => $quotation,
            "inquiry" => $inquiry,
            "logo" => $logo,
        ], function ($message) use ($inquiry) {
            $message->from($inquiry->client->email, $inquiry->client->fullName)
                ->to($inquiry->vendor->contact->email)
                ->subject('Quotation Accepted');
        });
    }
    public function sendEmail($quotation){

        $inquiry = $quotation->quoteEnq;

        $logoPath = $this->template->storage_path . "/venndors/attachments/" . $inquiry->vendor->profile_pic;

        if (file_exists($logoPath)) {
            $imageData = base64_encode(file_get_contents($logoPath));
            $mimeType = mime_content_type($logoPath);
        } else {
            $defaultPath = public_path('images/noimage.png');
            $imageData = base64_encode(file_get_contents($defaultPath));
            $mimeType = mime_content_type($defaultPath);
        }
        // Return Base64 image string
        $logo = "data:$mimeType;base64," . $imageData;
        Mail::send('core::mail.quotation_rejected', [
            'client' => $inquiry->client,
            'vendor' => $inquiry->vendor,
            'quotation' => $quotation,
            "inquiry" => $inquiry,
            "logo" => $logo,
        ], function ($message) use ($inquiry) {
            $message->from($inquiry->client->email, $inquiry->client->fullName)
                ->to($inquiry->vendor->contact->email)
                ->subject('Quotation Rejected');
        });
    }

    public function message(Request $request, $quotationId){
        $quote = Quote::find($quotationId);

        $conv = ChatConversation::where([
            "table_name" => "enquiries",
            "table_id" => $quote->enq_id,
        ])
        ->with('messages.to', 'messages.from')  // Fix the typo here
        ->first();
        $user = auth('webContact')->user();
        return view($this->viewPath . '::frontend.dashboard.message.message', compact('conv','user'));
    }
}

