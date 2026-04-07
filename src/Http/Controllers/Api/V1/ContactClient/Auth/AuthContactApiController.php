<?php

namespace Systha\Core\Http\Controllers\Api\V1\ContactClient\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * @group Contacts
 * @subgroup Auth
 */
class AuthContactApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('contacts')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(['token' => $token]);
    }

    /**
     * @subgroup Profile
     */
    public function profile()
    {
        $user = Auth::guard('contacts')->user();
        $client = $user->profile();

        $stripeCustomer = $client->stripeProfile;
        $vendor = $client->vendor;

        $data = [
            'id' => $client->id,
            'fname' => $client->fname,
            'lname' => $client->lname,
            'name' => $client->fullName,
            'email' => $client->email,
            'phone_no' => $client->phone_no,
            'address' => $client->address,
            'stripe_customer' => [
                'stripe_customer_id' => $stripeCustomer?->stripe_customer_id,
                'payment_method_id' => $stripeCustomer?->default_payment_method_id,
            ],
            'vendor' => [
                'vendor_code' => $vendor->vendor_code,
                'name' => $vendor->name,
                'id' => $vendor->id,
                'logo' => $vendor->logo,
                'stripe_pub_key' => optional($vendor->paymentCredential)->val1,
            ],
        ];

        return response()->json(['data' => $data]);
    }


    /**
     * @subgroup Auth
     */
    public function refresh()
    {
        $token = Auth::guard('contacts')->refresh();

        return response()->json(['token' => $token]);
    }

    /**
     * @subgroup Auth
     */
    public function logout()
    {
        Auth::guard('contacts')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
