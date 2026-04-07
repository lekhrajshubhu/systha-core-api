<?php

namespace Systha\Core\Http\Controllers\Api\V1\Tenant\Subscription;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Systha\Core\Models\Vendor;
use Systha\Core\Lib\Subscription\StripeSub;
use Systha\Core\Models\PackageSubscription;
use Systha\Core\Http\Resources\SubscriptionResource;

/**
 * @group Tenant
 * @subgroup Payments
 */
class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $contact = Auth::guard('vendor_client')->user();

        $query = PackageSubscription::where('client_id', $contact->table_id);

        // Optional: Date filter
        if ($request->filled('date')) {
            // $query->whereDate('created_at', $request->date);
        } elseif ($request->filled('start_date')) {
            // $query->whereDate('start_date', $request->date);
        }

        // Optional: Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->get();

        return SubscriptionResource::collection($subscriptions);
    }

    public function show($id)
    {
        try {
            $subscription = PackageSubscription::with(['vendor', 'client', 'package', 'packageType'])->findOrFail($id);

            return new SubscriptionResource($subscription); // ✅ Use resource, not raw response

        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage()], 422);
        }
    }

    // public function cancel(Request $request, $id)
    // {
    //     try {
    //         $packageSubscription = PackageSubscription::find($id);
    //         return response(["dat" => $packageSubscription], 200);
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         return response(["data" => $th->getMessage()], 422);
    //     }
    // }

    public function cancel(Request $request, $packageSubscriptionId)
    {
        $packageSubscription = PackageSubscription::find($packageSubscriptionId);

        $cart = $packageSubscription->cart;
        $subscription_id = $cart->subscription;
        $vendor = Vendor::find($packageSubscription->vendor_id);
        $stripe = new StripeSub($vendor);
        $resp = $stripe->cancelSubscription($subscription_id);

        $packageSubscription->update([
            'is_cancelled' => 1,
            'is_active' => 0,
            'status' => 'cancelled',

        ]);

        return response()->json([
            "resp" => $resp,
            'data' => $packageSubscription,
            'message' => 'Subscription Cancelled Successfully.'
        ]);
    }
}
