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
use Stripe\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Systha\Core\Http\Controllers\BaseController;

class StripeWebhookController extends BaseController
{
    public function handle(Request $request)
    {
        // $endpoint_secret = config('services.stripe.webhook_secret'); // from env

        // $endpoint_secret = "whsec_Wvsl1hD2UNoqKCV9nTqKa6ido1G3mVjA";
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        // Log the raw payload safely
        Log::info("Stripe webhook payload", ['payload' => $request->getContent()]);

        $payload = $request->getContent();
        $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');

        
        Log::info("request type".$request->type);
       

        $eventType = $request->type;
        // Handle event types
        switch ($request->type) {
            case 'invoice.payment_succeeded':
                // $invoice = $payload->data->object;
                Log::info('✅ Invoice payment succeeded', ['success payload' => $payload]);
                // return response()->json(["data" => $invoice], 200);

            case 'invoice.payment_failed':
                // $invoice = $payload->data->object;
                Log::warning('❌ Invoice payment failed', ['failed payload' => $payload]);
                // return response()->json(["data" => $invoice], 200);

            default:
                Log::info('Unhandled Stripe event type', ['type' => $eventType,"payload" => $payload]);
        }

        return response('Webhook handled', 200);
    }
}
