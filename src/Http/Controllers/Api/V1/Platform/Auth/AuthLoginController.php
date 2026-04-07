<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Systha\Core\Models\Client;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * @group Platform
 * @subgroup Auth
 */
class AuthLoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $client = Client::where('email', $request->get('email'))
            ->where('is_deleted', 0)
            ->first();

        if (! $client) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => [
                    'email' => ['The provided email does not exist.'],
                ],
            ], 401);
        }

        if (! Hash::check($request->get('password'), $client->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => [
                    'password' => ['The provided password is incorrect.'],
                ],
            ], 401);
        }

        $token = Auth::guard('platform')->login($client);

        return response()->json(['token' => $token]);
    }

   
}
