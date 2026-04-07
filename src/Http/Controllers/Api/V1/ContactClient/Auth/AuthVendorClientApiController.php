<?php

namespace Systha\Core\Http\Controllers\Api\V1\ContactClient\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * @group Contacts
 * @subgroup Auth
 */
class AuthVendorClientApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (! $token = Auth::guard('vendor_client')->attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = Auth::guard('vendor_client')->user();

            return response()->json([
                'token' => $token, 
                "user" => $user->client,
                "vendor_code" => $user->vendor_code,
            ], 200);
            //code...
        } catch (\Throwable $th) {
            return response([
                "error" => $th->getMessage()
            ], 422);
        }
    }

    /**
     * @subgroup Profile
     */
    public function profile()
    {
        $client = Auth::guard('vendor_client')->user();

        return response()->json(['user' => $client]);
    }

    /**
     * @subgroup Auth
     */
    public function refresh()
    {
        $token = Auth::guard('vendor_client')->refresh();

        return response()->json(['token' => $token]);
    }

    /**
     * @subgroup Auth
     */
    public function logout()
    {
        Auth::guard('vendor_client')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
