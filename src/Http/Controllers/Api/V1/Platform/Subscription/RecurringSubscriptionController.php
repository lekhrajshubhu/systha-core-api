<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Subscription;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stripe\Exception\ApiErrorException;
use Systha\Core\Models\ClientModel;
use Systha\Core\Models\Package;
use Systha\Core\Models\PackageType;
use Systha\Core\Models\RecurringSubscriptionDraft;
use Systha\Core\Models\RecurringSubscription;
use Systha\Core\Models\StripeCustomer;
use Systha\Core\Models\StripePaymentMethod;
use Systha\Core\Models\RecurringPayment;
use Systha\Core\Models\VendorModel;
use Systha\Core\Services\StripeService;

class RecurringSubscriptionController extends Controller
{
    public function createRecurringSubscriptionIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_code' => ['required', 'exists:vendors,vendor_code'],
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'plan_id' => ['required', 'integer', 'exists:package_types,id'],
            'contact.first_name' => ['required', 'string'],
            'contact.last_name' => ['required', 'string'],
            'contact.email' => ['required', 'email'],
            'contact.phone' => ['required', 'string'],
            'schedule.date' => ['required', 'date'],
            'schedule.time' => ['required', 'string'],
            'address.line_1' => ['required', 'string'],
            'address.city' => ['required', 'string'],
            'address.state' => ['required', 'string'],
            'address.zip' => ['required', 'string'],
            'note' => ['nullable', 'string'],
            'intent_type' => ['nullable', 'in:payment,setup'],
            'save_for_later' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $vendor = VendorModel::where('vendor_code', $data['vendor_code'])->firstOrFail();
        $package = Package::findOrFail($data['package_id']);
        $plan = PackageType::findOrFail($data['plan_id']);

        $amountCents = (int) round(($plan->amount ?? 0) * 100);
        $currency = 'usd';

        $client = ClientModel::firstOrCreate(
            ['email' => $data['contact']['email']],
            [
                'fname' => $data['contact']['first_name'],
                'lname' => $data['contact']['last_name'],
                'phone1' => $data['contact']['phone'],
            ]
        );

        $intentType = $data['intent_type'] ?? 'payment';
        $saveForLater = $data['save_for_later'] ?? true;

        $draft = RecurringSubscriptionDraft::create([
            'vendor_id' => $vendor->id,
            'client_id' => $client->id,
            'package_id' => $package->id,
            'plan_id' => $plan->id,
            'amount_cents' => $amountCents,
            'currency' => $currency,
            'status' => 'pending',
            'metadata' => [
                'schedule_date' => $data['schedule']['date'],
                'schedule_time' => $data['schedule']['time'],
                'note' => $data['note'] ?? null,
                'address' => $data['address'],
            ],
        ]);

        $stripe = new StripeService($vendor);
        $metadata = [
            'booking_reference' => $draft->booking_reference,
            'package_id' => $package->id,
            'plan_id' => $plan->id,
        ];

        if ($intentType === 'setup') {
            $intent = $stripe->createSetupIntentForVendorClient(
                $client,
                $vendor,
                $metadata,
                $draft->booking_reference
            );
            $draft->update([
                'stripe_customer_id' => $intent->customer,
                'stripe_setup_intent_id' => $intent->id,
            ]);

            return response()->json([
                'success' => true,
                'intentType' => 'setup_intent',
                'intentId' => $intent->id,
                'clientSecret' => $intent->client_secret,
                'bookingReference' => $draft->booking_reference,
                'amount' => $amountCents,
                'currency' => $currency,
            ]);
        }

        $intent = $stripe->createPaymentIntentForVendorClient(
            $client,
            $vendor,
            $amountCents,
            $currency,
            $metadata,
            $saveForLater,
            $draft->booking_reference
        );

        $draft->update([
            'stripe_customer_id' => $intent->customer,
            'stripe_payment_intent_id' => $intent->id,
        ]);

