<?php
namespace Systha\Core\Http\Controllers\Dashboard;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Systha\Core\Models\Quote;
use Illuminate\Support\Facades\Hash;
use Systha\Core\Models\Client;
use Systha\Core\Models\Vendor;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Schema;
use Systha\pesttemp\Event\SendMessage;
use Systha\Core\Models\QuoteEnq;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Systha\Core\Models\Appointment;
use Systha\Core\Models\InvoiceHead;
use Systha\Core\Models\Notification;
use Systha\Core\Helpers\NumberHelper;
use Systha\Core\Models\VendorTemplate;
use Systha\Core\Models\ServiceProvider;
use Systha\Core\Models\ChatConversation;
use Systha\Core\Services\MessageService;
use Systha\Core\Lib\Subscription\StripeSub;
use Systha\Core\Models\PackageSubscription;
use Systha\Core\Http\Controllers\Payment\PaymentController;

class DashboardController extends PaymentController
{
    protected $template;
    protected $vendor;
    protected $menus;
    protected $menu;
    protected $service;
    protected $messageService;
    protected $viewPath;
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        $h = request()->getHttpHost();
        $host = $h;
        if (strpos($h, 'www.') !== false) {
            $indexof = strpos($h, 'www.') + 4;
            $host = substr($h, $indexof, strlen($h) - 1);
        }

        $hostColumn = Schema::hasColumn((new VendorTemplate)->getTable(), 'template_host') ? 'template_host' : 'host';
        $temp = VendorTemplate::where($hostColumn, $host)->where('is_active', 1)->where('is_deleted', 0)->first();
        if (!$temp) {
            return redirect('/admin');
        }

        $this->viewPath = $temp->template_location;
        $this->template = $temp;
        $this->vendor = $temp->vendor;
        $this->vendor->address;
        $this->vendor->contact;
        $this->menus = $temp->menus()->orderBy('seq_no')->get();

