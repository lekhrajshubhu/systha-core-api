<?php

namespace Systha\Core\Http\Controllers\ServicePackage;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Systha\Core\Models\Client;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Package;
use Systha\Core\Models\PackageType;
// use Systha\Core\Lib\Subscription\Stripe;
use Systha\Core\Models\ChatConversation;
use Systha\Core\Models\SubscriptionCart;
// use Systha\Core\Lib\Subscription\StripeSub;
use Systha\Core\Services\MessageService;
use Systha\Core\Lib\Subscription\StripeSub;
use Systha\Core\Models\PackageSubscription;
use Systha\Core\Models\SubscriptionPayment;
use Systha\Core\Services\SubscriptionService;
use Systha\Core\Http\Controllers\BaseController;


class PackageSubscriptionController extends BaseController
{
    protected $stripe = null;
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|integer|exists:package_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:10|max:20',
            'add1' => 'required|string|max:255',
            'add2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:10',
            'stripe_token' => 'required|string',
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            // 'card_brand' => 'required|string',
            // 'card_last4' => 'required|digits:4',
            // 'card_exp_month' => 'required|integer|between:1,12',
            // 'card_exp_year' => 'required|integer|min:' . date('Y'),
        ], [
            'plan.required' => 'The plan selection is required.',
            'plan.exists' => 'The selected plan is invalid.',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'The start_date must be a valid date.',
            'start_date.after_or_equal' => 'The start date cannot be in the past.',
            'fname.required' => 'First name is required.',
            'lname.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => 'Phone number is required.',
            'phone.min' => 'Phone number must be at least 10 characters.',
            'add1.required' => 'Address Line 1 is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'zip.required' => 'Zip code is required.',
            'stripe_token.required' => 'Stripe payment token is required.',
            // 'card_brand.required' => 'Card brand is required.',
            // 'card_last4.required' => 'Last 4 digits of the card are required.',
            // 'card_last4.digits' => 'Last 4 digits must be exactly 4 numbers.',
            // 'card_exp_month.required' => 'Expiration month is required.',
            // 'card_exp_month.between' => 'Expiration month must be between 1 and 12.',
            // 'card_exp_year.required' => 'Expiration year is required.',
            // 'card_exp_year.min' => 'Expiration year must be the current year or later.',
        ]);



        $packageType = PackageType::find($request->plan);

        $package = Package::find($packageType->package_id);

        $vendor_id = $package->vendor_id;

        $start_date = $request->start_date;

        \DB::beginTransaction();
        try {
            $contact = null;
            $client_id = null;

            $client = Client::where('email', $request->email)->first();
            if (!$client) {
                $client = Client::create([
                    "fname" => $request->fname,
                    "lname" => $request->lname,
                    "email" => $request->email,
                    "phone_no" => $request->phone,
                    "state" => "publish",
                    "vendor_id" => $vendor_id,
                ]);
                $client_id = $client->id;
                $client->contact()->create([
                    "fname" => $request->fname,
                    "lname" => $request->lname,
                    "email" => $request->email,
                    "contact_type" => 'customer',
                    "phone_no" => $request->phone,
                    "mobile_no" => $request->phone,
                    "password" => bcrypt($request->password)
                ]);
                $client->address()->create([
                    "add1" => $request->add1,
                    "add2" => $request->add2 ?? '',
                    "city" => $request->city,
                    "state" => $request->state,
                    "zip" => $request->zip,
                    "address_type" => 'primary',
                ]);
            }

            $vendor = Vendor::find($package->vendor_id);

            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $stripeToken = $request->stripe_token;

            $startDate = $request->start_date;
            $startTime = now()->format('H:i:s');

            $stripe = new StripeSub($packageType->vendor); // Make sure this has required dependencies
            $messageService = new MessageService();
            $this->subscriptionService = new SubscriptionService($stripe, $messageService);
    
            $resp = $this->subscriptionService->subscribe($packageType, $client,$stripeToken,$startDate, $startTime);
            $packageSubscription = $resp["data"];
            DB::commit();
            return view($this->viewPath.'::components.template.package_subscription_success',compact('packageSubscription'));
        } catch (\Exception $e) {
            \DB::rollBack();
            return response(["message" => $e->getMessage(), 'f' => $e->getFile(), 'l' => $e->getLine()], 500);
        }
    }

    public function createStripePrice($subscription)
    {
        // createPackagePrice
        $stripe = new StripeSub();
        $sub = $stripe->createPackagePrice($subscription);
        return $sub;
    }

    public function sendSubscriptionMail($subscription)
    {
        try {
            $mail_id = $this->makeEmailLog($subscription);

            $id = $mail_id->id;
            $to = [];
            $cc = [];
            array_push($to, $subscription->client->email);

            $content = $mail_id->message;
            $mapper = new SubscriptionMapper($subscription, $content);
            $message = $mapper->mapper();
            $subject = $mail_id->subject;
            $this->send($to, $cc, $message, $subject, $id);

            return response()->json([
                "message" => "Email successfully sent.",
                "data" => "abc",
            ]);

        } catch (DecryptException $decryptException) {
            return response(["message" => "Password encoding cannot be decrypted. Please check encoded password"], 500);
        } catch (\Swift_TransportException $swift_TransportException) {
            return response(["message" => $swift_TransportException->getMessage()], 500);
        } catch (\Exception $e) {
            return response([
                "message" => $e->getMessage()
                ,
                "Line" => $e->getLine()
                ,
                "file" => $e->getFile()
            ], 500);
        }
    }

    protected function makeEmailLog($subscription)
    {
        $to = [];
        $cc = [];

        array_push($to, $subscription->client->email);

        $template = EmailTemplate::where('is_deleted', 0)->where('code', 'subscription_applied_email')->first();

        if (!$template) {
            $template = EmailTemplate::create([
                'code' => 'subscription_applied_email',
                'created_at' => '2021-06-23 21:44:36',
                'deleted_at' => NULL,
                'id' => 28,
                'is_active' => 1,
                'is_deleted' => 0,
                'section' => 'Subscription',
                'subject' => 'Subscription Subscribed',
                'table_id' => NULL,
                'table_name' => NULL,
                'temp_html' => '<p>Dear {client_name},</p><p>Package<b> {package_name}&nbsp;</b>has been subscribed successfully. further more information is given below.</p><p><br></p><table class="table table-bordered"><tbody><tr><td>Name</td><td><p>{client_name}</p></td></tr><tr><td>Phone</td><td>{client_phone}</td></tr><tr><td>Email</td><td>{client_email}</td></tr><tr><td>Package Name<br></td><td>{package_name}<br></td></tr><tr><td>Price($)</td><td>{price}</td></tr><tr><td>Frequency</td><td>{package_frequency}</td></tr><tr><td>Start date</td><td>{start_date}</td></tr><tr><td>End Date</td><td>{end_date}</td></tr></tbody></table><p><br></p><p>Thank You.</p>',
                'temp_instruction' => NULL,
                'temp_json' => 'client_name, package_name, package_frequency, start_date, end_date, price, client_email, client_phone',
                'temp_name' => 'Subscription Subscribed Email',
                'temp_type' => 'Subscription',
                'updated_at' => '2021-10-06 04:48:44',
                'userc_id' => 1,
                'userd_id' => NULL,
                'useru_id' => NULL,
                'vendor_id' => NULL,
            ]);
        }

        $emailLog = new EmailLog();
        $emailLog->table = 'package_subscriptions';
        $emailLog->table_id = $subscription->id;
        $emailLog->from = auth()->check() ? auth()->user()->email : '';
        $emailLog->to = json_encode($to);
        $emailLog->cc = json_encode($cc);
        $emailLog->subject = $template->subject;
        $emailLog->message = $template->temp_html;
        $emailLog->sent_status = 'Success';
        $emailLog->sent_date = date('Y-m-d');
        $emailLog->save();
        return $emailLog;
    }

    public function send($to, $cc, $message, $subject, $id)
    {
        $emailSetup = getDefault('email_setup');
        $data = explode(":", $emailSetup);

        if (count($data) >= 3) {
            // dd($configurations);
            $configurations['to'] = $to;
            $configurations['cc'] = $cc;
            $configurations['smtp_port'] = $data[1];
            $configurations['subject'] = $subject;
            $configurations['content_message'] = $message;
            $configurations['id'] = $id;
            $configurations['smtp_host'] = $data[0];
            $configurations['smtp_username'] = $data[2];
            $configurations['smtp_password'] = \Illuminate\Support\Facades\Crypt::encryptString($data[3]);
            if (isset($data[4])) {
                $configurations['smtp_encryption'] = $data[4];
            }
            $configurations['from_name'] = getDefault('company_email');
            $configurations['from_email'] = getDefault('company_email');

            app()->makeWith('sendMail.main-mailer', $configurations);
            // app('sendMail.mailer', $configurations)->send();
        } else {
            throw new \Exception("Please set up your own smtp host credentials first");
        }
    }

    private function createApiSubscriptionPayment(PackageSubscription $subscription, SubscriptionCart $cart, $card)
    {
        // $card = $this->stripe->retrieveCart($customer_id, $card_id);
        $invoice_number = str_pad($this->last_inv() + 1, 5, '0', STR_PAD_LEFT);
        // $card = $cart->cardInfo();
        // dd($card);
        // generate inv number from database
        $client = Client::find($subscription->client_id);
        $subscription["client"] = $client;
        return $subscription->payments()->save(new SubscriptionPayment([
            'gateway' => 'stripe',
            'cart_id' => $cart->id,
            'invoice_number' => $invoice_number,
            'payment_type' => "card",
            'cr_last_4' => $card->last4,
            'cr_exp_month' => $card->exp_month,
            'cr_exp_year' => $card->exp_year,
            'cr_cardholder_name' => $subscription->client->fullName,
            'cr_billing_zip' => $card->address_zip,
            'convenience_fee' => $subscription->convenience_fee,
            'price' => $subscription->price,
            'amount' => $subscription->price,
            'payment_id' => $cart->card_id,
            'description' => 'subscription payment',
            'is_active' => 1
        ]));

    }
    private function createSubscriptionPayment($request, PackageSubscription $subscription, SubscriptionCart $cart, $card)
    {
     
        // $invoice_number = str_pad($this->last_inv() + 1, 5, '0', STR_PAD_LEFT);
        $subscription->payments()->save(new SubscriptionPayment([
            'gateway' => 'stripe',
            'cart_id' => $cart->id,
            'payment_id' => $card->id,
            // 'invoice_number' => $invoice_number,
            'payment_type' => $request->card_brand,
            'cr_last_4' => $request->card_last4,
            'cr_exp_month' => $request->card_exp_month,
            'cr_exp_year' => $request->card_exp_year,
            'cr_cardholder_name' => $card->name ?? $subscription->client->fname . " " . $subscription->client->lname,
            'cr_billing_zip' => $card->address_zip,
            'convenience_fee' => $subscription->convenience_fee,
            'price' => $subscription->price,
            'amount' => $subscription->price,
            'description' => 'subscription payment',
            'is_active' => 1
        ]));
    }

    private function last_inv(): int
    {
        $inv = SubscriptionPayment::query()->latest('created_at')->value('invoice_number') ?: '0';
        $num = preg_replace('/[^\d]/', '', $inv);
        return is_numeric($num) ? (int) $num : 0;
    }


    public function createCustomer(array $data, $vendor)
    {
        $stripe = new StripeSub($vendor);
        return $stripe->createCustomer($data);
    }

    public function createNewCustomerCard($token, $customer_id, $vendor)
    {
        $stripe = new StripeSub($vendor);
        return $stripe->createCard($token, $customer_id);
    }


    public function createPlan($subscription)
    {
        $stripe = new StripeSub();
        $price = $stripe->createPlan($subscription);
        return $price->id;
    }


    public function delete(PackageSubscription $subscription)
    {
        $subscription->update([
            'is_deleted' => 1,
            'userd_id' => auth()->id(),
            'deleted_at' => now()
        ]);
        $cart = $subscription->cart;
        $subscription_id = $cart->subscription;
        $stripe = new StripeSub();
        $stripe->cancelSubscription($subscription_id);

        return response()->json([
            'data' => $subscription,
            'message' => 'Subscription Deleted Successfully.'
        ]);
    }

    public function cancel($packageSubscriptionId)
    {
        $packageSubscription = PackageSubscription::find($packageSubscriptionId);

        $cart = $packageSubscription->cart;
        $subscription_id = $cart->subscription;
        $vendor = Vendor::find($packageSubscription->vendor_id);
        $stripe = new StripeSub($vendor);
        $stripe->cancelSubscription($subscription_id);

        $packageSubscription->update([
            'is_cancelled' => 1,
            'is_active' => 0,
            'status' => 'cancelled',
        ]);

        return response()->json([
            'data' => $packageSubscription,
            'message' => 'Subscription Cancelled Successfully.'
        ]);
    }

    // Note
    public function storeNote(Request $request, PackageSubscription $subscription)
    {
        $note = Note::create([
            'title' => $request->title ?: '',
            'description' => $request->description,
            'table' => 'quot_enqs',
            // 'table_id' => $enq->id,
            'userc_id' => auth()->user()->id
        ]);
        $this->sendNoteAddedToClientNotification($note);

        $this->sendNoteAddedNotification($note);


        return view('Salon::quote-enq.inc._notes_data_template', compact('enq'));
    }

    // Note
    /**
     * Update CLient notes
     *
     * @param Request $request
     * @param integer $id
     * @return void
     */
    public function updateNote(Request $request, int $id)
    {
        $note = Note::find($id);
        $note->update([
            'description' => $request->description,
            'updated_at' => date('Y-m-d H:i:s'),
            'useru_id' => auth()->id() ?: 0,
        ]);
        $enq = QuoteEnq::find($note->table_id);
        return view('Salon::quote-enq.inc._notes_data_template', compact('enq'));
    }

    public function deleteNote(Note $note)
    {
        $note->update([
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
            'userd_id' => auth()->id() ?: 0,
        ]);
        $enq = QuoteEnq::find($note->table_id);
        return view('Salon::quote-enq.inc._notes_data_template', compact('enq'));
    }

    public function sendNoteAddedToClientNotification($note)
    {
        $template = EmailTemplate::where('code', 'admin_added_note_notification')
            ->where('is_deleted', 0)
            ->orderByDesc('updated_at')
            ->first();

        $quote_enq = QuoteEnq::find($note->table_id);

        $message = (new QuoteEnqNoteMapper($note))->mapper($template);

        $admin_notification = new Notification();
        $admin_notification->table = 'clients';
        $admin_notification->table_id = $quote_enq->client_id;
        $admin_notification->related_table = $quote_enq->getTable();
        $admin_notification->related_table_id = $quote_enq->id;
        $admin_notification->type = 'Notes';
        $admin_notification->content = $message;
        $admin_notification->icon = 'fa fa-bell';
        $admin_notification->url = '/admin/quote-enq/detail/' . $quote_enq->id;
        $admin_notification->is_sms = 0;
        $admin_notification->is_email = 0;
        $admin_notification->is_flash = 1;
        $admin_notification->userc_id = Auth::id();
        $admin_notification->save();
    }

    public function sendNoteAddedNotification(Note $note)
    {
        $quote_enq = QuoteEnq::find($note->table_id);

        NotifyFactory::notify($note, new QuoteEnqNoteMapper($note), [
            'url' => 'admin/quote-enq/view/' . $note->id,
            'code' => 'quote_enquiry_note_notification',
            'related_model' => $quote_enq,
        ]);
    }


    // public function editRemainderDate(Request $request, PackageSubscription $subscription)
    // {
    //     $remainder_date = Carbon::parse($request->date)->format('Y-m-d');

    //     $enq->update([
    //         'remainder_date' => $remainder_date
    //     ]);
    //     return response()->json([
    //         'data' => format_to_us_date($enq->remainder_date),
    //         'message' => 'Remainder Set Successfully.'
    //     ]);
    // }

    // public function changeAssignee(Request $request, PackageSubscription $subscription)
    // {
    //     $enq->update([
    //         'assigned_to' => $request->assigned_to
    //     ]);

    //     $this->sendAcknowledgeNotification($enq);

    //     return response()->json([
    //         'data' => $enq,
    //         'assignee' => $enq->assignee->fullName,
    //         'message' => 'Assignee Updated Successfully.'
    //     ]);
    // }

    public function sendAcknowledgeNotification(PackageSubscription $subscription)
    {
        NotifyFactory::notify($enq, new QuoteEnquiryMapper($enq), [
            'url' => 'admin/quote-enq/view/' . $enq->id,
            'code' => 'enquiry_acknowledge_notification',
            'related_model' => $enq,
        ]);
    }

    public function sendEnquiryReceivedNotification(PackageSubscription $subscription)
    {
        NotifyFactory::notify($enq, new QuoteEnquiryMapper($enq), [
            'url' => 'admin/quote-enq/view/' . $enq->id,
            'code' => 'quote_enquiry_notification',
            'related_model' => $enq,
        ]);
    }

    public function deleteService(QuoteEnqService $service)
    {
        $service->update([
            'is_deleted' => 1,
            'deleted_at' => now()
        ]);

        return response()->json([
            'data' => $service,
            'message' => 'Quote Service Deleted Successfully.'
        ]);
    }

    function stripeWebhook(Request $request)
    {
        // $type = $request->input('type');
        // if($type == 'invoice.payment_succeeded'){
        // $stripe_sub_id = $request->input('data.object.subscription');
        echo ($request->all());
        dd($request->all());
        $stripe_sub_id = 'sub_1NdFZsFCkDOH9dhPWiYsgmCP';
        $cart = SubscriptionCart::where('subscription', $stripe_sub_id)->where('is_deleted', 0)->first();

        if (!$subscription = PackageSubscription::find($cart->subscription_id)) {
            return response()->json(['status' => 'Subscription not found']);
        }

        $sub_quote = new SubscriptionQuote();
        $sub_quote->package_subscription_id = $cart->subscription_id;
        $sub_quote->client_id = $subscription->client_id;
        $sub_quote->vendor_id = $subscription->vendor_id;

        if ($cart->payment) {
            $sub_quote->subscription_payment_id = $cart->payment->id;
        }

        $sub_quote->save();
        // dd($subscription->package->packageServices);
        if (count($subscription->package->packageServices)) {
            foreach ($subscription->package->packageServices as $service) {

                $sub_quote_service = new SubscriptionQuoteService();
                $sub_quote_service->service_id = $service->service_id;
                $sub_quote_service->quote_id = $sub_quote->id;
                $sub_quote_service->save();
            }
        }
        return response()->json([
            'data' => $sub_quote,
            'message' => 'Quote Added Successfully.'
        ]);
        // }

    }
}
