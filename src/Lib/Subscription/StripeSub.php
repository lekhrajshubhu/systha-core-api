<?php

namespace Systha\Core\Lib\Subscription;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Systha\Core\Models\PaymentCredential;
use Systha\Core\Models\TaxMaster;
use Systha\Core\Models\Vendor;

class StripeSub
{
    private $client;

    public function __construct(Vendor $vendor = null)
    {
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

    public function retrieveActiveCustomer(string $customerId)
    {
        // 1. Fetch customer with expanded payment method
        $customer = $this->client->customers->retrieve($customerId);

        // 2. Check if deleted
        if (!empty($customer->deleted)) {
            return null; // ❌ inactive
        }

        return $customer; // ✅ active

    }

    public function attachStripeTokenToCustomer(string $customerId, string $stripeToken)
    {
        return $this->client->customers->update(
            $customerId,
            ['source' => $stripeToken],
            // ['expand' => ['default_source']]
        );
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

    public function retrieveCard($customer_id, $card_id)
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
        // dd($subscription->packageType);
        $package = $subscription->package;
        // dd($this->client->products,$package);
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
                // 'price' => $package->price,
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
        // header('Access-Control-Allow-Origin:*');
        // dd($p->id);
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

        // dd($package->stripe_product_id);

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
    // public function createPackageSubscription($cart,$start_date,$type)
    // {
    //     //dd($cart,$start_date,$type);
    //     $cart['package_type'] = $type;
    //     $start_date = date_format(date_create($start_date),'U');
    //     try {
    //         return $this->client->subscriptions->create([
    //             'customer' => $cart['customer_id'],
    //             'items' => [
    //                 ['price' => $cart['plan_id']],
    //             ],
    //             'metadata' => ["cart_id" => $cart['cart_id']],

    //             'billing_cycle_anchor' => $start_date,
    //         ]);
    //         //code...
    //     } catch (\Throwable $th) {
    //         // header('Access-Control-Allow-Origin:*');
    //         dd($th);
    //         return $th->getMessage();
    //     }
    // }
    // public function createPackageSubscription($cart, $start_date, $type)
    // {
    //     $cart['package_type'] = $type;

    //     // Check if start_date is today, if so, set it to the current date.
    //     if (Carbon::parse($start_date)->isToday()) {
    //         $start_date = now()->format('U'); // Set to current timestamp
    //     } else {
    //         $start_date = Carbon::parse($start_date)->format('U'); // Convert provided start_date to timestamp
    //     }

    //     try {
    //         return $this->client->subscriptions->create([
    //             'customer' => $cart['customer_id'],
    //             'items' => [
    //                     ['price' => $cart['plan_id']],
    //                 ],
    //             //'metadata' => ["cart_id" => $cart['cart_id']],
    //             'billing_cycle_anchor' => $start_date,
    //         ]);
    //     } catch (\Throwable $th) {
    //         $errorMessage = $th->getMessage();

    //         if (str_contains($errorMessage, 'billing_cycle_anchor')) {
    //             throw new \Exception('Invalid subscription start date. The billing cycle must start in the future.');
    //         }

    //         throw new \Exception('Stripe Subscription Error: ' . $errorMessage);
    //         // return $th->getMessage();
    //     }
    // }
    // public function createPackageSubscription($cart, $start_date, $type)
    // {
    //     $cart['package_type'] = $type;

    //     // Stripe requires billing_cycle_anchor to be in the future if used
    //     $startTimestamp = Carbon::parse($start_date);
    //     $now = now();

    //     // If today, set no anchor (Stripe uses default)
    //     $billingAnchor = null;
    //     if ($startTimestamp->greaterThan($now)) {
    //         $billingAnchor = $startTimestamp->timestamp;
    //     }

    //     try {
    //         $params = [
    //             'customer' => $cart['customer_id'],
    //             'items' => [
    //                 ['price' => $cart['plan_id']],
    //             ],
    //             // Optional: add metadata
    //             //'metadata' => ['cart_id' => $cart['cart_id']],
    //         ];

    //         if ($billingAnchor) {
    //             $params['billing_cycle_anchor'] = $billingAnchor;
    //             $params['proration_behavior'] = 'none'; // Avoid unexpected prorations
    //         }

    //         return $this->client->subscriptions->create($params);
    //     } catch (\Throwable $th) {
    //         $message = $th->getMessage();

    //         if (str_contains($message, 'billing_cycle_anchor')) {
    //             throw new \Exception('Invalid subscription start date. It must be a future time.');
    //         }

    //         throw new \Exception('Stripe Subscription Error: ' . $message);
    //     }
    // }


    public function createPackageSubscription(array $cart, string $type)
    {
        $cart['package_type'] = $type;

        // Always use “now + 60 seconds” to satisfy Stripe’s “must be future” rule
        $billingAnchor = Carbon::now()->addSeconds(60)->timestamp;   // safe future time

        $params = [
            'customer' => $cart['customer_id'],
            'items'    => [
                ['price' => $cart['plan_id']],
            ],
            'billing_cycle_anchor' => $billingAnchor,
            'proration_behavior'   => 'none',   // avoid prorations
            // 'metadata'          => ['cart_id' => $cart['cart_id']],
        ];

        try {
            return $this->client->subscriptions->create($params);
        } catch (ApiErrorException $e) {
            // Surface Stripe’s own message for easier debugging
            throw new \Exception('Stripe Subscription Error: ' . $e->getMessage());
        }
    }

    public function createPackagePrice($subscription)
    {
        $package = $subscription->package;
        // dd($subscription->packageType);
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
            // 'recurring' => ['interval' => $this->interval('weekly')],
            'product' => $package->stripe_product_id,
            'metadata' => [
                'product_id' => $subscription->id,
                'product_name' => $package->package_name,
            ]
        ]);
    }

    public function createMembershipTypeAsProduct($membership, $type)
    {
        // dd($subscription->packageType);
        // $package = $subscription->package;
        // dd($this->client->products,$package);
        $name = $membership->package_name . '-' . $type->type_name . 'ly';
        // if($type->discount_type == 'percent'){
        //     $type_amount = $type->amount - ($type->discount / 100 * $type->amount);
        // }


        // dd($type_amount);
        // dd($name);
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

        // dd($package->stripe_product_id);

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
        // dd($subscription_id);
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
    public function deleteCustomer(string $customerId)
    {
        try {
            return $this->client->customers->delete($customerId);
        } catch (\Exception $e) {
            // Optionally log the error
            Log::error('StripeSub: Failed to delete customer', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
