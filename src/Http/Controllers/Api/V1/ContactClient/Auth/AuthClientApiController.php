<?php

namespace Systha\Core\Http\Controllers\Api\V1\ContactClient\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * @group Contacts
 * @subgroup Auth
 */
class AuthClientApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('client')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(['token' => $token]);
    }

    /**
     * @subgroup Profile
     */
    public function profile()
    {
        $client = Auth::guard('client')->user();

        return response()->json(['user' => $client]);
    }

    /**
     * @subgroup Auth
     */
    public function refresh()
    {
        $token = Auth::guard('client')->refresh();

        return response()->json(['token' => $token]);
    }

    /**
     * @subgroup Auth
     */
    public function logout()
    {
        Auth::guard('client')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
