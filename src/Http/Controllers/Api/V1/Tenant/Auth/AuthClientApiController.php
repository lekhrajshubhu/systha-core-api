<?php

namespace Systha\Core\Http\Controllers\Api\V1\Tenant\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * @group Tenant
 * @subgroup Auth
 */
class AuthClientApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('vendor_client')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(['token' => $token]);
    }

    /**
     * @subgroup Profile
     */
    public function profile()
    {
        $client = Auth::guard('vendor_client')->user();

        return response()->json(['data' => $client]);
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
