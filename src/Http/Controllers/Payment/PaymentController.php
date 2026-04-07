<?php

namespace Systha\Core\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Systha\Salon\Lib\Authorize\AuthorizePay;
use Systha\Salon\Services\Payment\{Payment, AuthorizeNet};
use Exception;
use Systha\Core\Models\Client;
use Systha\Core\Models\CustomerPaymentProfile;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\VendorPaymentProfile;

class PaymentController extends Controller
{
    /**
     * Client which is doing the payment
     *
     * @var Client
     */
    protected $client;

    /**
     * Vendor which is doing the payment
     *
     * @var Vendor
     */
    protected $vendor;

    /**
     * Requested data
     *
     * @var Request
     */
    protected $request;

    /**
     * Name as per card
     * It is null when the submission is done with customer profile so need to set on chargeCustomerProfile method
     *
     * @var String
     */
    protected $name_per_card;

    /**
     * Initialize Payment
     *
     * @param array $paymentData | (can include getaway key default is authorize.net)
     * @return void
     */
    protected function initPayment(array $data)
    {
        if (!$this->request->has('customer_profile')) {
            //if there is no selected profile
            $transaction = $this->authorizePayment($data);
            $this->customerPaymentProfile($data, $transaction);
            return $transaction;
        } else {
            //payment with customer profile
            $paymentProfile = CustomerPaymentProfile::find($this->request->customer_profile);
            $this->name_per_card = $paymentProfile->name_per_card;
            if ($paymentProfile->client_id === $this->client->id) {
                return $this->chargeCustomerProfile($paymentProfile, $data['amount']);
            } else {
                throw new Exception("Sorry your saved credentials not working");
            }
        }
    }

    protected function initSquarePayment(array $data)
    {
        if (!$this->request->has('customer_profile')) {
            //if there is no selected profile
            $transaction = $this->squarePayment($data);
            $this->createPaymentProfileSquare($data, $transaction);
            return $transaction;
        } else {
            //payment with customer profile
            $paymentProfile = CustomerPaymentProfile::find($this->request->customer_profile);
            $this->name_per_card = $paymentProfile->name_per_card;
            if ($paymentProfile->client_id === $this->client->id) {
                return $this->chargeCustomerProfile($paymentProfile, $data['amount']);
            } else {
                throw new Exception("Sorry your saved credentials not working");
            }
        }
    }

    protected function initStripePayment(array $data)
    {
        if (!$this->request->has('customer_profile')) {
            //if there is no selected profile
            $transaction = $this->stripePayment($data);
            // dd($transaction);
            $profile = $this->createPaymentProfileStripe($data, $transaction);
            return $transaction;
        } else {
            //payment with customer profile
            $paymentProfile = CustomerPaymentProfile::find($this->request->customer_profile);
            $this->name_per_card = $paymentProfile->name_per_card;
            if ($paymentProfile->client_id === $this->client->id) {
                return $this->chargeCustomerProfile($paymentProfile, $data['amount']);
            } else {
                throw new Exception("Sorry your saved credentials not working");
            }
        }
    }

    protected function initPaypalPayment(array $data)
    {
        if (!$this->request->has('customer_profile')) {
            //if there is no selected profile
            $transaction = $this->paypalPayment($data);
            $this->createPaymentProfilePaypal($data, $transaction);
            return $transaction;
        } else {
            //payment with customer profile
            $paymentProfile = CustomerPaymentProfile::find($this->request->customer_profile);
            $this->name_per_card = $paymentProfile->name_per_card;
            if ($paymentProfile->client_id === $this->client->id) {
                return $this->chargeCustomerProfile($paymentProfile, $data['amount']);
            } else {
                throw new Exception("Sorry your saved credentials not working");
            }
        }
    }

