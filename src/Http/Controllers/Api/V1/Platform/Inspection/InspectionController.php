<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Inspection;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Systha\Core\DTO\InspectionStoreData;
use Systha\Core\Handler\InspectionStoreHandler;
use Systha\Core\Http\Controllers\Api\V1\Platform\PlatformBaseController;
use Systha\Core\Http\Requests\InspectionStoreRequest;
use Systha\Core\Http\Resources\InspectionListResource;
use Systha\Core\Http\Resources\InspectionResource;
use Systha\Core\Models\InquiryModel;

class InspectionController extends PlatformBaseController
{
    public function store(
        InspectionStoreRequest $request,
        InspectionStoreHandler $handler
    ): JsonResponse
    {
        $dto = InspectionStoreData::fromArray(
            $request->validated(),
            $request->file('photos', [])
        );

        // header('Access-Control-Allow-Origin: *');

        // dd($dto);
        $inspection = $handler->handle($dto, $this->user?->id);

        return response()->json([
            'message' => 'Inspection created successfully.',
            'data' => new InspectionResource($inspection),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $inspections = InquiryModel::query()
            ->where('client_id', $this->user?->id)
            ->where('state', 'publish')
            ->where('inquiry_info->request_type', 'inspection')
            ->with(['vendor', 'files'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => InspectionListResource::collection($inspections->items()),
            'meta' => [
                'current_page' => $inspections->currentPage(),
                'from' => $inspections->firstItem(),
                'last_page' => $inspections->lastPage(),
                'path' => $inspections->path(),
                'per_page' => $inspections->perPage(),
                'to' => $inspections->lastItem(),
                'total' => $inspections->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $inspection = InquiryModel::query()
            ->where('id', $id)
            ->where('client_id', $this->user?->id)
            ->where('state', 'publish')
            ->where('inquiry_info->request_type', 'inspection')
            ->with(['client', 'vendor', 'serviceAddress', 'files'])
            ->firstOrFail();

        return response()->json([
            'data' => new InspectionResource($inspection),
        ]);
    }
}
