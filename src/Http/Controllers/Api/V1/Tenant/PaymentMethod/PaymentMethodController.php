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


namespace Systha\Core\Http\Controllers\Api\V1\Tenant\PaymentMethod;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Systha\Core\Http\Resources\PaymentMethodResource;
use Systha\Core\Models\StripeCustomer;

/**
 * @group Tenant
 * @subgroup Payments
 */
class PaymentMethodController extends Controller
{
    public function index(Request $request){
        $auth = auth('vendor_client')->user();

        $client = $auth->client;
        try {
            $stripeCustomer = StripeCustomer::where('client_id',$client->id)
            ->with('paymentMethods')->with('defaultPaymentMethod')
            ->first();
            return response([
                "data" => new PaymentMethodResource($stripeCustomer),
            ]);
        } catch (\Throwable $th) {
            return response(["error"=>$th->getMessage()],422);
        }
    }
}