        return response()->json([
            'success' => true,
            'intentType' => 'payment_intent',
            'intentId' => $intent->id,
            'clientSecret' => $intent->client_secret,
            'bookingReference' => $draft->booking_reference,
            'amount' => $amountCents,
            'currency' => $currency,
        ]);
    }

    public function finalizeRecurringSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_reference' => ['required', 'uuid'],
            'stripe_intent_id' => ['required', 'string'],
            'intent_type' => ['required', 'in:payment_intent,setup_intent'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $draft = RecurringSubscriptionDraft::where('booking_reference', $data['booking_reference'])->firstOrFail();
        $vendor = VendorModel::findOrFail($draft->vendor_id);
        $client = ClientModel::findOrFail($draft->client_id);
        $plan = PackageType::find($draft->plan_id);
        $stripe = new StripeService($vendor);

        if (!$plan || empty($plan->stripe_price_id)) {
            return response()->json(['error' => 'Subscription plan is not configured with stripe_price_id'], 422);
        }

        $intentType = $data['intent_type'];
        $status = null;
        $paymentMethodId = null;
        $stripeCustomerId = null;
        $cardDetails = null;
        $stripeSubscriptionId = null;
        $latestInvoiceId = null;

        $providedIntentId = $data['stripe_intent_id'];
        $storedIntentId = $intentType === 'payment_intent'
            ? $draft->stripe_payment_intent_id
            : $draft->stripe_setup_intent_id;

        // Prefer stored intent id; validate when client sends one
        if ($storedIntentId && $providedIntentId && $storedIntentId !== $providedIntentId) {
            return response()->json(['error' => 'Intent mismatch with booking reference'], 422);
        }

        $intentIdToUse = $storedIntentId ?: $providedIntentId;

        if (!$intentIdToUse) {
            return response()->json(['error' => 'No intent found for this booking reference'], 404);
        }

        try {
            if ($intentType === 'payment_intent') {
                $intent = $stripe->retrievePaymentIntent($intentIdToUse);
                $status = $intent->status;
                $stripeCustomerId = $intent->customer;
                $paymentMethodId = $intent->payment_method;

                if (!in_array($intent->status, ['succeeded', 'requires_capture', 'requires_action']) && $intent->status !== 'processing') {
                    return response()->json(['error' => 'Payment not completed'], 400);
                }

                $cardDetails = optional($intent->charges->data[0]->payment_method_details ?? null)->card ?? null;
            } else {
                $intent = $stripe->retrieveSetupIntent($intentIdToUse);
                $status = $intent->status;
                $stripeCustomerId = $intent->customer;
                $paymentMethodId = $intent->payment_method;
                if ($intent->status !== 'succeeded') {
                    return response()->json(['error' => 'Setup not completed'], 400);
                }
                $paymentMethod = $stripe->retrievePaymentMethod($paymentMethodId);
                $cardDetails = $paymentMethod->card ?? null;
            }
        } catch (ApiErrorException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode(),
                'status' => $e->getHttpStatus(),
            ], 400);
        }

        // Fallback: when payment intent has no charge yet (e.g., requires_capture) we may not get card details from charges
        if (!$cardDetails && $paymentMethodId) {
            try {
                $paymentMethod = $stripe->retrievePaymentMethod($paymentMethodId);
                $cardDetails = $paymentMethod->card ?? null;
            } catch (ApiErrorException $e) {
                // Keep going; we just won't persist card details
            }
        }

        if ($draft->stripe_customer_id && $stripeCustomerId && $draft->stripe_customer_id !== $stripeCustomerId) {
            return response()->json(['error' => 'Customer mismatch'], 422);
        }

        $startDate = Carbon::parse($draft->metadata['schedule_date'] ?? Carbon::now())->toDateString();
        $interval = $this->resolveInterval($plan?->type_name);
        $intervalCount = $this->resolveIntervalCount($plan?->duration);
        $nextBillingDate = $this->computeNextBillingDate($startDate, $interval, $intervalCount);

        // Create Stripe Subscription when price is available and not already created
        if ($plan && $plan->stripe_price_id) {
            $existingSubId = RecurringSubscription::where('draft_id', $draft->id)->value('stripe_subscription_id');
            if (!$existingSubId) {
                try {
                    $subscription = $stripe->createSubscriptionWithPrice(
                        $stripeCustomerId,
                        $plan->stripe_price_id,
                        $paymentMethodId,
                        [
                            'booking_reference' => $draft->booking_reference,
                            'draft_id' => (string) $draft->id,
                            'vendor_id' => (string) $draft->vendor_id,
                            'client_id' => (string) $draft->client_id,
                        ],
                        $draft->booking_reference . '-subscription'
                    );
                    $stripeSubscriptionId = $subscription->id;
                    $latestInvoiceId = $subscription->latest_invoice ?? null;
                    if (is_object($latestInvoiceId) && isset($latestInvoiceId->id)) {
                        $latestInvoiceId = $latestInvoiceId->id;
                    }
                } catch (ApiErrorException $e) {
                    return response()->json([
                        'error' => 'Subscription creation failed: ' . $e->getMessage(),
                        'stripe_code' => $e->getStripeCode(),
                        'status' => $e->getHttpStatus(),
                    ], 400);
                }
            } else {
                $stripeSubscriptionId = $existingSubId;
            }
        }

        DB::transaction(function () use (
            $draft,
            $intentType,
            $intent,
            $paymentMethodId,
            $cardDetails,
            $stripeCustomerId,
            $startDate,
            $interval,
            $intervalCount,
            $nextBillingDate,
            $stripeSubscriptionId,
            $latestInvoiceId
        ) {
            $draft->update([
                'status' => 'paid',
                'stripe_customer_id' => $stripeCustomerId,
                'stripe_payment_intent_id' => $intentType === 'payment_intent' ? $intent->id : null,
                'stripe_setup_intent_id' => $intentType === 'setup_intent' ? $intent->id : null,
                'stripe_payment_method_id' => $paymentMethodId,
                'card_brand' => $cardDetails->brand ?? null,
                'card_last4' => $cardDetails->last4 ?? null,
            ]);

            // Optionally persist payment method
            if ($paymentMethodId && $draft->client_id && $cardDetails) {
                $stripeCustomerLocalId = StripeCustomer::where('client_id', $draft->client_id)
                    ->where('vendor_id', $draft->vendor_id)
                    ->value('id');

                StripePaymentMethod::updateOrCreate(
                    ['payment_method_id' => $paymentMethodId],
                    [
                        'client_id' => $draft->client_id,
                        'stripe_customer_id' => $stripeCustomerLocalId,
                        'card_brand' => $cardDetails->brand ?? null,
                        'card_last4' => $cardDetails->last4 ?? null,
                        'exp_month' => $cardDetails->exp_month ?? null,
                        'exp_year' => $cardDetails->exp_year ?? null,
                        'funding' => $cardDetails->funding ?? null,
                        'country' => $cardDetails->country ?? null,
                        'is_default' => true,
                        'is_active' => true,
                        'is_deleted' => false,
                        'status' => 'active',
                    ]
                );
            }

            RecurringSubscription::updateOrCreate(
                ['draft_id' => $draft->id],
                [
                    'vendor_id' => $draft->vendor_id,
                    'client_id' => $draft->client_id,
                    'package_id' => $draft->package_id,
                    'plan_id' => $draft->plan_id,
                    'stripe_customer_id' => $stripeCustomerId,
                    'stripe_payment_method_id' => $paymentMethodId,
                    'stripe_subscription_id' => $stripeSubscriptionId,
                    'start_date' => $startDate,
                    'next_billing_date' => $nextBillingDate,
                    'billing_interval' => $interval,
                    'interval_count' => $intervalCount,
                    'status' => 'active',
                    'metadata' => [
                        'booking_reference' => $draft->booking_reference,
                        'intent_type' => $intentType,
                        'stripe_intent_id' => $intent->id,
                        'amount_cents' => $intent->amount_received ?? $intent->amount ?? $draft->amount_cents,
                        'currency' => $intent->currency ?? $draft->currency,
                        'schedule_date' => $draft->metadata['schedule_date'] ?? null,
                        'schedule_time' => $draft->metadata['schedule_time'] ?? null,
                    ],
                ]
            );

            // Record initial payment when using payment_intent
            if ($intentType === 'payment_intent') {
                $subId = RecurringSubscription::where('draft_id', $draft->id)->value('id');
                RecurringPayment::updateOrCreate(
                    ['stripe_payment_intent_id' => $intent->id],
                    [
                        'recurring_subscription_id' => $subId,
                        'stripe_invoice_id' => is_object($latestInvoiceId) && isset($latestInvoiceId->id)
                            ? $latestInvoiceId->id
                            : $latestInvoiceId,
                        'stripe_payment_method_id' => $paymentMethodId,
                        'amount_cents' => $intent->amount_received ?? $intent->amount ?? null,
                        'currency' => $intent->currency ?? null,
                        'status' => $this->mapIntentStatus($intent->status),
                        'card_brand' => $cardDetails->brand ?? null,
                        'card_last4' => $cardDetails->last4 ?? null,
                        'exp_month' => $cardDetails->exp_month ?? null,
                        'exp_year' => $cardDetails->exp_year ?? null,
                        'paid_at' => Carbon::now(),
                        'processed_at' => Carbon::now(),
                        'raw' => $intent,
                    ]
                );
            }
        });

        $subscription = RecurringSubscription::where('draft_id', $draft->id)->first();

        return response()->json([
            'success' => true,
            'status' => 'paid',
            'bookingReference' => $draft->booking_reference,
            'stripePaymentIntentId' => $draft->stripe_payment_intent_id,
            'stripeSetupIntentId' => $draft->stripe_setup_intent_id,
            'stripePaymentMethodId' => $draft->stripe_payment_method_id,
            'stripeSubscriptionId' => $subscription?->stripe_subscription_id,
            'card' => [
                'brand' => $draft->card_brand,
                'last4' => $draft->card_last4,
            ],
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'stripeSubscriptionId' => $subscription->stripe_subscription_id,
                'startDate' => optional($subscription->start_date)->toDateString(),
                'nextBillingDate' => optional($subscription->next_billing_date)->toDateString(),
                'billingInterval' => $subscription->billing_interval,
                'intervalCount' => $subscription->interval_count,
                'status' => $subscription->status,
            ] : null,
        ]);
    }

    private function resolveInterval(?string $typeName): string
    {
        $interval = strtolower($typeName ?? '');
        return in_array($interval, ['day', 'week', 'month', 'year']) ? $interval : 'month';
    }

    private function resolveIntervalCount($duration): int
    {
        return (int) ($duration ?: 1);
    }

    private function computeNextBillingDate(string $startDate, string $interval, int $count): string
    {
        $date = Carbon::parse($startDate);
        return match ($interval) {
            'day' => $date->addDays($count)->toDateString(),
            'week' => $date->addWeeks($count)->toDateString(),
            'year' => $date->addYears($count)->toDateString(),
            default => $date->addMonths($count)->toDateString(),
        };
    }

    private function mapIntentStatus(?string $status): string
    {
        return match ($status) {
            'succeeded' => 'succeeded',
            'processing' => 'processing',
            'requires_action', 'requires_payment_method' => 'requires_action',
            'canceled' => 'canceled',
            default => 'pending',
        };
    }

    public function chargeSavedCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_code' => ['required', 'exists:vendors,vendor_code'],
            'client_email' => ['required', 'email'],
            'payment_method_id' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.5'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $vendor = VendorModel::where('vendor_code', $data['vendor_code'])->firstOrFail();
        $client = ClientModel::where('email', $data['client_email'])->firstOrFail();
        $amountCents = (int) round($data['amount'] * 100);
        $currency = $data['currency'] ?? 'usd';

        $stripe = new StripeService($vendor);
        $intent = $stripe->chargeSavedPaymentMethod(
            $client,
            $vendor,
            $data['payment_method_id'],
            $amountCents,
            $currency,
            ['purpose' => 'recurring_charge']
        );

        return response()->json([
            'success' => true,
            'status' => $intent->status,
            'paymentIntentId' => $intent->id,
            'clientSecret' => $intent->client_secret ?? null,
        ]);
    }
}
