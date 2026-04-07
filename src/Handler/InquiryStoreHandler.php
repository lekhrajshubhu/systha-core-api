<?php

namespace Systha\Core\Handler;

use Illuminate\Support\Facades\DB;
use Systha\Core\DTO\InquiryStoreDto;
use Systha\Core\Models\ClientModel;
use Systha\Core\Models\InquiryModel;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\VendorClient;
use Systha\Core\Services\CustomMailService;
use Systha\Core\Services\EmailLogoService;

class InquiryStoreHandler
{
    public function handle(InquiryStoreDto $data): InquiryModel
    {
        return DB::transaction(function () use ($data) {
            $vendorId = Vendor::query()
                ->where('vendor_code', $data->vendorCode)
                ->value('id');

            if (!$vendorId) {
                abort(422, 'Invalid vendor code.');
            }

            // Create or update client
            $client = ClientModel::firstOrCreate(
                [
                    'email' => $data->email,
                ],
                [
                    'phone_no' => $data->phone,
                    'fname' => $data->firstName,
                    'lname' => $data->lastName,
                ]
            );

            if (! $client->wasRecentlyCreated) {
                $client->update([
                    'fname' => $data->firstName,
                    'lname' => $data->lastName,
                ]);
            }

            VendorClient::firstOrCreate([
                'vendor_id' => $vendorId,
                'email' => $data->email,
                'client_id' => $client->id,
            ], [
                'is_active' => 1,
                'vendor_code' => $data->vendorCode,
            ]);
            // Create inquiry
            $inquiry = InquiryModel::create([
                'client_id' => $client->id,
                'vendor_id' => $vendorId,
                'desc' => $data->note,
                'inquiry_info' => $data->selectedItems,
                'reviewable_history' => $data->selectedItems,
                'status' => 'new',
                'state' => 'publish'
            ]);

            // Create service address
            $inquiry->serviceAddress()->create([
                'add1' => $data->addressLine1,
                'add2' => $data->addressLine2,
                'city' => $data->city,
                'state' => $data->state,
                'zip' => $data->zip,
                'address_type' => 'inquiries',
            ]);

            return $inquiry->load([
                'client',
                'serviceAddress',
            ]);
        });
    }

    public function sendInquiryEmails($inquiry, $vendor)
    {
        $logo = app(EmailLogoService::class)->vendorLogoDataUri($vendor);
        $mailService = app(CustomMailService::class);

        // Email to vendor
        $vendorEmailData = [
            'from_email' => $inquiry->client->email,
            'from_name' => "lekhraj@shubhu.com",
            // 'to_email' => optional($vendor->contact)->email ?? $vendor->email,
            'to_email' => "lekhraj@systha.com",
            'to_name' => $vendor->name,
            'subject' => 'New Schedule Service',
            'message' => "asfas",
            'cc' => [],
            'bcc' => [],
            'attachments' => [],
            'table_name' => 'inquiries',
            'table_id' => $inquiry->id,
        ];
        $vendorResult = $mailService->send($vendorEmailData, $vendor);

        // Email to client
        $clientEmailData = [
            'from_email' => optional($vendor->contact)->email ?? $vendor->email,
            'from_name' => $vendor->name,
            'to_email' => "lekhraj@systha.com",
            // 'to_name' => $inquiry->client->fullName ?? $inquiry->client->fname,
            'to_email' => "lekhraj@systha.com",
            'subject' => 'Your Service Schedule',
            'message' =>"adfas",
            'cc' => [],
            'bcc' => [],
            'attachments' => [],
            'table_name' => 'inquiries',
            'table_id' => $inquiry->id,
        ];
        // $clientResult = $mailService->send($clientEmailData, $vendor);

        return [
            'vendor_email' => $vendorResult,
            'client_email' => "client email sending skipped for testing",
             // $clientResult
        ];
    }
}
