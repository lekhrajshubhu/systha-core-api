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

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Setting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Systha\Core\DTO\AddressDto;
use Systha\Core\DTO\ClientDto;
use Systha\Core\Http\Controllers\Api\V1\Platform\PlatformBaseController;
use Systha\Core\Http\Requests\AddressStoreRequest;
use Systha\Core\Http\Requests\ClientStoreRequest;
use Systha\Core\Models\AddressModel;
use Systha\Core\Models\AttachmentModel;
use Systha\Core\Models\Client;
use Systha\Core\Models\ClientModel;


/**
 * @group Platform
 * @subgroup Messages
 */
class SettingController extends PlatformBaseController
{
    public function profileGeneral(ClientStoreRequest $request)
    {
        $validated = $request->validated();

        $clientDto = ClientDto::fromRequest($request);
        try {
            $result = DB::transaction(function () use ($clientDto, $validated) {

                if ($clientDto->email) {

                    $client = ClientModel::updateOrCreate(
                        ['email' => $clientDto->email],
                        $clientDto->toArray()
                    );
                } else {
                    $client = ClientModel::create($clientDto->toArray());
                }


                if (isset($validated['avatar'])) {
                    $attachment = AttachmentModel::storeUpload($validated['avatar'], 'clients');

                    $client->attachmentUsages()->create([
                        'attachment_id' => $attachment->id,
                        'meta' => [
                            'type' => 'avatar',
                            'is_primary' => true,
                        ],
                    ]);
                }


                return [
                    'client' => $client,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Client saved and assigned to company successfully.',
                'data' => [
                    'client' => $result['client'],
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save client and assign to company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function profileAddressList(Request $request)
    {
        $client = $this->user;

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Authenticated client not found.',
            ], 404);
        }

        $addresses = $client->addressList()
            ->select(['id', 'add1', 'add2', 'city', 'state', 'zip', 'address_type','is_default'])
            ->orderByRaw("(is_default = 1) DESC")
            ->where('is_deleted',0)
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'add1' => $address->add1,
                    'add2' => $address->add2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'zip' => $address->zip,
                    'is_default' => $address->is_default,
                    'label' => ucwords(str_replace(['-', '_'], ' ', $address->address_type)),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ], 200);
    }

    public function profileAddressDetail(Request $request, $addressId)
    {
        $address = AddressModel::find($addressId);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $address->id,
                'add1' => $address->add1,
                'add2' => $address->add2,
                'city' => $address->city,
                'state' => $address->state,
                'zip' => $address->zip,
                'is_default' => $address->is_default,
                'label' => ucwords(str_replace(['-', '_'], ' ', $address->address_type)),
            ],
        ], 200);
    }
    public function profileAddressUpdate(AddressStoreRequest $request)
    {

        $validated = $request->validated();

        $client = $this->user;
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Authenticated client not found.',
            ], 404);
        }

        $addressDto = AddressDto::fromRequest($request);
        $payload = array_merge(
            $addressDto->toArray(),
            [
                'table_name' => 'clients',
                'table_id' => $client->id,
                'is_default' => $addressDto->isDefault ?? false,
            ]
        );

        try {
            $result = DB::transaction(function () use ($addressDto, $payload, $client) {

                if (!empty($payload['is_default'])) {
                    AddressModel::where('table_name', 'clients')
                        ->where('table_id', $client->id)
                        ->where('id', '!=', $addressDto->id)
                        ->update(['is_default' => false]);
                }

                if ($addressDto->id) {
                    $address = AddressModel::find($addressDto->id);

                    if (!$address) {
                        throw new \RuntimeException('Address not found.');
                    }

                    $address->update($payload);
                } else {
                    $address = AddressModel::create($payload);
                }


                return [
                    'address' => $address,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Address saved successfully.',
                'data' => [
                    'address' => [
                        'id' => $result['address']->id,
                        'add1' => $result['address']->add1,
                        'add2' => $result['address']->add2,
                        'city' => $result['address']->city,
                        'state' => $result['address']->state,
                        'zip' => $result['address']->zip,
                        'is_default' => $result['address']->is_default,
                        'label' => ucwords(str_replace(['-', '_'], ' ', $result['address']->address_type)),
                    ],
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save client and assign to company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function profileAddressDelete(Request $request, $addressId)
    {
        $client = $this->user;
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Authenticated client not found.',
            ], 404);
        }

        $address = AddressModel::find($addressId);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        }

        try {
            $address->update(['is_deleted' => true,'deleted_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete address.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
