<?php

namespace Systha\Core\Lib\Services\Payment;

use Systha\Core\Lib\PaymentGetway\StripePay;
use Systha\crates\Models\Order;

class Stripe implements PayableInterface{

    public function pay($data){
        $stripe = new StripePay;
        $payment = $stripe->chargeCreditCard($data);
        if(gettype($payment) == "array" && isset($payment['error'])){
            if($payment['code'] === "E00007"){
                $env = app()->environment();
                $development = $env === "local" ? "sandbox" : 'production';
                throw new \Exception("Use $development env for $env environment");
            }
            throw new \Exception($payment['error'], 500);
        }
        return $payment;
    }

    public static function refund(Order $order)
    {
        $stripe = new StripePay;
        $payment = $stripe->refundOrder($order);
        if(gettype($payment) == "array" && isset($payment['error'])){
            if($payment['code'] === "E00007"){
                $env = app()->environment();
                $development = $env === "local" ? "sandbox" : 'production';
                throw new \Exception("Use $development env for $env environment");
            }
            throw new \Exception($payment['error'], 500);
        }
        return $payment;
    }
}
