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

namespace Systha\Core\Http\Controllers\StripeWebhook;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Stripe\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Systha\Core\Http\Controllers\BaseController;
use Systha\Core\Models\RecurringSubscription;
use Systha\Core\Models\RecurringPayment;
use Systha\Core\Models\VendorModel;
use Systha\Core\Services\StripeService;

class StripeWebhookController extends BaseController
{
    public function handle(Request $request)
    {
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        $payload = $request->getContent();
        $sigHeader = $request->server('HTTP_STRIPE_SIGNATURE');

        try {
            $event = $endpointSecret
                ? \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret)
                : json_decode($payload);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        $type = $event->type ?? null;
        $object = $event->data->object ?? null;

        try {
            switch ($type) {
                case 'invoice.payment_succeeded':
                    $this->handleInvoiceSucceeded($object);
                    break;
                case 'invoice.payment_failed':
                    $this->handleInvoiceFailed($object);
                    break;
                case 'customer.subscription.updated':
                case 'customer.subscription.deleted':
                case 'customer.subscription.paused':
                    $this->handleSubscriptionUpdated($object);
                    break;
                default:
                    Log::info('Unhandled Stripe event type', ['type' => $type]);
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler error', ['type' => $type, 'message' => $e->getMessage()]);
            return response('Error', 500);
        }

        return response('Webhook handled', 200);
    }

    private function handleInvoiceSucceeded($invoice): void
    {
        $subscriptionId = $invoice->subscription ?? null;
        if (!$subscriptionId) {
            return;
        }

        $subscription = RecurringSubscription::where('stripe_subscription_id', $subscriptionId)->first();
        if (!$subscription) {
            return;
        }

        $nextPeriod = $invoice->lines->data[0]->period->end ?? null;
        $nextBillingDate = $nextPeriod ? Carbon::createFromTimestamp($nextPeriod)->toDateString() : null;

        $card = $this->extractCardFromInvoice($invoice, $subscription);

        $status = $this->mapStatus($invoice->payment_intent->status ?? null, $invoice->status ?? null, 'succeeded');

        RecurringPayment::updateOrCreate(
            ['stripe_payment_intent_id' => $invoice->payment_intent ?? null],
            [
                'recurring_subscription_id' => $subscription->id,
                'stripe_invoice_id' => $invoice->id ?? null,
                'stripe_payment_method_id' => $card['payment_method_id'],
                'amount_cents' => $invoice->amount_paid ?? null,
                'currency' => $invoice->currency ?? null,
                'status' => $status,
                'card_brand' => $card['brand'],
                'card_last4' => $card['last4'],
                'exp_month' => $card['exp_month'],
                'exp_year' => $card['exp_year'],
                'paid_at' => isset($invoice->status_transitions->paid_at)
                    ? Carbon::createFromTimestamp($invoice->status_transitions->paid_at)
                    : Carbon::now(),
                'processed_at' => Carbon::now(),
                'raw' => $invoice,
            ]
        );

        $subscription->update([
            'status' => 'active',
            'next_billing_date' => $nextBillingDate,
        ]);
    }

    private function handleInvoiceFailed($invoice): void
    {
        $subscriptionId = $invoice->subscription ?? null;
        if (!$subscriptionId) {
            return;
        }

        $subscription = RecurringSubscription::where('stripe_subscription_id', $subscriptionId)->first();
        if (!$subscription) {
            return;
        }

        $card = $this->extractCardFromInvoice($invoice, $subscription);

        $status = $this->mapStatus($invoice->payment_intent->status ?? null, $invoice->status ?? null, 'failed');

        RecurringPayment::updateOrCreate(
            ['stripe_payment_intent_id' => $invoice->payment_intent ?? null],
            [
                'recurring_subscription_id' => $subscription->id,
                'stripe_invoice_id' => $invoice->id ?? null,
                'stripe_payment_method_id' => $card['payment_method_id'],
                'amount_cents' => $invoice->amount_due ?? null,
                'currency' => $invoice->currency ?? null,
                'status' => $status,
                'card_brand' => $card['brand'],
                'card_last4' => $card['last4'],
                'exp_month' => $card['exp_month'],
                'exp_year' => $card['exp_year'],
                'paid_at' => null,
                'processed_at' => Carbon::now(),
                'raw' => $invoice,
            ]
        );

        $subscription->update([
            'status' => 'past_due',
        ]);
    }

    private function handleSubscriptionUpdated($subscriptionObj): void
    {
        $subscriptionId = $subscriptionObj->id ?? null;
        if (!$subscriptionId) {
            return;
        }

        $subscription = RecurringSubscription::where('stripe_subscription_id', $subscriptionId)->first();
        if (!$subscription) {
            return;
        }

        $periodEnd = $subscriptionObj->current_period_end ?? null;
        $nextBillingDate = $periodEnd ? Carbon::createFromTimestamp($periodEnd)->toDateString() : $subscription->next_billing_date;
        $status = $subscriptionObj->status ?? $subscription->status;

        $subscription->update([
            'status' => $status,
            'next_billing_date' => $nextBillingDate,
        ]);
    }

    private function mapStatus($paymentIntentStatus, $invoiceStatus, $default): string
    {
        $piStatus = $paymentIntentStatus ?? '';
        return match ($piStatus) {
            'succeeded' => 'succeeded',
            'processing' => 'processing',
            'requires_action', 'requires_payment_method' => 'requires_action',
            'canceled' => 'canceled',
            default => match ($invoiceStatus) {
                'paid' => 'succeeded',
                'uncollectible', 'void', 'canceled' => 'failed',
                default => $default,
            },
        };
    }

    private function extractCardFromInvoice($invoice, RecurringSubscription $subscription): array
    {
        $paymentMethodId = $invoice->payment_method ?? null;
        $brand = $last4 = null;
        $expMonth = $expYear = null;

        // Try expanded payment_intent->charges->data[0]->payment_method_details->card
        $pi = $invoice->payment_intent ?? null;
        if (is_object($pi) && isset($pi->charges->data[0]->payment_method_details->card)) {
            $pmCard = $pi->charges->data[0]->payment_method_details->card;
            $brand = $pmCard->brand ?? null;
            $last4 = $pmCard->last4 ?? null;
            $expMonth = $pmCard->exp_month ?? null;
            $expYear = $pmCard->exp_year ?? null;
            $paymentMethodId = $pi->payment_method ?? $paymentMethodId;
        }

        // If not expanded, fetch via StripeService using vendor credentials
        if (!$brand || !$last4) {
            $pmId = $paymentMethodId ?? (is_object($pi) ? ($pi->payment_method ?? null) : null);
            if ($pmId) {
                try {
                    $vendor = VendorModel::find($subscription->vendor_id);
                    if ($vendor) {
                        $stripe = new StripeService($vendor);
                        $pm = $stripe->retrievePaymentMethod($pmId);
                        $card = $pm->card ?? null;
                        if ($card) {
                            $brand = $card->brand ?? $brand;
                            $last4 = $card->last4 ?? $last4;
                            $expMonth = $card->exp_month ?? $expMonth;
                            $expYear = $card->exp_year ?? $expYear;
                            $paymentMethodId = $pmId;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to retrieve payment method for invoice', ['error' => $e->getMessage()]);
                }
            }
        }

        return [
            'payment_method_id' => $paymentMethodId,
            'brand' => $brand,
            'last4' => $last4,
            'exp_month' => $expMonth,
            'exp_year' => $expYear,
        ];
    }
}
