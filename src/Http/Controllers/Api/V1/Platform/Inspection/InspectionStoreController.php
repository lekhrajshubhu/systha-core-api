<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Inspection;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Systha\Core\DTO\InspectionStoreData;
use Systha\Core\Handler\InspectionStoreHandler;
use Systha\Core\Http\Controllers\Api\V1\Platform\PlatformBaseController;
use Systha\Core\Http\Requests\InspectionStoreRequest;
use Systha\Core\Http\Resources\InspectionResource;
use Systha\Core\Models\Vendor;

class InspectionStoreController extends PlatformBaseController
{
    public function store(
        InspectionStoreRequest $request,
        InspectionStoreHandler $handler
    ): JsonResponse {
        $dto = InspectionStoreData::fromArray(
            $request->validated(),
            $request->file('photos', [])
        );

        // header('Access-Control-Allow-Origin: *');

        $inquiry = $handler->handle($dto, $this->user?->id);

        // return response()->json([
        //     'message' => 'Inspection created successfully.',
        //     'data' => new InspectionResource($inspection),
        // ], 201);
        $inquiry->loadMissing(['client']);

        $vendor = Vendor::find($inquiry->vendor_id);


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
