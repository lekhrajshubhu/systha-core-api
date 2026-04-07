<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Systha\Core\Http\Controllers\Api\V1\Platform\PlatformBaseController;
use Systha\Core\Models\Client;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

/**
 * @group Platform
 * @subgroup Auth
 * @property-read \Tymon\JWTAuth\JWTGuard $platformGuard
 * @property-read ?\Systha\Core\Models\Client $user
 * @property-read \Tymon\JWTAuth\Token|string|null $token
 * @property-read array $profileData
 */
class AuthProfileController extends PlatformBaseController
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

        $token = auth()->guard('platform')->login($client);

        return response()->json(['token' => $token]);
    }

    /**
     * @subgroup Profile
     */
    public function profile()
    {
        // Reject requests that do not include any platform auth token.
        if (! $this->token) {
            return response()->json(['message' => 'Token not provided.'], 401);
        }

        try {
            // Return the already-resolved authenticated platform user.
            if (! $this->user) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }

            return response()->json(['data' => $this->profileData]);
        } catch (TokenExpiredException $e) {
            try {
                // Refresh the expired token and re-resolve the authenticated user.
                ['token' => $token, 'user' => $user] = $this->refreshTokenAndUser();

                // If the token refresh succeeds but no user can be resolved, treat it as unauthorized.
                if (! $user) {
                    return response()->json(['message' => 'Unauthorized.'], 401);
                }

                // Return the authenticated user together with the new token for the client to store.
                return response()->json([
                    'data' => $this->profileData,
                    'token' => $token,
                ]);
            } catch (JWTException $e) {
                // Token refresh failed, so the client must authenticate again.
                return response()->json(['message' => 'Token cannot be refreshed.'], 401);
            }
        } catch (JWTException $e) {
            // The provided token is malformed or otherwise invalid.
            return response()->json(['message' => 'Invalid token.'], 401);
        }
    }

    /**
     * @subgroup Profile
     */
    public function updateProfile(Request $request)
    {
        $authClient = $this->user;
        $client = $request->filled('id')
            ? Client::find($request->get('id'))
            : $authClient;

        if (!$client) {
            return response()->json([
                'message' => 'Client not found.',
            ], 404);
        }

        $emailRules = ['required', 'email', 'max:255'];
        if (!$request->filled('id')) {
            $emailRules[] = 'unique:clients,email,' . $authClient->id;
        }

        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
            'fname' => ['nullable', 'string', 'max:255'],
            'lname' => ['nullable', 'string', 'max:255'],
            'email' => $request->filled('email') ? $emailRules : ['nullable'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ((int) $client->id !== (int) $authClient->id) {
            return response()->json([
                'message' => 'You are not allowed to update this profile.',
            ], 403);
        }

        $name = trim((string) $request->get('name', ''));
        $firstName = null;
        $lastName = null;

        if ($request->filled('fname') || $request->filled('lname')) {
            $firstName = trim((string) $request->get('fname', ''));
            $lastName = trim((string) $request->get('lname', ''));
        } elseif ($name !== '') {
            $parts = preg_split('/\s+/', $name, 2);
            $firstName = $parts[0] ?? null;
            $lastName = $parts[1] ?? null;
        }

        if (!is_null($firstName)) {
            $client->fname = $firstName;
        }
        if (!is_null($lastName)) {
            $client->lname = $lastName;
        }
        if ($request->filled('email')) {
            $client->email = $request->get('email');
        }
        if ($request->has('phone')) {
            $client->phone_no = $request->get('phone');
        }

        $client->save();

        if ($client->contact && (!is_null($firstName) || !is_null($lastName) || $request->filled('email') || $request->has('phone'))) {
            $client->contact->update([
                'fname' => is_null($firstName) ? $client->contact->fname : $firstName,
                'lname' => is_null($lastName) ? $client->contact->lname : $lastName,
                'email' => $request->filled('email') ? $request->get('email') : $client->contact->email,
                'phone_no' => $request->has('phone') ? $request->get('phone') : $client->contact->phone_no,
                'mobile_no' => $request->has('phone') ? $request->get('phone') : $client->contact->mobile_no,
            ]);
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $client->fresh(),
        ]);
    }

    /**
     * @subgroup Profile
     */
    public function updateProfileAddress(Request $request)
    {
        $authClient = $this->user;
        $client = $request->filled('id')
            ? Client::find($request->get('id'))
            : $authClient;

        if (!$client) {
            return response()->json([
                'message' => 'Client not found.',
            ], 404);
        }

        if ((int) $client->id !== (int) $authClient->id) {
            return response()->json([
                'message' => 'You are not allowed to update this profile.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'integer'],
            'add1' => ['required', 'string', 'max:255'],
            'add2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'zip' => ['required', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $client->address()->updateOrCreate(
            ['table_name' => 'clients', 'table_id' => $client->id, 'address_type' => 'primary'],
            [
                'add1' => $request->get('add1'),
                'add2' => $request->get('add2'),
                'city' => $request->get('city'),
                'state' => $request->get('state'),
                'zip' => $request->get('zip'),
            ]
        );

        return response()->json([
            'message' => 'Address updated successfully.',
            'data' => $client->fresh(),
        ]);
    }

    /**
     * @subgroup Profile
     */
    public function updateProfilePassword(Request $request)
    {
        $client = $this->user;

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!Hash::check($request->get('current_password'), $client->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors' => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $client->password = bcrypt($request->get('new_password'));
        $client->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    /**
     * @subgroup Auth
     */
    public function refresh()
    {
        $token = $this->platformGuard->refresh();

        return response()->json(['token' => $token]);
    }

    /**
     * @subgroup Auth
     */
    public function logout()
    {
        $this->platformGuard->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @subgroup Auth
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $client = Client::where('email', $request->email)->first();
        if (!$client) {
            return response()->json(['message' => 'Email does not exist.'], 404);
        }

        $newPassword = Str::random(8);
        $client->password = bcrypt($newPassword);
        $client->save();

        $fromEmail = 'system@systha.com';
        $fromName = 'Support';

        try {
            Mail::send('core::mail.password_reset', [
                'password' => $newPassword,
                'user' => $client,
                'logo' => 'noimage.png',
            ], function ($message) use ($client, $fromEmail, $fromName) {
                $message->from($fromEmail, $fromName)
                        ->to($client->email)
                        ->subject('Your Password Has Been Reset');
            });
        } catch (\Throwable $e) {
            Log::error('Password reset email failed', [
                'error' => $e->getMessage(),
                'email' => $client->email,
            ]);
            return response()->json([
                'message' => 'Failed to send email.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json(['message' => 'A new password has been sent to your email.']);
    }
}
