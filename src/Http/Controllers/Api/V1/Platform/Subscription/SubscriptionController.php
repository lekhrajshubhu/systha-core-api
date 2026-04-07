<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Subscription;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Systha\Core\Models\Vendor;
use Systha\Core\Lib\Subscription\StripeSub;
use Systha\Core\Models\PackageSubscription;
use Systha\Core\Http\Resources\SubscriptionResource;

/**
 * @group Platform
 * @subgroup Payments
 */
class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $client = auth('platform')->user();

        $query = PackageSubscription::where('client_id', $client->id)
            ->with(['package.packageServices', 'packageType', 'vendor']);

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('start_date', $request->start_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->is_paid);
        }

        // Search keyword
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('subs_no', 'like', '%' . $search . '%')
                  ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortable = [
            'subscription_no' => 'subs_no',
            'subscription_date' => 'created_at',
            'status' => 'status',
            'amount' => 'price',
        ];

        if ($sortBy && array_key_exists($sortBy, $sortable)) {
            $query->orderBy($sortable[$sortBy], $sortOrder);
        } else {
            $query->latest();
        }

        $perPage = (int) $request->get('per_page', 15);
        if ($perPage < 1) {
            $perPage = 15;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $subscriptions = $query->paginate($perPage);

        return response()->json([
            'data' => SubscriptionResource::collection($subscriptions->items()),
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'from' => $subscriptions->firstItem(),
                'last_page' => $subscriptions->lastPage(),
                'path' => $subscriptions->path(),
                'per_page' => $subscriptions->perPage(),
                'to' => $subscriptions->lastItem(),
                'total' => $subscriptions->total(),
            ],
        ]);
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