    /**
     * Set the client
     *
     * @param Client $client
     * @return self
     */
    protected function withClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }
    /**
     * Set the vendor
     *
     * @param Vendor $client
     * @return self
     */
    protected function withVendor(Vendor $vendor): self
    {
        $this->vendor = $vendor;
        return $this;
    }

    /**
     * Set the request
     *
     * @param Request $request
     * @return self
     */
    protected function withRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Make default authorize net payment
     *
     * @param array $data
     * @return void
     */
    public function authorizePayment(array $data)
    {
        return app('payment.authorizeNet', $data)->makePayment();
    }

    /**
     * Make default Square payment
     *
     * @param array $data
     * @return void
     */
    public function squarePayment(array $data)
    {
        return app('payment.square', $data)->makePayment();
    }

    /**
     * Make default Stripe payment
     *
     * @param array $data
     * @return void
     */
    public function stripePayment(array $data)
    {
        return app('payment.stripe', $data)->makePayment();
    }

    /**
     * Make default Stripe payment
     *
     * @param array $data
     * @return void
     */
    public function paypalPayment(array $data)
    {
        return app('payment.paypal', $data)->makePayment();
    }

    /**
     * Create new customer profile
     *
     * @param array $data
     * @return Response
     */
    protected function customerProfile(array $data)
    {
        $at = new AuthorizePay();
        return  $at->createCustomerProfile($data['email'], $data);
    }

    /**
     * Charge client with profile
     *
     * @param CustomerPaymentProfile $profile
     * @param float $amount
     * @return void
     */
    protected function chargeCustomerProfile(CustomerPaymentProfile $profile, float $amount)
    {
        $at = new AuthorizePay();
        $payment =  $at->chargeCustomerProfile($profile->profile_id, $profile->payment_id, $amount);
        if (gettype($payment) == "array" && isset($payment['error'])) {
            throw new \Exception($payment['error'], 500);
        }
        return $payment;
    }

    /**
     * Save or update customer profile
     *
     * @param array $data
     * @param [type] $transaction
     * @return CustomerPaymentProfile
     */
    public function customerPaymentProfile(array $data, $transaction)
    {
        $customerProfile = $this->customerProfile($data);
        return $this->duplicateCard($customerProfile, $transaction);
    }

    /**
     * Check for customer profile duplication
     *
     * @param [type] $customerProfile
     * @param [type] $transaction
     * @return void
     */
    public function duplicateCard($customerProfile, $transaction)
    {
        if ($this->client) {
            $paymentProfile = $this->client ? $this->client->paymentProfiles()->where([
                'card' => $transaction['data']['credit_card']['type'],
                'card_no' => $transaction['data']['credit_card']['number'],
            ])->first() : [];
        } else {
            $paymentProfile = $this->vendor ? $this->vendor->paymentProfiles()->where([
                'card' => $transaction['data']['credit_card']['type'],
                'card_no' => $transaction['data']['credit_card']['number'],
            ])->first() : [];
        }
        return $paymentProfile ? $this->updatePaymentProfile($paymentProfile, $transaction) : $this->createPaymentProfile($customerProfile, $transaction);
    }

    /**
     * Update customer profile
     *
     * @param [type] $paymentProfile
     * @param [type] $transaction
     * @return CustomerPaymentProfile
     */
    public function updatePaymentProfile($paymentProfile, $transaction)
    {
        $paymentProfile->update([
            'card' => $transaction['data']['credit_card']['type'],
            'name_per_card' => $transaction['data']['credit_card']['name_per_card'],
            'card_no' => $transaction['data']['credit_card']['number'],
            'expiry' => $transaction['data']['credit_card']['expiry'],
        ]);
        return $paymentProfile;
    }

    /**
     * Store customer profile
     *
     * @param [type] $customerProfile
     * @param [type] $transaction
     */
    public function createPaymentProfile($customerProfile, $transaction)
    {
        if ($this->client) {
            return CustomerPaymentProfile::create(
                array_merge([
                    'client_id' => $this->client->id,
                    'card' => $transaction['data']['credit_card']['type'],
                    'name_per_card' => $transaction['data']['credit_card']['name_per_card'],
                    'card_no' => $transaction['data']['credit_card']['number'],
                    'expiry' => $transaction['data']['credit_card']['expiry'],
                ], $customerProfile)
            );
        } else {
            return VendorPaymentProfile::create(
                array_merge([
                    'vendor_id' => $this->vendor->id,
                    'card' => $transaction['data']['credit_card']['type'],
                    'name_per_card' => $transaction['data']['credit_card']['name_per_card'],
                    'card_no' => $transaction['data']['credit_card']['number'],
                    'expiry' => $transaction['data']['credit_card']['expiry'],
                ], $customerProfile)
            );
        }
    }

    /**
     * Store customer profile
     *
     * @param [type] $customerProfile
     * @param [type] $transaction
     */
    public function createPaymentProfileSquare($data, $transaction)
    {
        $month = $transaction->getCardDetails()->getCard()->getExpMonth();
        $month = $month < 10 ? "0$month" : $month;
        if ($this->client) {
            return CustomerPaymentProfile::create([
                'client_id' => $this->client->id,
                'card' => $transaction->getCardDetails()->getCard()->getCardBrand(),
                'payment_id' => $transaction->getId(),
                'name_per_card' => $data['name_per_card'],
                'amount' => $data['amount'],
                'card_no' => $transaction->getCardDetails()->getCard()->getLast4(),
                'expiry' => $transaction->getCardDetails()->getCard()->getExpYear() . '-' . $month . '-' . '01',
            ]);
        } else {
            return VendorPaymentProfile::create(
                [
                    'vendor_id' => $this->vendor->id,
                    'payment_id' => $transaction->getId(),
                    'card' => $transaction->getCardDetails()->getCard()->getCardBrand(),
                    'name_per_card' => $transaction['data']['credit_card']['name_per_card'],
                    'card_no' => $transaction->getCardDetails()->getCard()->getLast4(),
                    'amount' => $data['amount'],
                    'expiry' => $transaction->getCardDetails()->getCard()->getExpYear() . '-' . $month . '-' . '01',
                ]
            );
        }
    }

    /**
     * Store customer profile
     *
     * @param [type] $customerProfile
     * @param [type] $transaction
     */
    public function createPaymentProfileStripe($data, $transaction)
    {
        $month = $transaction['payment_method_details']['card']['exp_month'];
        $month = $month < 10 ? "0$month" : $month;
        if ($this->client) {
            return CustomerPaymentProfile::create([
                'client_id' => $this->client->id,
                'profile_id' => $this->client->id,
                'card' => $transaction['payment_method_details']['card']['brand'],
                'payment_id' => $transaction['id'],
                'name_per_card' => $data['name_per_card'],
                'amount' => $data['amount'],
                'card_no' => $transaction['payment_method_details']['card']['last4'],
                'expiry' => $transaction['payment_method_details']['card']['exp_year'] . '-' . $month . '-' . '01',
            ]);
        } else {
            return VendorPaymentProfile::create(
                [
                    'vendor_id' => $this->vendor->id,
                    'card' => $transaction['payment_method_details']['card']['brand'],
                    'payment_id' => $transaction['id'],
                    'name_per_card' => $data['name_per_card'],
                    'amount' => $data['amount'],
                    'card_no' => $transaction['payment_method_details']['card']['last4'],
                    'expiry' => $transaction['payment_method_details']['card']['exp_year'] . '-' . $month . '-' . '01',
                ]
            );
        }
    }

    /**
     * Store customer profile
     *
     * @param [type] $customerProfile
     * @param [type] $transaction
     */
    public function createPaymentProfilePaypal($data, $transaction)
    {
        $month = $transaction['data']['credit_card']['expire_month'];
        $month = $month < 10 ? "0$month" : $month;
        if ($this->client) {
            return CustomerPaymentProfile::create([
                'client_id' => $this->client->id,
                'card' => $transaction['data']['credit_card']['type'],
                'payment_id' => $transaction['data']['id'],
                'name_per_card' => $data['name_per_card'],
                'amount' => $data['amount'],
                'card_no' => $transaction['data']['credit_card']['number'],
                'expiry' => $transaction['data']['credit_card']['expire_year'] . '-' . $month . '-' . '01',
            ]);
        } else {
            return VendorPaymentProfile::create(
                [
                    'vendor_id' => $this->vendor->id,
                    'card' => $transaction['data']['credit_card']['type'],
                    'payment_id' => $transaction['data']['id'],
                    'name_per_card' => $data['name_per_card'],
                    'amount' => $data['amount'],
                    'card_no' => $transaction['data']['credit_card']['number'],
                    'expiry' => $transaction['data']['credit_card']['expire_year'] . '-' . $month . '-' . '01',
                ]
            );
        }
    }
}
