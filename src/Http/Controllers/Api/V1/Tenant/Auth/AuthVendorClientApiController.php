<?php

namespace Systha\Core\Http\Controllers\Api\V1\Tenant\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Systha\Core\Http\Controllers\Api\V1\Tenant\BaseController;
use Systha\Core\Models\Client;
use Systha\Core\Models\VendorClient;

/**
 * @group Tenant
 * @subgroup Auth
 */
class AuthVendorClientApiController extends BaseController
{
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'vendor_code' => 'required',
        ]);

        $credentials = $request->only('email', 'password', 'vendor_code');

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
        $auth = Auth::guard('vendor_client')->user();
        $client = $auth->client;
        return response()->json(['data' => $client]);
    }

    /**
     * @subgroup Profile
     */
    public function updateProfile(Request $request)
    {
        $auth = Auth::guard('vendor_client')->user();
        $client = Client::find($request->get('id', $auth->client->id));

        if (!$client) {
            return response()->json([
                'message' => 'Client not found.',
            ], 404);
        }

        $emailRules = ['required', 'email', 'max:255'];
        if (!$request->filled('id')) {
            $emailRules[] = 'unique:clients,email,' . $client->id;
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

        if ((int) $client->id !== (int) $client->id) {
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
        $contact = Auth::guard('vendor_client')->user();
        $client = $contact?->client;

        if (!$client) {
            return response()->json([
                'message' => 'Client not found.',
            ], 404);
        }

        $request->validate([
            'id' => ['nullable', 'integer'],
            'add1' => ['required', 'string', 'max:255'],
            'add2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'zip' => ['required', 'string', 'max:20'],
        ]);

        if ($request->filled('id') && (int) $request->get('id') !== (int) $client->id) {
            return response()->json([
                'message' => 'You are not allowed to update this profile.',
            ], 403);
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
        $auth = Auth::guard('vendor_client')->user();
        $client = $auth->client;


        if (!$client) {
            return response()->json([
                'message' => 'Client not found.',
            ], 404);
        }
        $vendorClient = VendorClient::where(['client_id' => $client->id, 'vendor_code' => $this->vendor->vendor_code])->first();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if (!Hash::check($request->get('current_password'), $vendorClient->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors' => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $vendorClient->password = bcrypt($request->get('new_password'));
        $vendorClient->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
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

    /**
     * @subgroup Auth
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $client = VendorClient::where('email', $request->email)->first();
        if (!$client) {
            return response()->json(['message' => 'Email does not exist.'], 404);
        }

        $newPassword = Str::random(8);
        $client->password = bcrypt($newPassword);
        $client->save();

        $fromEmail = 'system@systha.com';
        $fromName = 'Support';


        Mail::send('core::mail.password_reset', [
            'password' => $newPassword,
            'user' => $client,
            'logo' => 'noimage.png',
        ], function ($message) use ($client, $fromEmail, $fromName) {
            if ($fromEmail) {
                $message->from($fromEmail, $fromName);
            }
            $message->to($client->email)
                ->subject('Your Password Has Been Reset');
        });

        return response()->json(['message' => 'A new password has been sent to your email.']);
    }
}
