<?php

/**
 * THIS INTELLECTUAL PROPERTY IS COPYRIGHT Ⓒ 2020
 * SYSTHA TECH LLC. ALL RIGHT RESERVED
 * -----------------------------------------------------------
 * SALES@SYSTHATECH.COM 
 * 512 903 2202
 * WWW.SYSTHATECH.COM
 * -----------------------------------------------------------
 */


namespace Systha\Core\Services;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Systha\Core\Helpers\Formatter;
use Systha\Core\Models\ChatConversation;
use Systha\Core\Models\EmailTemplate;
use Systha\Core\Models\QuoteEnq;
use Systha\Core\Models\Vendor;

class InquiryService
{
    public function createInquiry(array $data)
    {
        $validated = Validator::make($data, [
            'client_id' => 'required|exists:clients,id',
            'vendor_id' => 'required|exists:vendors,id',
            'address_id' => 'required|exists:addresses,id',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|string',
            'reviewable_history' => 'nullable|array',
            'inquiry_info' => 'nullable|array',
            'service_list' => 'required|array',
        ])->validate();

        // dd($validated);
        try {
            return DB::transaction(function () use ($validated) {
                $quoteEnq = QuoteEnq::create([
                    'client_id' => $validated['client_id'],
                    'vendor_id' => $validated['vendor_id'],
                    'address_id' => $validated['address_id'],
                    'preferred_date' => $validated['preferred_date'],
                    'preferred_time' => $validated['preferred_time'],
                    'reviewable_history' => $validated['reviewable_history'] ? json_encode($validated['reviewable_history']) : null,
                    'inquiry_info' => isset($validated['inquiry_info']) ? json_encode($validated['inquiry_info']) : null,
                    'is_recurring' => $validated['is_recurring'],
                    'recurring_frequency' => $validated['recurring_frequency'],
                    'state' => 'publish',
                    'status' => 'new',
                ]);

                $this->addServices($quoteEnq, $validated['service_list']);
                $this->sendEmail($quoteEnq);

                return $quoteEnq;
            });
        } catch (\Throwable $e) {
            Log::error('Inquiry creation failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            throw $e;
        }
    }

    public function addServices(QuoteEnq $quoteEnq, $serviceList): array
    {
        $quoteEnqServices = [];

        foreach ($serviceList as $service) {
            $quoteEnqServices[] = $quoteEnq->enqServices()->create([
                'enq_id' => $quoteEnq->id,
                'service_id' => $service['service_id'],
                'item_name' => $service['service_name'],
                'quantity' => $service['quantity'] ?? 1,
                'price' => $service['price'],
                'unit_type' => $service['type'],
            ]);
        }
        return $quoteEnqServices;
    }



    public function sendEmail(QuoteEnq $quoteEnq)
    {
        $templateService = new EmailTemplateService();
        $client = $quoteEnq->client;
        $vendor = Vendor::find($quoteEnq->vendor_id);

        if (!$client || empty($client->email)) {
            Log::warning('Missing client or email for inquiry ID: ' . $quoteEnq->id);
            return;
        }
        $serviceNames = $quoteEnq->enqServices->pluck('item_name')->toArray();
        $joinedServiceNames = implode(', ', $serviceNames);

        $address = $quoteEnq->address;

        $serviceLocation = trim(
            ($address->add1 ?? '') . ', ' .
                ($address->city ?? '') . ', ' .
                ($address->state ?? '') . ', ' .
                ($address->zip ?? '') . ', ' .
                strtoupper($address->country_code ?? '')
        );


        // Prepare template data
        $data = [
            'customer_name'    => $client->fullName,
            'customer_phone'   => $client->phone_no,
            'customer_email'   => $client->email,
            'company_name'     => $vendor->name,
            'service_name'     => $joinedServiceNames,
            'vendor_website'   => $vendor->website ?? (env('APP_WEBSITE') ?? config('app.url', 'https://example.com')),
            'company_website'  => env('APP_WEBSITE') ?? config('app.url', 'https://example.com'),
            'preferred_date'    => Formatter::bladeDate($quoteEnq->preferred_date),
            'preferred_time'    => Formatter::formatToTime($quoteEnq->preferred_time),
            'service_location' => $serviceLocation,
        ];
        // dd($data);

        $emailTemplate = EmailTemplate::where('code', 'new_inquiry')->first();

        // dd($emailTemplate);
        if (!$emailTemplate) {
            Log::warning('Email template not found: new_inquiry');
            return;
        }
        // dd($emailTemplate, $data);

        try {
            $rendered = $templateService->load($emailTemplate, $data)->render();

            // $msgService = new MessageService();
            // $msgService->sendMessage($quoteEnq, $vendor, $client, $rendered['msgContent']);

            // dd($rendered);


            // $mailService = new CustomMailService($vendor);
            $mailService = app(CustomMailService::class,['vendor' => $vendor]);



            $emailData = [
                'from_email'  => $vendor->contact->email ?? 'noreply@example.com',
                'from_name'   => $vendor->name,
                'to_email'    => $client->email ?? 'sales@example.com',
                'to_name'     => $client->fullName,
                'subject'     => $rendered['subject'],
                'message'     => $rendered['content'],


                'cc'          => [],
                'bcc'         => [],
                'attachments' => [],
                'table_name'  => 'quote_enqs',
                'table_id'    => $quoteEnq->id,
            ];


            $emailToCustomer = $mailService->send($emailData);
     
            $emailData = [
                'from_email'  => 'noreply@email.com',
                'from_name'   => $vendor->name,
                'to_email'    => $vendor->contact->email ?? 'sales@example.com',
                'to_name'     => $vendor->name,
                'subject'     => $rendered['vendor_subject'],
                'message'     => $rendered['vendor_content'],

                'cc'          => [],
                'bcc'         => [],
                'attachments' => [],
                'table_name'  => 'quote_enqs',
                'table_id'    => $quoteEnq->id,
            ];

            $emailToVendor =  $mailService->send($emailData);




            return [
                'emailToCustomer' => $emailToCustomer,
                'emailToVendor' => $emailToVendor,
            ];
        } catch (\Throwable $e) {
            return response([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500);
        }
    }










    public function addQuote(QuoteEnq $quoteEnq)
    {
        $quote = $quoteEnq->quotes()->create([
            "enq_id" => $quoteEnq->id,
            "vendor_id" => $quoteEnq->vendor_id,
            "client_id" => $quoteEnq->client_id,
            "preferred_date" => $quoteEnq->preferred_date,
            "preferred_time" => $quoteEnq->preferred_time,
            "is_recurring" => $quoteEnq->is_recurring,
            "recurring_frequency" => $quoteEnq->recurring_frequency,
            "status" => "pending"
        ]);

        $service_ids = [];
        foreach ($quoteEnq->enqServices as $service) {
            $service_ids[] =  $service->service_id;
            $quote->quoteServices()->create([
                "table_name" => "services",
                "table_id" => $service->service_id,
                "service_id" => $service->service_id,
                "service_name" => $service->service_name,
                "price" => $service->price,
                "quantity" => $service->quantity,
            ]);
        }

        $appointment = null;
        if ($quoteEnq->is_recurring) {
            $data = [
                "client_id" => $quote->client_id,
                "vendor_id" => $quote->vendor_id,
                "preferred_date" => $quote->preferred_date,
                "preferred_time" => $quote->preferred_time,
                "service_ids" => $service_ids,
                "recurring_frequency" => $quote->recurring_frequency,
                "is_recurring" => $quote->is_recurring,
                "service_list" => $quoteEnq->enqServices,
            ];
            // app(CustomMailService::class,['vendor' => $vendor]);

            $appointmentServiceContainer =  app(AppointmentServiceContainer::class);
            $appointment = $appointmentServiceContainer->storeAppointment($data);
        }

        return [
            "quote" => $quote,
            "appointment" => $appointment
        ];
    }

    public function handleMessage(QuoteEnq $quoteEnq, string $message)
    {
        // Create chat conversation
        $conv = ChatConversation::create([
            "table_name" => "enquiries",
            "table_id" => $quoteEnq->id,
            "title" => $quoteEnq->enq_no,
            "vendor_id" => $quoteEnq->vendor_id,
            "client_id" => $quoteEnq->client_id,
            "is_active" => 1,
        ]);

        // Add members to the conversation
        $conv->members()->create([
            "table_name" => "vendors",
            "table_id" => $quoteEnq->vendor_id,
        ]);
        $conv->members()->create([
            "table_name" => "clients",
            "table_id" => $quoteEnq->client_id,
        ]);

        // Send a message
        $conv->messages()->create([
            "table_from" => "vendors",
            "table_from_id" => $quoteEnq->vendor_id,
            "table_to" => "clients",
            "table_to_id" => $quoteEnq->client_id,
            "message" => $message,
            "seen_client" => 1,
        ]);
        return $conv;
    }


    public function handleQuoteEnquiry(QuoteEnq $quoteEnq, string $message)
    {
        // Create chat conversation
        $conv = ChatConversation::create([
            "table_name" => "enquiries",
            "table_id" => $quoteEnq->id,
            "title" => $quoteEnq->enq_no,
            "vendor_id" => $quoteEnq->vendor_id,
            "client_id" => $quoteEnq->client_id,
            "is_active" => 1,
        ]);

        // Add members to the conversation
        $conv->members()->create([
            "table_name" => "vendors",
            "table_id" => $quoteEnq->vendor_id,
        ]);
        $conv->members()->create([
            "table_name" => "clients",
            "table_id" => $quoteEnq->client_id,
        ]);

        // Send a message
        $conv->messages()->create([
            "table_from" => "vendors",
            "table_from_id" => $quoteEnq->vendor_id,
            "table_to" => "clients",
            "table_to_id" => $quoteEnq->client_id,
            "message" => $message,
            "seen_vendor" => 1,
        ]);
    }
}
