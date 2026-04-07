<?php

namespace Systha\Core\Http\Controllers\Api\V1\ContactClient\Appointment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Systha\Core\Models\Appointment;
use Systha\Core\Services\StripeService;
use Systha\Core\Http\Resources\AppointmentResource;

/**
 * @group Contacts
 * @subgroup Appointments
 */
class AppointmentController extends Controller
{

    public function index(Request $request)
    {
        $contact = auth('contacts')->user();


        $query = Appointment::where('client_id', $contact->table_id)->with('address');

        // Filter by date (exact date or preferred date)
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('start_date', $request->date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->latest()->get();

        return  AppointmentResource::collection($appointments);
    }

    public function show(Request $request, $id)
    {


        $appointment = Appointment::find($id);
        $appointment->load('client', 'payment','payments','provider');
 
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
