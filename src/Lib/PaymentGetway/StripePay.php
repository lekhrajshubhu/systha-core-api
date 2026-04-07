<?php
namespace Systha\Core\Lib\PaymentGetway;

use Exception;
use Stripe;
class StripePay
{
    public function chargeCreditCard($data)
    {
        // dd($data);
        try {
            if(array_key_exists("vendor", $data)){
                if(isset($data['vendor'])){
                    $vendor = $data['vendor'];
                    //dd($vendor->defaultPaymentCredential);
                    if(!is_null(getVendorPaymentCredetial($data['vendor']->id, 'stripe'))){
                        $apiKey = $vendor->defaultPaymentCredential->val2;

                    }
                    else{
                        $apiKey = default_company('stripe_secret_key');
                    }
                }else{
                    $apiKey = default_company('stripe_secret_key');
                }
            }else {
                $apiKey = default_company('stripe_secret_key');
            }
            // dd($apiKey);
            Stripe\Stripe::setApiKey($apiKey);
            $transaction = Stripe\Charge::create([
                    "amount" => $data['amount'] * 100,
                    "currency" => "usd",
                    "source" => $data['stripeToken'],
                    "description" => "Order Payment."
            ]);
            return $transaction;
            // $transaction_id = $transaction_id->getId();
        } catch (Exception $e) {
            echo "Exception when making Payment";
            var_dump($e->getFile(), $e->getLine(), $e->getMessage());
        }

    }

}