        // dd($this->viewPath);
    }


    // public function inspection(Request $request){

    //     try {
    //     //
    //     } catch (\Throwable $th) {
    //         return response(["error"=>$th->getMessage()],422);
    //     }
    // }
    public function dashboardItem($section)
    {
        $profile = auth('webContact')->user();
        //dd($profile->owner);
        $address = $profile->owner->address;
        //dd($address);
        $items = [];
        switch ($section) {
            case 'schedules':
                // dd(auth('webContact')->user()->table_id);
                $items = Appointment::where('client_id', auth('webContact')->user()->table_id)
                    ->whereDate("start_date", date('Y-m-d'))
                    ->with('address', 'invoice', 'subscription.payment')
                    ->orderByDesc('created_at')
                    ->get();
                // dd("here");
                // dd($items);
                // return response(["data"=>$items],200);
                break;
            case 'estimates':
                $items = QuoteEnq::where('client_id', auth('webContact')->user()->table_id)
                    //->where('status','new')
                    ->with('address', 'quotes')
                    ->orderByDesc('created_at')
                    ->get();
                break;
            case 'subscription':
                $items = PackageSubscription::where('client_id', auth('webContact')->user()->table_id)
                    ->orderByDesc('created_at')
                    ->with('package', 'packageType')->get();
                break;
            case 'notification':
                $items = Notification::where(['table_id' => auth('webContact')->user()->table_id, "table" => "clients"])
                    ->orderByDesc('created_at')
                    ->get();
                break;
            case 'billings':
                // $items = InvoiceHead::leftJoin('payments','payments.appointment_id','invoice_heads.appointment_id')
                // ->select('payments.payment_type','payments.gateway','payments.cr_last4','payments.cr_exp_month','payments.cr_exp_year','invoice_heads.amount','payments.transaction_id',
                // 'invoice_heads.invoice_no','payments.id','payments.created_at','invoice_heads.id as id')
                // ->where('invoice_heads.client_id',$profile->table_id)
                // ->get();
                $items = InvoiceHead::where([
                    "client_id" => auth('webContact')->user()->table_id
                ])
                ->orderByDesc('created_at')
                ->get();


                // $items = [];
                break;
            case 'message':
                $items = ChatConversation::where([
                    'is_deleted' => 0,
                    'client_id' => auth('webContact')->user()->table_id
                ])
                    ->with('lastMessage')
                    ->get();
                break;
            case 'files':
                // $items = ChatConversation::where([
                //     'is_deleted' => 0,
                //     'client_id' => auth('webContact')->user()->table_id
                // ])
                //     ->with('lastMessage')
                //     ->get();
                $items = [];
                break;
            case 'settings':
                break;

        }

        return view($this->viewPath.'::frontend.dashboard.' . $section . ".index", compact('items', 'profile', 'address'));
    }
    public function dashboardAppoinment(Request $request)
    {
        $items = Appointment::where('client_id', auth('webContact')->user()->table_id)
            ->when($request->type, function ($query, $type) {
                $today = date('Y-m-d'); // Get the current date in 'Y-m-d' format
                $tomorrow = date('Y-m-d', strtotime('+1 day', strtotime($today))); //
                if ($type == "today") {
                    $query->where(["start_date" => $today]);
                } else if ($type == "tomorrow") {
                    $query->where(["start_date" => $tomorrow]);
                } else if ($type == "upcoming") {
                    $query->where("start_date", '>', $tomorrow);
                } else if ($type == "archived") {
                    $query->where("start_date", '<', $today);
                }
            })
            ->with('client', 'address', 'services')
            ->get();
        return response(["data" => $items], 200);
    }
    public function dashboardEstimates(Request $request)
    {

        $items = QuoteEnq::where('client_id', auth('webContact')->user()->table_id)
            // ->where('status',$request->type)
            ->with('address', 'client')
            ->orderByDesc('created_at')
            ->get();
        // ->when($request->status,function($query, $status) {
        //     // $today = date('Y-m-d'); // Get the current date in 'Y-m-d' format
        //     // $tomorrow = date('Y-m-d', strtotime('+1 day', strtotime($today))); //
        //     // if($type == "today"){
        //     //     $query->where(["start_date" => $today]);
        //     // }else if($type=="tomorrow"){
        //     //     $query->where(["start_date" => $tomorrow]);
        //     // }else if($type=="upcoming"){
        //     //     $query->where("start_date", '>',$tomorrow);
        //     // }else if($type=="archived"){
        //     //     $query->where("start_date",'<', $today);
        //     // }
        // })
        // ->get();
        return response(["data" => $items], 200);
    }

    public function dashboardAppoinmentDetail(Request $request, $id)
    {
        $appointment = Appointment::with([
            'client',
            'services',
            'address',
            'payment',
            'reviews.reviewer', // Eager load reviews and their reviewable relation
        ])->find($id);
        return view($this->viewPath.'::frontend.dashboard.schedules.schedule-detail', compact('appointment'));
    }
    public function dashboardAppoinmentPaymentForm(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        $appointment->client;
        $appointment->services;
        $appointment->address;
        $appointment->payment;
        return view($this->viewPath.'::frontend.dashboard.schedules.schedule-payment', compact('appointment'));
    }
    public function dashboardAppoinmentReshedule(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        $appointment->client;
        $appointment->services;
        $appointment->address;
        $appointment->payment;
        return view($this->viewPath.'::frontend.dashboard.schedules.schedule-reshedule', compact('appointment'));
    }
    public function dashboardAppoinmentResheduleRequest(Request $request)
    {
        try {
            $request->validate([
                "appointment_id" => "required",
                "preferred_date" => "required",
                "preferred_time" => "required",
                "reschedule_reason" => "required",
            ]);
            $appointment = Appointment::find($request->appointment_id);
            $appointment->preferred_date = $request->preferred_date;
            $appointment->preferred_time = $request->preferred_time;
            $appointment->reschedule_reason = $request->reschedule_reason;
            $appointment->reschedule_request = 1;
            $appointment->reschedule_request_date = now();
            $appointment->update();

            $message = "<div>
            <p style='margin-bottom:0;'>A rescheduling request has been made for your appointment.</p>
                <p class='appointment' data-id='" . e($appointment->id) . "'>Appointment #" . e($appointment->appointment_no) . " has been requested to be rescheduled. Click here to view the updated appointment details.</p>
            </div>";
            $this->messageService->sendMessage($appointment, $appointment->client, $appointment->vendor, $message);
            // return response(["message"=>"Request send successfully"],200);
            return $this->dashboardAppoinmentDetail($request, $appointment->id);

        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage(). " line: ".$th->getFile()], 422);
        }
        // return view($this->viewPath.'::frontend.dashboard.schedule-reshedule',compact('appointment'));
    }

    public function dashboardAppoinmentReviewForm(Request $request, $id)
    {
        try {
            //code...
            $appointment = Appointment::find($id);
            $appointment->client;
            $appointment->services;
            $appointment->address;
            $appointment->payment;
            return view($this->viewPath.'::frontend.dashboard.schedules.schedule-review-form', compact('appointment'));

        } catch (\Throwable $th) {
            //throw $th;
            return response(["error" => $th->getMessage()], 422);
        }
        // return view($this->viewPath.'::frontend.dashboard.schedule-reshedule',compact('appointment'));
    }
    public function dashboardAppoinmentReview(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'appointment_id' => 'required|integer|exists:appointments,id', // Ensure appointment exists
            'review' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'attachments.*' => 'nullable|file|max:2048', // Optional file, max size 2MB
        ], [
            'title.required' => 'Please provide a title for your review.',
            'appointment_id.required' => 'The appointment ID is required.',
            'appointment_id.exists' => 'The selected appointment ID is invalid.',
            'review.required' => 'A review description is required.',
            'rating.required' => 'Please provide a rating.',
            'rating.integer' => 'The rating must be a valid integer.',
            'rating.min' => 'The rating must be at least 1.',
            'rating.max' => 'The rating can’t be more than 5.',
            'attachments.*.file' => 'The attachment must be a valid file.',
            'attachments.*.max' => 'Each attachment must not exceed 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {

            $appointment = Appointment::find($request->appointment_id);

            $review = $appointment->review()->create([
                "review" => $request->review,
                "title" => $request->title,
                'stars' => $request->rating,
                'reviewer_table_name' => auth('webContact')->user()->table_name,
                'reviewer_table_id' => auth('webContact')->user()->table_id,
            ]);

            // Set review ID after creation
            $reviewId = $review->id;
            $appointmentId = $appointment->id;

            $attachmentPaths = [];

            if ($request->has('attachments')) {

                foreach ($request->file('attachments') as $file) {
                    // Get original file name and replace spaces with dashes
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $formattedName = str_replace(' ', '-', $originalName) . '-' . now()->format('Ymd-His') . '.' . $file->getClientOriginalExtension();

                    // Directory path
                    $directoryPath = 'public/appointments/' . $appointmentId . '/reviews/' . $reviewId;

                    // Check if the directory exists and create it if it doesn't
                    if (!Storage::exists($directoryPath)) {
                        // Create the directory and set permissions
                        Storage::makeDirectory($directoryPath, 0775, true); // Permissions set to 0775 (rwxrwxr-x)
                    }

                    // Check if the file is an image
                    if (in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                        // Convert image to webp format using Intervention Image
                        $image = Image::make($file);
                        $webpFilename = pathinfo($formattedName, PATHINFO_FILENAME) . '.webp';
                        $imageContents = $image->encode('webp', 100); // 100 is the quality of the .webp image

                        // Store the image as .webp
                        Storage::put($directoryPath . '/' . $webpFilename, $imageContents);

                        // Add the .webp path to the attachment paths array
                        $attachmentPaths[] = $directoryPath . '/' . $webpFilename;
                    } else {
                        // For non-image files, use 'put' to store them
                        $fileContents = file_get_contents($file->getRealPath());
                        $path = Storage::put($directoryPath . '/' . $formattedName, $fileContents);

                        // Add file path to the array
                        $attachmentPaths[] = $path;
                    }
                }


            }
            if (!empty($attachmentPaths)) {
                foreach ($attachmentPaths as $path) {
                    $review->images()->create(['file_name' => $path]);
                }
            }

            DB::commit();

            $reviewHTML = view("buglogicpc::frontend.dashboard.schedules._reviews", compact('appointment'))->render();
            // dd($reviewHTML);
            return response(["data" => $review, "reviewHTML" => $reviewHTML], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response(["error" => $th->getMessage()], 422);
        }
    }

    public function dashboardAppoinmentPayment(Request $request)
    {
        DB::beginTransaction();
        try {

            $appointment = Appointment::find($request->appointmentId);
            $paymentData = $this->mapPaymentData($request, $appointment);

            $client = \Systha\Core\Models\Client::find($appointment->client_id);
            // dd($paymentData);
            $paymentDetails = $this->withClient($client)->withRequest($request)->initStripePayment($paymentData);

            $this->storePaymentByStripe($request, $paymentDetails, $appointment);

            DB::commit();
            return response(["message" => "Payment Completed Successfully", $paymentDetails], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(["error" => $th->getMessage(), "line" => $th->getLine(), "file" => $th->getFile()], 422);
            //throw $th;
        }
    }
    protected function storePaymentByStripe(Request $request, $paymentDetails, Appointment $appointment)
    {
        $month = $paymentDetails['payment_method_details']['card']['exp_month'];
        $month = $month < 10 ? "0$month" : $month;
        $paymentData = [
            // 'order_id' => $order->id,
            'appointment_id' => $appointment->id,
            'table_id' => $appointment->id,
            'table_name' => 'appointments',
            'payment_type' => $paymentDetails['payment_method_details']['card']['brand'],
            'gateway' => "Stripe",
            'cr_last4' => $paymentDetails['payment_method_details']['card']['last4'],
            'cr_exp_month' => $month,
            'cr_exp_year' => $request['expm'],
            'transaction_id' => $paymentDetails['id'],
            'billing_zip_code' => $request->delivery_zip,
            'card_last_name' => $appointment->client->lname,
            'amount' => $paymentDetails['amount'] / 100,
            'ref_no' => $paymentDetails['id'],
        ];

        $appointment->payment()->create($paymentData);
        // return Payment::create($paymentData);
    }

    protected function mapPaymentData(Request $request, $appointment): array
    {
        return array_merge(
            [
                "first_name" => $appointment->client->fname,
                "last_name" => $appointment->client->lname,
                "name_per_card" => $appointment->client->fname . " " . $appointment->client->lname,
                "email" => $appointment->client->contact->email,
                "gateway" => $request->gateway ?: '-',
                "nonce" => $request->nonce ?: '-',
                "stripeToken" => $request->stripeToken ?: '-',
                "country" => "USA",
                "amount" => $appointment->total_service,
                "inv_number" => $appointment->appointment_no,
                "des" => "Payment",
                "vendor" => $appointment->vendor,
            ],
            ["card" => str_replace(" ", "", $request->card)],
            $request->only([
                'code',
                'expy',
                'expm',
                'zip',
                'email',
                'name_per_card'
            ])
        );
    }


    public function dashboardEstimateDetail(Request $request, $id)
    {
        $enq = QuoteEnq::find($id);
        $enq->enqServices;
        $enq->address;
        $enq->quotes;
        // dd($enq->quotes);
        $quotes = $enq->quotes()->where('status', 'confirmed')->get();
        if (count($quotes)) {
            $enq['quotes'] = $quotes;
        }
        // dd($enq->quotes);
        // if($quote) {
        //     $service = $enq->enqServices()->filter(function($service,$quote){
        //         return $service->s
        //     })
        // }
        return view($this->viewPath.'::frontend.dashboard.estimates.estimate-detail', compact('enq'));
    }
    public function dashboardQuotationDetail(Request $request, $id)
    {
        $quote = Quote::find($id);
        $chatConversation = ChatConversation::where(["table_name" => "quotes", "table_id" => $id])->first();
        if (!$chatConversation) {
            $chatConversation = ChatConversation::create([
                "title" => $quote->quote_number,
                "table_name" => $quote->getTable(),
                "table_id" => $quote->id,
            ]);

            $chatConversation->members()->create([
                "table_name" => "vendors",
                "table_id" => $quote->vendor_id,
            ]);
            $chatConversation->members()->create([
                "table_name" => "clients",
                "table_id" => $quote->requested_id,
            ]);
        }
        $chatConversation->members;
        return view($this->viewPath.'::frontend.dashboard.quotation.quotation-detail', compact('quote', 'chatConversation'));
    }
    public function dashboardQuotationConfirmed(Request $request, $id)
    {
        $quote = Quote::find($id);
        return view($this->viewPath.'::frontend.dashboard.quotation.quotation-confirm', compact('quote'));
    }
    public function dashboardQuotationReject(Request $request, $id)
    {
        $quote = Quote::find($id);
        return view($this->viewPath.'::frontend.dashboard.quotation.quotation-reject', compact('quote'));
    }

    public function dashboardSubscriptionCancel(Request $request, $id)
    {
        $subscription = PackageSubscription::find($id);
        return view($this->viewPath.'::frontend.dashboard.subscription.subscription-cancel', compact('subscription'));
    }

    public function dashboardSubscriptionDetail(Request $request, $id)
    {
        $subscription = PackageSubscription::find($id);
        $subscription->payment;
        $subscription->packageType;
        $stripe = new StripeSub($subscription->vendor);
        $info = $stripe->retrieveSubscription($subscription->cart->subscription);
        return view($this->viewPath.'::frontend.dashboard.subscription.subscription-detail', compact('subscription', 'info'));
    }
    // public function dashboardBillingDetail(Request $request, $id){

    //     $invoice = InvoiceHead::find($id);
    //     $appointment->vendor->address;
    //     $appointment->vendor->contact;
    //     $appointment->client;
    //     $appointment->payment;
    //     // $appointment = Appointment::find($invoice->appointment->id);
    //     // $appointment->services;
    //     // $appointment->vendor->address;
    //     // $appointment->vendor->contact;
    //     // $appointment->client;
    //     return view($this->viewPath.'::frontend.dashboard.billings.billing-detail',compact('appointment','invoice'));
    // }
    public function dashboardBillingDetail(Request $request, $id)
    {
        $invoice = InvoiceHead::with([
            'appointment.vendor.address',
            'appointment.vendor.contact',
            'appointment.client',
            'appointment.payment'
        ])->find($id);

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Check if invoicable is an Appointment and load 'services' relation
        if ($invoice->invoicable instanceof Appointment) {
            $invoice->load('invoicable.services');
        }
        if ($invoice->invoicable instanceof PackageSubscription) {
            $invoice->load('invoicable.package');
            $invoice->load('invoicable.packageType');
        }

        // dd($invoice->invoicable->package);

        return view($this->viewPath.'::frontend.dashboard.billings.invoice-modal', compact('invoice'));
    }


    public function dashboardBillingPayment(Request $request, $id)
    {

        $invoice = InvoiceHead::find($id);

        return view($this->viewPath.'::frontend.dashboard.billings.billing-payment', compact('invoice'));
    }

    public function dashboardBilling(Request $request)
    {
        DB::beginTransaction();
        try {

            $invoice = InvoiceHead::find($request->invId);
            $invoice["price"] = $invoice->amount;
            $paymentData = $this->mapPaymentData($request, $invoice);


            // dd($paymentData);
            $client = \Systha\Core\Models\Client::find($invoice->client_id);

            $paymentDetails = $this->withClient($client)->withRequest($request)->initStripePayment($paymentData);

            $appointment = Appointment::find($invoice->appointment_id);
            $this->storePaymentByStripe($request, $paymentDetails, $appointment);

            DB::commit();
            return response(["message" => "Payment Completed Successfully"], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(["error" => $th->getMessage()], 422);
            //throw $th;
        }
    }


    public function dashboardUpdateProfile(Request $request)
    {
        try {
            $request->validate([
                "fname" => "required",
                "lname" => "required",
                "email" => "required",
                "phone" => "required",
            ]);

            $auth = auth('webContact')->user();

            DB::beginTransaction();
            // $client = Client::where($auth->table_id);
            $auth->fname = $request->fname;
            $auth->lname = $request->lname;
            $auth->email = $request->email;
            $auth->phone_no = $request->phone;
            $auth->update();
            $auth->owner()->update([
                "fname" => $request->fname,
                "lname" => $request->lname,
            ]);

            DB::commit();
            return response(["message" => "Profiled Updated"], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(["error" => $th->getMessage()], 422);
            //throw $th;
        }
    }

    public function dashboardUpdateAddress(Request $request)
    {
        try {
            $request->validate([
                "add1" => "required",
                "city" => "required",
                "state" => "required",
                "zip" => "required",
            ]);

            $auth = auth('webContact')->user();
            DB::beginTransaction();
            // $client = Client::where($auth->table_id);
            // if($request->id){
            //     $address = Address::find($request->id);
            // }
            if ($auth->owner->address) {
                $address = $auth->owner->address;
                $address->add1 = $request->add1;
                $address->city = $request->city;
                $address->state = $request->state;
                $address->zip = $request->zipe;
                $address->update();
            } else {
                $auth->owner->address()->create([
                    'add1' => $request->add1 ?? null,
                    'add2' => $request->add2 ?? null,
                    'city' => $request->city ?? null,
                    'state' => $request->state ?? null,
                    'zip' => $request->zip ?? null
                ]);
            }



            DB::commit();
            return response(["message" => "Address Updated"], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(["error" => $th->getMessage()], 422);
            //throw $th;
        }
    }
    public function dashboardUpdatePassword(Request $request)
    {
        try {
            $request->validate([
                "password" => "required",
            ]);

            $auth = auth('webContact')->user();

            DB::beginTransaction();
            $password = base64_decode($request->password);
            $auth = auth('webContact')->user();
            $auth->password = Hash::make($password);
            $auth->update();

            DB::commit();
            return response(["message" => "Password Updated"], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response(["error" => $th->getMessage()], 422);
        }
    }

    public function dashboardMessages(Request $request, $id)
    {
        try {
            $conv = ChatConversation::find($id);
            $conv->messages;
            foreach ($conv->messages as $message) {
                $tableFrom = "";
                switch ($message->table_from) {
                    case 'vendors':
                        $tableFrom = Vendor::find($message->table_from_id);
                        break;
                    case 'clients':
                        $tableFrom = Client::find($message->table_from_id);
                        break;
                    case 'service_providers':
                        $tableFrom = ServiceProvider::find($message->table_from_id);
                        break;
                }
                $message["from"] = $tableFrom;
            }
            $msgTo = null;
            switch ($conv->table_name) {
                case 'appointment':
                case 'appointments':
                    $app = Appointment::find($conv->table_id);
                    $msgTo = $app->vendor_id;
                    break;
                case 'quotes':
                    $quote = Quote::find($conv->table_id);
                    $msgTo = $quote->vendor_id;
                    break;
            }

            $user = auth('webContact')->user();
            return view("buglogicpc::frontend.dashboard.message.temp_conversation", compact('conv', 'user', 'msgTo'));
            // return response(["data"=>$conv,"user"=>$auth,"msg_to"=>$msgTo],200);
        } catch (\Throwable $th) {
            return response(["error" => $th->getMessage()], 422);
        }
    }

    public function dashboardSaveMessage(Request $request, ChatConversation $conversation)
    {
        try {
            //dd($request->all(),$conversation);
            $auth = auth('webContact')->user();
            //dd($auth->table,$auth->table_id,$auth);
            $conversation->messages()->create([
                'table_from' => $auth->table,
                'table_from_id' => $auth->table_id,
                'message' => $request->message,
                'title' => $conversation->title,
                'is_sent' => 1
            ]);
            broadcast(new SendMessage($conversation, $request->message));
            // ChatConversationMessageSaved::dispatch($conversation);
            //event(new ChatConversationMessageSaved($conversation));
            return response()->json(['message' => 'Message updated Sucessfully'], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }
    public function sendMessage(Request $request)
    {
        $request->validate([
            "conversation_id" => "required",
            "table_from" => "required",
            "table_from_id" => "required",
            "table_to" => "required",
            "table_to_id" => "required",
            "message" => "required",
        ]);

        try {
            $chatConv = ChatConversation::find($request->conversation_id);
            if ($chatConv) {

                $message = $chatConv->messages()->create([
                    "table_from" => $request->table_from,
                    "table_from_id" => $request->table_from_id,
                    "table_to" => $request->table_to,
                    "table_to_id" => $request->table_to_id,
                    "message" => $request->message,
                    'seen_client' => ($this->user['table_name'] == 'clients') ? 1 : 0,
                    'seen_vendor' => ($this->user['table_name'] == 'vendors') ? 1 : 0,
                ]);
                broadcast(new SendMessage($chatConv, $message));
                return response(["message" => "Message Sent Successfully", 'data' => $message], 200);
            } else {
                return response(["error" => "No conversation found"], 422);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 422);
        }
    }

    public function recentServices(Request $request)
    {
        $appointments = Appointment::where('client_id', auth('webContact')->user()->table_id)
            ->with('client', 'technician', 'invoice')
            ->paginate(1);
        // dd($appointments);
        return response(["data" => $appointments], 200);
    }
}

