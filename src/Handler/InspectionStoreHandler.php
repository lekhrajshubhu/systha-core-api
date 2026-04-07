<?php

namespace Systha\Core\Handler;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Systha\Core\DTO\InspectionStoreDto;
use Systha\Core\Models\AttachmentModel;
use Systha\Core\Models\ClientModel;
use Systha\Core\Models\InquiryModel;
use Systha\Core\Models\Vendor;

class InspectionStoreHandler
{
    public function handle(InspectionStoreDto $data, ?int $authenticatedClientId = null): InquiryModel
    {
        return DB::transaction(function () use ($data, $authenticatedClientId) {
            $vendorId = Vendor::query()
                ->where('vendor_code', $data->vendorCode)
                ->value('id');

            if (! $vendorId) {
                abort(422, 'Invalid vendor code.');
            }

            $client = $this->resolveClient($data, $authenticatedClientId);

            $inspection = InquiryModel::create(
                $data->toInspectionArray(clientId: $client->id, vendorId: (int) $vendorId)
            );

            $inspection->serviceAddress()->create(
                $data->toServiceAddressArray()
            );

            do {
                $attachmentDirectory = 'inspections/' . now()->format('Y-His') . '-' . Str::upper(Str::random(4));
            } while (Storage::disk('media')->exists($attachmentDirectory));

            foreach ($data->photos as $index => $photo) {
                $attachment = AttachmentModel::storeUpload(
                    $photo,
                    $attachmentDirectory
                );

                $inspection->attachmentUsages()->create([
                    'attachment_id' => $attachment->id,
                    'meta' => [
                        'description' => (string) ($data->photoDescriptions[$index] ?? ''),
                    ],
                    'order' => $index,
                ]);
            }

            return $inspection->load(['client', 'vendor', 'serviceAddress', 'attachments.attachment']);
        });
    }

    protected function resolveClient(InspectionStoreDto $data, ?int $authenticatedClientId = null): ClientModel
    {
        if ($authenticatedClientId) {
            $client = ClientModel::query()->findOrFail($authenticatedClientId);
            $client->update($data->toClientUpdateArray());

            return $client;
        }

        $client = ClientModel::firstOrCreate(
            $data->toClientLookupArray(),
            $data->toClientCreateArray(),
        );

        if (! $client->wasRecentlyCreated) {
            $client->update($data->toClientUpdateArray());
        }

        return $client;
    }
}
