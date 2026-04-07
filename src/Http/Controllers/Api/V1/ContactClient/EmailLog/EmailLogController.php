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


namespace Systha\Core\Http\Controllers\Api\V1\ContactClient\EmailLog;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Systha\Core\Models\EmailLog;
use Illuminate\Support\Facades\Auth;



// class EmailLogController extends Controller
// {
//     public function index(Request $request){
//         $contact = auth('contacts')->user();
//         try {
//             $logs = EmailLog::where('from',$contact->email)->orWhere('to', $contact->email)->latest()->get();
//             return response([
//                 "data"=>$logs
//             ]);
//         } catch (\Throwable $th) {
//             return response(["error"=>$th->getMessage()],422);
//         }
//     }
// }

/**
 * @group Contacts
 * @subgroup Messages
 */
class EmailLogController extends Controller
{
    public function index(Request $request){
        $client = Auth::guard('clients')->user();
        try {
            $query = EmailLog::query()
                ->where(function ($q) use ($client) {
                    $q->where('from', $client->email)
                        ->orWhere('to', $client->email);
                });

            if ($request->filled('status')) {
                $query->where('sent_status', $request->get('status'));
            }

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', '%' . $search . '%')
                        ->orWhere('from', 'like', '%' . $search . '%')
                        ->orWhere('to', 'like', '%' . $search . '%');
                });
            }

            $sortBy = $request->get('sort_by');
            $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
            $sortable = [
                'created_at' => 'created_at',
                'subject' => 'subject',
                'sent_status' => 'sent_status',
            ];

            if ($sortBy && array_key_exists($sortBy, $sortable)) {
                $query->orderBy($sortable[$sortBy], $sortOrder);
            } else {
                $query->latest();
            }

            $perPage = (int) $request->get('per_page', 10);
            if ($perPage < 1) {
                $perPage = 10;
            }
            if ($perPage > 100) {
                $perPage = 100;
            }

            $logs = $query->paginate($perPage);

            return response()->json([
                'data' => $logs->items(),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'from' => $logs->firstItem(),
                    'last_page' => $logs->lastPage(),
                    'path' => $logs->path(),
                    'per_page' => $logs->perPage(),
                    'to' => $logs->lastItem(),
                    'total' => $logs->total(),
                ],
            ]);
        } catch (\Throwable $th) {
            return response(["error"=>$th->getMessage()],422);
        }
    }
}
