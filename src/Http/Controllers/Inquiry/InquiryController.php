<?php

namespace Systha\Core\Http\Controllers\Inquiry;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Systha\Core\Services\ClientService;
use Systha\Core\Http\Controllers\BaseController;

class InquiryController extends BaseController
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        parent::__construct();
        $this->clientService = $clientService;
    }
    public function store(Request $request)
    {
        // Validate input data
        // $validator = $this->clientService->validateClientData($request->all());
        $data = $request->all();
        $validator = Validator::make($data, [
            'service_category.*' => 'integer|exists:service_categories,id',
            'date' => 'required|date|date_format:Y-m-d',
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required',
            'add1' => 'required|string|max:255',
            'add2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required',
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // dd($data);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            // Create or update client
            $client = $this->clientService->createOrUpdateClient($request->all(), $this->vendor);

            // Create quote inquiry
            $quoteEnq = $this->clientService->createQuoteEnquiry($client, $request->all(), $this->vendor);

            $this->sendEmail($quoteEnq);
            // Return success response
            return view($this->viewPath.'::components.template.schedule_service_success', compact('quoteEnq'));
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 422);
        }
    }

    public function sendEmail($quoteEnq)
    {

        $logoPath = $this->template->storage_path . "/venndors/attachments/" . $quoteEnq->vendor->profile_pic;

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
        // Send email to the vendor
        Mail::send('core::mail.inquiry_to_vendor', [
            'client' => $quoteEnq->client,
            'vendor' => $quoteEnq->vendor,
            "quoteEnq" => $quoteEnq,
            "logo" => $logo,
        ], function ($message) use ($quoteEnq) {
            $message->from($quoteEnq->client->email, $quoteEnq->client->fullName)
                ->to($quoteEnq->vendor->contact->email)
                ->subject('New Schedule Service');
        });

        // Send email to the client
        Mail::send('core::mail.inquiry_to_client', [
            'client' => $quoteEnq->client,
            'vendor' => $quoteEnq->vendor,
            "quoteEnq" => $quoteEnq,
            "logo" => $logo,
        ], function ($message) use ($quoteEnq) {
            $message->from($quoteEnq->vendor->contact->email, $quoteEnq->vendor->name)
                ->to($quoteEnq->client->email)
                ->subject('Your Service Schedule');
        });
    }
}
