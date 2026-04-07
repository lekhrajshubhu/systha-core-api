<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Appointment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Systha\Core\Models\Appointment;
use Systha\Core\Http\Resources\AppointmentListResource;
use Systha\Core\Http\Resources\AppointmentResource;

/**
 * @group Platform
 * @subgroup Appointments
 */
class AppointmentController extends Controller
{

    public function index(Request $request)
    {
        $client = auth('platform')->user();

        $query = Appointment::where('client_id', $client->id)->with('address');

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

        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortable = [
            'appointment_no' => 'appointment_no',
            'appointment_date' => 'start_date',
            'status' => 'status',
            'amount' => 'total_amount',
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

        $appointments = $query->paginate($perPage);

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

    public function todaysAppointments(Request $request)
    {
        $client = auth('platform')->user();

        $appointments = Appointment::where('client_id', $client->id)
            ->with('address', 'vendor')
            ->whereDate('start_date', Carbon::today())
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'data' => $appointments->map(fn ($appointment) => (new AppointmentResource($appointment))->toTodayArray()),
        ]);
    }
    public function appointmentList(Request $request)
    {
        $client = auth('platform')->user();

        $query = Appointment::where('client_id', $client->id)->with(['address', 'vendor']);

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

        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortable = [
            'appointment_no' => 'appointment_no',
            'appointment_date' => 'start_date',
            'status' => 'status',
            'amount' => 'total_amount',
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

        $appointments = $query->paginate($perPage);

        return response()->json([
            'data' => AppointmentListResource::collection($appointments->items()),
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
        $appointment = Appointment::with('client', 'payment', 'payments', 'provider', 'vendor','quotation.sections')->find($id);
        return response(["data" => new AppointmentResource($appointment)], 200);
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
