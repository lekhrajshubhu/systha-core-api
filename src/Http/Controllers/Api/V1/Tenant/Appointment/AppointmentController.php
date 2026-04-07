<?php

namespace Systha\Core\Http\Controllers\Api\V1\Tenant\Appointment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Systha\Core\Models\Appointment;
use Systha\Core\Http\Resources\AppointmentResource;
use Systha\Core\Http\Controllers\Api\V1\Tenant\BaseController;

/**
 * @group Tenant
 * @subgroup Appointments
 */
class AppointmentController extends BaseController
{

    public function index(Request $request)
    {
        $client = $this->client;

        $query = Appointment::where(['client_id' => $client->id,'vendor_id' => $this->vendor->id])->with('address');

        // Filter by date (exact date or preferred date)
        if ($request->filled('date')) {
            // $date = \Carbon\Carbon::parse($request->date)->toDateString();
            $query->whereDate('start_date', $request->date);
        } elseif ($request->filled('start_date')) {
            // $startDate = \Carbon\Carbon::parse($request->start_date)->toDateString();
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
                $q->where('appointment_no', 'like', '%' . $search . '%')
                    //   ->orWhere('provider_name', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        $perPage = (int) $request->get('per_page', 15);
        if ($perPage < 1) {
            $perPage = 15;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $appointments = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => AppointmentResource::collection($appointments->items()),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'from' => $appointments->firstItem(),
                'last_page' => $appointments->lastPage(),
                'path' => $appointments->path(),
                'per_page' => $appointments->perPage(),
                'to' => $appointments->lastItem(),
                'total' => $appointments->total(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {


        $appointment = Appointment::find($id);
        $appointment->load('client', 'payment', 'payments', 'provider');

        // $stripeService = app(StripeService::class,[
        //     "vendor" => $appointment->vendor
        // ]);
        // $info = $stripeService->getCardInfo("pm_1RoOKTFCkDOH9dhP1KeKMrXG");

        // dd($info);
        return response(["data" => $appointment], 200);
    }


    public function profile()
    {
        $user = Auth::guard('contact')->user();
        return response()->json($user);
    }

    public function logout()
    {
        Auth::guard('contact')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
