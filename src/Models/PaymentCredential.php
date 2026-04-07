<?php

namespace Systha\Core\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class PaymentCredential extends Model
{
    protected $guarded = [];

    public function stripeToken(array $data)
    {
        if($this->name == 'stripe') {
            $req = new Client();
            $res = $req->post('https://api.stripe.com/v1/tokens', [
                'headers' => [
                    'Authorization' => "Bearer $this->val2"
                ],
                'form_params' => [
                    'card' => [
                        'number' => $data['card_number'],
                        'exp_month' => $data['expm'],
                        'exp_year' => $data['expy'],
                        'cvc' => $data['code'],
                    ]
                ]
            ]);
            if($res->getStatusCode() === 200) {
                $responseJSON = json_decode($res->getBody(), true);
                return $responseJSON['id'];
            }
        }
    }
}
