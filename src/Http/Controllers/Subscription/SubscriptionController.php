<?php
namespace Systha\Core\Http\Controllers\Subscription;

use Illuminate\Http\Request;
use Systha\Core\Lib\Subscription\StripeSub;
use Systha\Core\Models\PackageSubscription;
use Systha\Core\Http\Controllers\BaseController;
use Systha\Core\Http\Controllers\Dashboard\DashboardController;
use Systha\Core\Services\MessageService;

class SubscriptionController extends BaseController
{
    public function cancel(Request $request, $subscription_id)
    {
        try {
            //code...
            $subscription = PackageSubscription::find($subscription_id);
            $cart = $subscription->cart;
            $subscription_id = $cart->subscription;
            // dd($subscription->vendor);
            $stripe = new StripeSub($subscription->vendor);
            // dd($subscription_id);
            $stripe->cancelSubscription($subscription_id);

            $subscription->update([
                'is_cancelled' => 1,
                "status" => "cancelled"
            ]);

            $info = $stripe->retrieveSubscription($subscription->cart->subscription);
            return view($this->viewPath.'::frontend.dashboard.subscription.subscription-detail', compact('subscription', 'info'));
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'error' => $th->getMessage(),
            ], 422);
        }
    }
}

