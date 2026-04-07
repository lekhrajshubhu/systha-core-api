<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Inquiry;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Systha\Core\DTO\InquiryStoreData;
use Systha\Core\Handler\InquiryStoreHandler;
use Systha\Core\Http\Requests\InquiryStoreRequest;
use Systha\Core\Models\Vendor;

class InquiryStoreController extends Controller
{
    public function store(
        InquiryStoreRequest $request,
        InquiryStoreHandler $handler
    ): JsonResponse {

        $dto = InquiryStoreData::fromArray($request->validated());

        $inquiry = $handler->handle($dto);

        $inquiry->loadMissing(['client']);

        $vendor = Vendor::with('contact')->find($inquiry->vendor_id);

        $emailStatus = 'success';
        $emailError = null;
        $emailResults = null;

        if ($vendor && $inquiry->client) {
            try {
                $emailResults = $handler->sendInquiryEmails($inquiry, $vendor);
                if (
                    empty($emailResults['vendor_email']['success']) ||
                    empty($emailResults['client_email']['success'])
                ) {
                    $emailStatus = 'error';
                    $emailError = [
                        'vendor' => $emailResults['vendor_email']['error'] ?? null,
                        'client' => $emailResults['client_email']['error'] ?? null,
                    ];
                }
            } catch (\Throwable $th) {
                Log::error('Inquiry email send failed', [
                    'inquiry_id' => $inquiry->id,
                    'vendor_id' => $vendor->id ?? null,
                    'error' => $th->getMessage(),
                ]);
                $emailStatus = 'error';
                $emailError = $th->getMessage();
            }
        }

        return response()->json([
            'message' => $emailStatus === 'success'
                ? 'Inquiry created and email sent successfully.'
                : 'Inquiry created, but failed to send email.',
            'data' => $inquiry,
            'email_status' => $emailStatus,
            'email_error' => $emailError,
        ], 201);
    }
}
