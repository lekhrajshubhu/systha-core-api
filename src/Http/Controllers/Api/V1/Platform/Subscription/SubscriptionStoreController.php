<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Subscription;

use App\Http\Controllers\Controller;
use Systha\Core\DTO\SubscriptionStoreDto;
use Systha\Core\Handler\SubscriptionStoreHandler;
use Systha\Core\Http\Requests\RecurringRequest;

/**
 * @group Platform
 * @subgroup Payments
 */
class SubscriptionStoreController extends Controller
{
    public function store(RecurringRequest $request, SubscriptionStoreHandler $handler)
    {

        header('Access-Control-Allow-Origin: *');
    try {

        $dto = SubscriptionStoreDto::fromArray($request->validated());

        $result = $handler->handle($dto);

        dd($result);
        return response()->json([
            'message' => 'Subscription created successfully',
            // 'data' => $subscription,
        ], 201);
    } catch (\Throwable $th) {
        //throw $th;
        dd($th->getMessage());
        return response()->json([
            'message' => 'Failed to create subscription',
            'error' => $th->getMessage(),
        ], 500);
    }
    }
}