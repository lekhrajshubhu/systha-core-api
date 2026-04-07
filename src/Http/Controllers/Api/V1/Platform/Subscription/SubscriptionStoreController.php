<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Subscription;

use App\Http\Controllers\Controller;
use Systha\Core\DTO\SubscriptionStoreData;
use Systha\Core\Handler\SubscriptionStoreHandler;
use Systha\Core\Http\Requests\RecurringRequest;

/**
 * @group Platform
 * @subgroup Payments
 */
class SubscriptionStoreController extends Controller
{
    public function store(RecurringRequest $request,  SubscriptionStoreHandler $handler)
    {
        // $client = auth('platform')->user();

        // header('Access-Control-Allow-Origin: *');

        $validated = $request->validated();

        $dto = SubscriptionStoreData::fromArray($validated);

        $subscription = $handler->handle($dto);

        return response([
            'message' => 'Subscription created successfully',
            'data' => $subscription
        ], 201);
        // dd($validated);
    }
}
