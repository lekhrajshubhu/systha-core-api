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
use Systha\Core\Models\Quote;
use Systha\Core\Models\Vendor;
use Illuminate\Support\Facades\Validator;
use Systha\Core\Helpers\Formatter;

use Systha\Core\Models\EmailTemplate;



class QuotationService
{
    public function createQuotation(array $data)
    {
        $validated = Validator::make($data, [
            'enq_id' => 'required|exists:quote_enqs,id',
            'client_id' => 'required|exists:clients,id',
            'vendor_id' => 'required|exists:vendors,id',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|string',
            'service_list' => 'required|array',
            'quote_number' => 'required|string|unique:quotes,quote_number',
            'description' => 'nullable|string|max:2000',
            'expiry_date' => 'nullable|date',
        ])->validate();

        try {
            return DB::transaction(function () use ($validated) {
                $quotation = Quote::create([
                    'enq_id' => $validated['enq_id'],
                    'client_id' => $validated['client_id'],
                    'vendor_id' => $validated['vendor_id'],
                    'preferred_date' => $validated['preferred_date'],
                    'preferred_time' => $validated['preferred_time'],
                    'description' => $validated['description'] ?? null,
                    'quote_number' => $validated['quote_number'],
                    'expiry_date' => $validated['expiry_date'],
                    'table_name' => 'quote_enqs',
                    'table_id' => $validated['enq_id'],
                    'status' => "new"
                ]);

                $this->addServices($quotation, $validated['service_list']);
                $this->sendEmail($quotation);

                $quotation->quoteEnq()->update([
                    "status" => "quoted"
                ]);

                return $quotation;
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

    public function addServices(Quote $quote, $serviceList): array
    {
        $quoteServices = [];

        foreach ($serviceList as $service) {
            $quoteServices[] = $quote->quoteServices()->create([

                'service_id' => $service['service_id'],
                'service_name' => $service['item_name'],
                'price' => $service['price'],
                'amend_charge' => $service['amend_charge'] ?? 1,
                'cancel_charge' => $service['cancel_charge'] ?? 1,
                'table_name' => 'quotes',
                'table_id' => $quote->id,
            ]);
        }
        return $quoteServices;
    }



    public function sendEmail(Quote $quote)
    {
        $templateService = app(EmailTemplateService::class);

        $client = $quote->client;
        $vendor = Vendor::find($quote->vendor_id);

        if (!$client || empty($client->email)) {
            Log::warning('Missing client or email for inquiry ID: ' . $quote->id);
            return;
        }

        $serviceNames = $quote->quoteServices->pluck('service_name')->toArray();
        $joinedServiceNames = implode(', ', $serviceNames);

        //   header('Access-Control-Allow-Origin:*');

         $address = $quote->quoteEnq->address;

        $serviceLocation = trim(
            ($address->add1 ?? '') . ', ' .
                ($address->city ?? '') . ', ' .
                ($address->state ?? '') . ', ' .
                ($address->zip ?? '') . ', ' .
                strtoupper($address->country_code ?? '')
        );

        // Prepare template data
        $data = [
            'customer_name'           => $client->fullName,
            'customer_phone'          => $client->phone_no,
            'customer_email'          => $client->email,
            'quotation_id'            => $quote->quote_number, // or quote_number if you have one
            'quotation_amount'        => Formatter::amount_format($quote->grand_total), // replace with actual field
            'quotation_valid_until'   => $quote->expiry_date ? Formatter::bladeDate($quote->expiry_date) : null,
            'company_name'            => $vendor->name,
            'service_name'            => $joinedServiceNames,

            'preferred_date'          => Formatter::bladeDate($quote->preferred_date),
            'preferred_time'          => Formatter::formatTime($quote->preferred_time),
            'service_location'        => $serviceLocation,

            'vendor_website'          => $vendor->website ?? (env('APP_WEBSITE') ?? config('app.url', 'https://example.com')),
            'company_website'         => env('APP_WEBSITE') ?? config('app.url', 'https://example.com'),
        ];

        $emailTemplate = EmailTemplate::where('code', 'quotation_created')->first();

        if (!$emailTemplate) {
            Log::warning('Email template not found: quotation_created');
            return;
        }

        try {
            $rendered = $templateService->load($emailTemplate, $data)->render();

            // $mailService = new VendorMailService($vendor);
            $mailService = app(VendorMailService::class, ['vendor' => $vendor]);

            $emailData = [
                'from_email'  => $vendor->contact->email ?? 'noreply@example.com',
                'from_name'   => $vendor->name,
                'to_email'    => $client->email, // likely you want to send to the client, not vendor
                'to_name'     => $client->fullName,

                'subject'     => $rendered['subject'],
                'message'     => $rendered['content'],

                'cc'          => [],
                'bcc'         => [],
                'attachments' => [],
                'table_name'  => 'quotes',
                'table_id'    => $quote->id,
            ];

            // return $mailService->send($emailData);

            $emailToCustomer = $mailService->send($emailData);

            // dd($emailToCustomer);
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
                'table_name'  => 'quotes',
                'table_id'    => $quote->id,
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

    public function quotationConfirmationEmail(Quote $quote)
    {
        $templateService = app(EmailTemplateService::class);
        $client = $quote->client;

        $vendor = Vendor::find($quote->vendor_id);


        if (!$client || empty($client->email)) {
            Log::warning('Missing client or email for quote ID: ' . $quote->id);
            return;
        }


        $serviceNames = $quote->quoteServices->pluck('service_name')->toArray();
        $joinedServiceNames = implode(', ', $serviceNames);

        // header('Access-Control-Allow-Origin:*');


        // dd($joinedServiceNames);
        $address = $quote->quoteEnq->address;

        $serviceLocation = trim(
            ($address->add1 ?? '') . ', ' .
                ($address->city ?? '') . ', ' .
                ($address->state ?? '') . ', ' .
                ($address->zip ?? '') . ', ' .
                strtoupper($address->country_code ?? '')
        );


        // Prepare data for the template
        $data = [
            'quotation_id'          => $quote->quote_number ?? $quote->id,
            'customer_name'         => $client->fullName,
            'customer_phone'        => $client->phone_no,
            'customer_email'        => $client->email,
            'service_location'  => $serviceLocation,
            'quotation_amount'      => Formatter::amount_format($quote->grand_total),
            'quotation_valid_until' => $quote->expiry_date ? Formatter::viewBladeDate($quote->expiry_date) : 'N/A',
            'company_name'          => $vendor->name,
            'company_website'       => $vendor->website ?? env('APP_WEBSITE', config('app.url')),
            'preferred_date'    => Formatter::bladeDate($quote->preferred_date),
            'preferred_time'    => Formatter::formatToTime($quote->preferred_time),
            'service_name' => $joinedServiceNames,
        ];



        $emailTemplate = EmailTemplate::where('code', 'quotation_confirmation')->first();

        if (!$emailTemplate) {
            Log::warning('Email template not found for code: quotation_confirmation');
            return;
        }

        try {
            $rendered = $templateService->load($emailTemplate, $data)->render();


            $mailService = new VendorMailService($vendor);
            $emailData = [
                'from_email'  => $vendor->contact->email ?? 'noreply@example.com',
                'from_name'   => $vendor->name ?? config('app.name'),
                'to_email'    => $client->email,
                'to_name'     => $client->fullName,
                'subject'     => $rendered['subject'],
                'message'     => $rendered['content'],

                'cc'          => [],
                'bcc'         => [],
                'attachments' => [],
                'table_name'  => 'quotes',
                'table_id'    => $quote->id,
            ];

      
            // $emailToCustomer = $mailService->send($emailData);

            // dd($emailToCustomer);
            $emailDataVendor = [
                'from_email'  => 'noreply@email.com',
                'from_name'   => $vendor->name,
                'to_email'    => $vendor->contact->email ?? 'sales@example.com',
                'to_name'     => $vendor->name,
                'subject'     => $rendered['vendor_subject'],
                'message'     => $rendered['vendor_content'],

                'cc'          => [],
                'bcc'         => [],
                'attachments' => [],
                'table_name'  => 'quotes',
                'table_id'    => $quote->id,
            ];

            // dd($emailDataVendor);
            // $emailToVendor =  $mailService->send($emailDataVendor);

            // dd($emailToVendor);
            return [
                // 'emailToCustomer' => $emailToCustomer,
                // 'emailToVendor' => $emailToVendor,
            ];
        } catch (\Throwable $e) {
            dd($e->getMessage());
            Log::error('Failed to send quotation confirmation email: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response([
                'error' => 'Email sending failed',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
