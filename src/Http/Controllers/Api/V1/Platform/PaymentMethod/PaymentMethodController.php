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


namespace Systha\Core\Http\Controllers\Api\V1\Platform\PaymentMethod;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Systha\Core\Models\StripeCustomer;
use Systha\Core\Http\Resources\PaymentMethodResource;

/**
 * @group Platform
 * @subgroup Payments
 */
class PaymentMethodController extends Controller
{
    public function index(Request $request){
        $client = auth('platform')->user();
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
