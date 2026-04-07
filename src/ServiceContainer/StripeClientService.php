<?php

namespace Systha\Core\ServiceContainer;

use Exception;
use Illuminate\Support\Carbon;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Systha\Core\Models\PaymentCredential;
use Systha\Core\Models\TaxMaster;

use Systha\Core\Models\VendorModel;

class StripeClientService
{
    private StripeClient $client;

    public function __construct(?VendorModel $vendor = null)
    {
        dd($vendor);
        if ($vendor) {
            if ($pay = $vendor->defaultPaymentCredential && $vendor->defaultPaymentCredential->val2) {
                $this->client = new StripeClient($vendor->defaultPaymentCredential->val2);
            } else {
                throw_unless($pay, new Exception('Vendor default payment credential is not configured'));
            }
        } else {
            $pay = PaymentCredential::query()->where('name', 'stripe')->where('is_default', 1)->where('is_deleted', 0)->first();
            throw_unless($pay, new Exception('Stripe default payment credential is not configured'));
            $this->client = new StripeClient($pay->val2);
        }
    }

    public function createCustomer(array $data)
    {
        return $this->client->customers->create(array_merge($data, [
            'description' => config('app.name') . ' customer',
        ]));
    }

    public function createCard(string $token, string $customer_id)
    {
        return $this->client->customers->createSource(
            $customer_id,
            ['source' => $token]
        );
    }

    public function retrieveCart($customer_id, $card_id)
    {
        return $this->client->customers->retrieveSource(
            $customer_id,
            $card_id,
            []
        );
    }

    private function interval(string $interval): string
    {
        $intervals = [
            'daily' => 'day',
            'weekly' => 'week',
            'monthly' => 'month',
            'yearly' => 'year'
        ];
        if (isset($intervals[$interval])) {
            return $intervals[$interval];
        }
        if (isset(array_flip($intervals)[$interval])) {
            return $interval;
        }
        return 'month';
    }

    public function createPlan($subscription)
    {
        $package = $subscription->package;
        $p = $this->client->products->create([
            'name' => $package->package_name,
            'metadata' => [
                'product_id' => $subscription->id,
                'product_name' => $package->package_name,
            ],
        ]);

        if ($package->vendor_id) {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->where('vendor_id', $package->vendor_id)->where('is_deleted', 0)->first();
        } else {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->whereNull('vendor_id')->where('is_deleted', 0)->first();
        }
        $taxamount = 0;
        if ($tax) {
            if ($tax->type == 'percentage') {
                $taxamount = $tax->value / 100 * $package->price;
            } else {
                $taxamount = $tax->value;
            }
        }

        return $this->client->prices->create([
            'unit_amount' => ($package->price + $taxamount) * 100,
            'currency' => 'usd',
            'recurring' => ['interval' => $this->interval(strtolower($subscription->packageType->type_name))],
            'product' => $p->id,
            'metadata' => [
                'product_id' => $subscription->id,
                'product_name' => $package->package_name,
            ]
        ]);
    }

    public function createPackageAsProduct($package)
    {
        $p = $this->client->products->create([
            'name' => $package->package_name,
            'metadata' => [
                'package_type' => 'package',
                'package_id' => $package->id,
                'package_name' => $package->package_name,
                'image' => $package->package_thumb,
            ],
        ]);

        if ($package->vendor_id) {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->where('vendor_id', $package->vendor_id)->where('is_deleted', 0)->first();
        } else {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->whereNull('vendor_id')->where('is_deleted', 0)->first();
        }
        $taxamount = 0;
        if ($tax) {
            if ($tax->type == 'percentage') {
                $taxamount = $tax->value / 100 * $package->price;
            } else {
                $taxamount = $tax->value;
            }
        }

        return $p->id;
    }

    public function createPackageTypeAsProduct($package, $type)
    {
        $name = $package->package_name . '-' . $type->type_name;
        $type_amount = $type->amount - ($type->discount / 100 * $type->amount);
        if (!$package->stripe_product_id) {
            $p = $this->client->products->create([
                'name' => $package->package_name,
                'metadata' => [
                    'product_id' => $type->id,
                    'product_name' => $name,
                ],
            ]);

            $package->stripe_product_id = $p->id;
            $package->save();
        }

        if ($package->vendor_id) {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->where('vendor_id', $package->vendor_id)->where('is_deleted', 0)->first();
        } else {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->whereNull('vendor_id')->where('is_deleted', 0)->first();
        }
        $taxamount = 0;
        if ($tax) {
            if ($tax->type == 'percentage') {
                $taxamount = $tax->value / 100 * $type_amount;
            } else {
                $taxamount = $tax->value;
            }
        }


        if (!$type->stripe_price_id) {
            return $this->client->prices->create([
                'unit_amount' => ($type_amount + $taxamount) * 100,
                'currency' => 'usd',
                'recurring' => ['interval' => strtolower($type->type_name), 'interval_count' => $type->duration ?? 1],
                'product' => $package->stripe_product_id,
                'metadata' => [
                    'product_id' => $type->id,
                    'product_name' => $name,
                ]
            ]);
        } else {
            return null;
        }
    }

    public function updatePackageAsPlan($packageAsProduct)
    {
        $this->client->products->update(
            $packageAsProduct->stripe_product_id,
            [
                'name' => $packageAsProduct->package_name,
                'metadata' => [
                    'package_id' => $packageAsProduct->id,
                    'package_name' => $packageAsProduct->package_name,
                ],
            ]
        );
    }

