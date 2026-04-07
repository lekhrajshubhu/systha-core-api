<?php

namespace Systha\Core\Services\Payment;

// use Systha\Salon\Lib\PaymentGetaway\PaypalPay;

// class Paypal implements PayableInterface{

//     public function pay($data){

//         $payment = $this->makeCardPayment($data);
//         if(gettype($payment) == "array" && isset($payment['error'])){
//             if($payment['code'] === "E00007"){  
//                 $env = app()->environment();
//                 $development = $env === "local" ? "sandbox" : 'production';             
//                 throw new \Exception("Use $development env for $env environment");
//             }
//             throw new \Exception($payment['error'], 500);                
//         }
//         return $payment;
//     }

//     public function makeCardPayment($data)
//     {
//         // dd($data);
//         $c_type = '';
//         if ($data['card'][0] == 3 || $data['card'][0] == "3") {
//             $c_type = 'amex';
//         }
//         if ($data['card'][0] == 4 || $data['card'][0] == "4") {
//             $c_type = 'visa';
//         }
//         if ($data['card'][0] == 5 || $data['card'][0] == "5") {
//             $c_type = 'mastercard';
//         }
//         if ($data['card'][0] == 6 || $data['card'][0] == "6") {
//             $c_type = 'discover';
//         }
//         $data = [
//             'card' => implode('', explode(' ', $data['card'])),
//             'type' => $c_type,
//             'expm' => $data['expm'],
//             'expy' => $data['expy'],
//             'code' => $data['code'],
//             'first_name' => $data['name_per_card'],
//             'last_name' => $data['name_per_card'],
//             'amount' => $data['amount'],
//             'des' => 'Order Payment',
//             'vendor' => $data['vendor']
//         ];
//         $resp = $this->paypalPayment($data);   
//         return $resp;
//     }

//     public function paypalPayment($data)
//     {
    
//         $pay = new PaypalPay();
//         if(array_key_exists('vendor', $data)){
//             if(!is_null(getVendorPaymentCredetial($data['vendor']->id, 'authourize.net'))){
//                 $data['api_key'] = getVendorPaymentCredetial($data['vendor']->id, 'paypal')->val1;
//             }
//             else{
//                 $data['api_key'] = default_company('paypal_api_key');
//             }
//         }
//         else{
//             $data['api_key'] = default_company('paypal_api_key');
//         }
//         if ($data['api_key']) :
//             $url = $pay->host . '/v1/oauth2/token';
//             $postArgs = 'grant_type=client_credentials';
//             $token = $pay->get_access_token($url, $postArgs);

//             $url = $pay->host . '/v1/payments/payment';
//             $payment = array(
//                 'intent' => 'sale',
//                 'payer' => array(
//                     'payment_method' => 'credit_card',
//                     'funding_instruments' => array(array(
//                         'credit_card' => array(
//                             'number' => $data['card'],
//                             'type' => $data['type'],
//                             'expire_month' => $data['expm'],
//                             'expire_year' => $data['expy'],
//                             'cvv2' => $data['code'],
//                             // 'first_name' => $data['first_name'],
//                             // 'last_name' => $data['last_name']
//                         )
//                     ))
//                 ),
//                 'transactions' => array(array(
//                     'amount' => array(
//                         'total' => $data['amount'],
//                         'currency' => 'USD'
//                     ),
//                     'description' => $data['des']
//                 ))
//             );
//             $json = json_encode($payment);

//             $json_resp = $pay->make_post_call($url, $json, $token);
//             $data = array();
//             //print_r($json_resp);
//             // dd($json_resp);

//             if (is_array($json_resp)) :

//                 if (array_key_exists("id", $json_resp)) :
//                     $data["status"] = 1;
//                     $data["data"] = array(
//                         "id" => $json_resp["id"],
//                         "credit_card" => $json_resp["payer"]["funding_instruments"][0]["credit_card"],
//                         "transcations" => $json_resp["transactions"][0]["related_resources"][0]["sale"]["id"],
//                         "amount" => $json_resp["transactions"][0]["related_resources"][0]["sale"]["amount"],
//                         "state" => $json_resp["transactions"][0]["related_resources"][0]["sale"]["state"]
//                     );

//                     // $this->json_throw($data);
//                     return $data;

//                 else :
//                     $data = array();
//                     $data["status"] = 0;
//                     // dd($json_resp);
//                     if ($json_resp["name"] == "UNKNOWN_ERROR") :
//                         $error_msg = "Some Error occured, try again or try later !";
//                     elseif ($json_resp["name"] == "VALIDATION_ERROR") :
//                         $error_msg = "Credit Card or CVV or Exp Date not valid !";
//                     elseif (isset($json_resp["details"]) && $json_resp["details"][0]["issue"] == "Value is invalid.") :
//                         $error_msg = "Invalid Credit Card !";
//                     elseif (isset($json_resp["details"])) :
//                         $error_msg = $json_resp["details"][0]["issue"];
//                     else :
//                         $error_msg = "Some Error occured, try again or try later !";
//                     endif;
//                     $data["error"] = $error_msg;
//                     // $this->json_throw($data);
//                     return $data;
//                 endif;
//             else :
//                 $data = array();
//                 $data["status"] = false;
//                 $data["error"] = $json_resp;
//                 // $this->json_throw($data);
//                 return $data;
//             endif;
//         else :
//             $data = array();
//             $data["status"] = false;
//             $data["error"] = "Invalid API KEY";
//             //   $this->json_throw($data);
//             return $data;
//         endif;
//     }
   
// }