    public function createPrice($product_id, $subscription)
    {
        $package = $subscription->package;

        if ($package->vendor_id) {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->where('vendor_id', $package->vendor_id)->where('is_deleted', 0)->first();
        } else {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->whereNull('vendor_id')->where('is_deleted', 0)->first();
        }
        $taxamount = 0;
        if ($tax) {
            if ($tax->type == 'percentage') {
                $taxamount = $tax->value / 100 * $package->price;
            } else {
                $taxamount = $tax->value;
            }
        }

        return $this->client->prices->create([
            'unit_amount' => ($package->price + $taxamount) * 100,
            'currency' => 'usd',
            'recurring' => ['interval' => $this->interval(strtolower($subscription->packageType->type_name))],
            'product' => $product_id,
            'metadata' => [
                'product_id' => $subscription->id,
                'product_name' => $package->package_name,
            ]
        ]);
    }

    public function updatePlan($product)
    {
        $this->client->products->update(
            $product->product_id,
            [
                'name' => $product->name,
                'metadata' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                ],
            ]
        );
    }

    public function createSubscription($cart)
    {
        return $this->client->subscriptions->create([
            'customer' => $cart['customer_id'],
            'items' => [
                ['price' => $cart['plan_id']],
            ],
            'metadata' => $cart,
        ]);
    }

    public function createPackageSubscription(array $cart, string $type)
    {
        $cart['package_type'] = $type;

        $billingAnchor = Carbon::now()->addSeconds(60)->timestamp;

        $params = [
            'customer' => $cart['customer_id'],
            'items'    => [
                ['price' => $cart['plan_id']],
            ],
            'billing_cycle_anchor' => $billingAnchor,
            'proration_behavior'   => 'none',
        ];

        try {
            return $this->client->subscriptions->create($params);
        } catch (ApiErrorException $e) {
            throw new Exception('Stripe Subscription Error: ' . $e->getMessage());
        }
    }

    public function createPackagePrice($subscription)
    {
        $package = $subscription->package;
        if ($package->vendor_id) {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->where('vendor_id', $package->vendor_id)->where('is_deleted', 0)->first();
        } else {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->whereNull('vendor_id')->where('is_deleted', 0)->first();
        }
        $taxamount = 0;
        if ($tax) {
            if ($tax->type == 'percentage') {
                $taxamount = $tax->value / 100 * $package->price;
            } else {
                $taxamount = $tax->value;
            }
        }

        return $this->client->prices->create([
            'unit_amount' => ($package->price + $taxamount) * 100,
            'currency' => 'usd',
            'recurring' => ['interval' => $this->interval(strtolower($subscription->packageType->type_name))],
            'product' => $package->stripe_product_id,
            'metadata' => [
                'product_id' => $subscription->id,
                'product_name' => $package->package_name,
            ]
        ]);
    }

    public function createMembershipTypeAsProduct($membership, $type)
    {
        $name = $membership->package_name . '-' . $type->type_name . 'ly';

        if (!$membership->stripe_product_id) {
            $p = $this->client->products->create([
                'name' => $membership->name,
                'metadata' => [
                    'product_id' => $type->id,
                    'product_name' => $name,
                ],
            ]);

            $membership->stripe_product_id = $p->id;
            $membership->save();
        }

        if ($membership->vendor_id) {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->where('vendor_id', $membership->vendor_id)->where('is_deleted', 0)->first();
        } else {
            $tax = TaxMaster::where('tax_code', 'sales_tax')->whereNull('vendor_id')->where('is_deleted', 0)->first();
        }
        $taxamount = 0;
        if ($tax) {
            if ($tax->type == 'percentage') {
                $taxamount = $tax->value / 100 * $type->amount;
            } else {
                $taxamount = $tax->value;
            }
        }


        if (!$type->stripe_price_id) {
            return $this->client->prices->create([
                'unit_amount' => ($type->amount + $taxamount) * 100,
                'currency' => 'usd',
                'recurring' => ['interval' => strtolower($type->type_name), 'interval_count' => $type->duration ?? 1],
                'product' => $membership->stripe_product_id,
                'metadata' => [
                    'product_id' => $type->id,
                    'product_name' => $name,
                ]
            ]);
        } else {
            return null;
        }
    }

    public function retrieveSubscription($subscription_id)
    {
        return $this->client->subscriptions->retrieve(
            $subscription_id,
            []
        );
    }

    public function paymentIntents($customer_id)
    {
        return $this->client->paymentIntents->all(['customer' => $customer_id]);
    }

    public function cancelSubscription($subscription_id)
    {
        return $this->client->subscriptions->cancel(
            $subscription_id,
            []
        );
    }

    public function pauseSubscription($subscription_id)
    {
        return $this->client->subscriptions->update(
            $subscription_id,
            ['pause_collection' => ['behavior' => 'void']]
        );
    }

    public function resumeSubscription($subscription_id)
    {
        return $this->client->subscriptions->update(
            $subscription_id,
            [
                'pause_collection' => '',
            ]
        );
    }

    public function retriveInvoice($invoice_id)
    {
        return $this->client->invoices->retrieve(
            $invoice_id,
            []
        );
    }
}
