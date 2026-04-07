<?php

namespace Systha\Core\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Systha\Core\Helpers\Formatter;
use Systha\Core\Models\Appointment;
use Systha\Core\Models\AppointmentService;
use Systha\Core\Models\AppointmentTax;
use Systha\Core\Models\EmailTemplate;
use Systha\Core\Models\InvoiceItem;
use Systha\Core\Models\Payment;
use Systha\Core\Models\ProviderTimeslot;
use Systha\Core\Models\TaxMaster;
use Systha\Core\Services\MessageService;

class AppointmentServiceContainer
{
    /**
     * Create a complete appointment with services, taxes, invoice, etc.
     *
     * @param array $data
     * @return Appointment
     * @throws \Throwable
     */
    public function storeAppointment(array $data)
    {

        $validated = Validator::make($data, [
            'client_id' => 'required|exists:clients,id',
            'vendor_id' => 'required|exists:vendors,id',
            'address_id' => 'required|exists:addresses,id',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|string',
            'service_list' => 'required|array',
            'timeslot_id' => 'nullable|exists:provider_timeslots,id',
            'plan_id' => 'nullable|exists:package_types,id',
            'is_emergency' => 'nullable',
        ])->validate();

        try {
            return DB::transaction(function () use ($validated) {

                $appointment = Appointment::create([
                    'client_id' => $validated['client_id'],
                    'vendor_id' => $validated['vendor_id'],
                    'address_id' => $validated['address_id'],
                    'preferred_date' => $validated['preferred_date'],
                    'preferred_time' => $validated['preferred_time'],
                    'start_time' => $validated['preferred_time'],
                    "is_recurring" => $validated['is_recurring'],
                    "plan_id" => $validated['plan_id'],
                    'start_date' => $validated['preferred_date'],
                    'is_emergency' => $validated['is_emergency'],

                    'state' => 'publish',
                    'status' => 'booked',
                ]);


                // Add services to the appointment
                $this->addServices($appointment, $validated['service_list']);

                if (isset($validated['timeslot_id']) && !empty($validated['timeslot_id'])) {
                    $this->handleTimeslot($validated, $appointment);
                }


                // Update totals after adding services
                // $invoice = InvoiceHead::create([
                //     'table_id' => $appointment->id,
                //     'table_name' => 'appointments',
                //     'client_id' => $appointment->client_id,
                //     'vendor_id' => $appointment->vendor_id,
                //     'shipping_addr_id' => $appointment->address_id,
                //     'sub_total' => $appointment->sub_total,
                //     'tax_amount' => $appointment->tax_amount,
                //     'amount' => $appointment->total_amount,
                //     'type' => 'debit',
                // ]);

                // // dd($invoice);

                // $this->generateInvoiceItems($appointment, $invoice->id);

                $appointment->update(['state' => 'publish']);

                $this->sendEmail($appointment);

                return $appointment;
            });
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
                "line" => $th->getLine(),
                "file" => $th->getFile(),
            ], 422);
        }
    }

    public function handleTimeslot(array $data, Appointment $appointment)
    {
        try {
            $timeslot = ProviderTimeslot::with('providerDate')->find($data['timeslot_id']);

            if (!$timeslot || !$timeslot->providerDate) {
                throw new \Exception("Timeslot or associated provider date not found.");
            }

            $timeslot->is_booked = 1;
            $timeslot->save();

            $appointment->update([
                'timeslot_id' => $timeslot->id,
                'start_time' => $timeslot->providerDate->start_time,
                'start_date' => $timeslot->providerDate->date,
            ]);

            $appointment->provider()->create([
                'provider_id' => $timeslot->providerDate->provider_id,
                'timeslot_id' => $timeslot->id,
            ]);
        } catch (\Exception $e) {
            // rethrow to let the transaction handle rollback
            throw new \Exception("Failed to handle timeslot: " . $e->getMessage());
        }
    }

    public function sendEmail(Appointment $appointment)
    {
        $templateService = new EmailTemplateService();
        $client = $appointment->client;
        $vendor = $appointment->vendor;

        if (!$client || empty($client->email)) {
            Log::warning('Missing client or email for appointment ID: ' . $appointment->id);
            return;
        }

        // Prepare service names
        $serviceNames = $appointment->services->pluck('service_name')->toArray();
        $joinedServiceNames = implode(', ', $serviceNames);

        $appointmentTime = $appointment->start_time
            ? Carbon::parse($appointment->start_time)->format('h:i A')
            : ($appointment->preferred_time ? Carbon::parse($appointment->preferred_time)->format('h:i A') : '');

        // Prepare template data
        $data = [
            'customer_name'        => $client->fullName,
            'customer_phone'       => $client->phone_no,
            'customer_email'       => $client->email,
            'service_location'     => $appointment->address->add1 . ', ' .
                $appointment->address->city . ', ' .
                $appointment->address->state . ', ' .
                $appointment->address->zip . ', ' .
                strtoupper($appointment->address->country_code),
            'appointment_number'   => $appointment->appointment_no,
            'appointment_date'     => Carbon::parse($appointment->start_date)->format('M d, Y'),
            'appointment_time'     => $appointmentTime,
            'service_name'         => $joinedServiceNames,
            'cancellation_reason'  => $appointment->cancellation_reason ?? 'No reason provided',
            'company_name'         => $vendor->name,
            'company_website'      => env('APP_WEBSITE') ?? config('app.url', 'https://example.com'),
        ];
        $emailTemplate = EmailTemplate::where('code', 'appointment_created')->first();

        if (!$emailTemplate) {
            Log::warning('Email template not found: appointment_created');
            return;
        }

        try {
            // Render email subject and content
            $rendered = $templateService->load($emailTemplate, $data)->render();


            $messageService = app(MessageService::class);

            $messageService->sendMessage($appointment, $vendor, $client, $rendered["msgContent"]);

            // Instantiate mail service with vendor (loads SMTP config internally)
            $mailService = new VendorMailService($vendor);

            // Prepare email details
            $emailData = [
                'from_email' => $vendor->contact->email,
                'from_name' => $vendor->name,
                'to_email' => $client->email,
                'to_name' => $client->name ?? '',
                'subject' => $rendered['subject'],
                'message' => $rendered['content'], // HTML allowed
                'cc' => [],      // optional: add if needed
                'bcc' => [],     // optional: add if needed
                'attachments' => [], // optional: add files if needed
                'table_name' => 'appointments',
                'table_id' => $appointment->id,
            ];

            // Send email
            // $result = $mailService->send($emailData);

            $emailToCustomer = $mailService->send($emailData);

            $emailData = [
                'from_email'  => 'noreply@email.com',
                'from_name'   => $vendor->name,
                'to_email'    => $vendor->contact->email ?? 'sales@example.com',
                'to_name'     => $vendor->name,
                'subject'     => $rendered['vendor_subject'],
                'message'     => $rendered['vendor_content'],

                'cc'          => [],
                'bcc'         => [],
                'attachments' => [],
                'table_name'  => 'appointments',
                'table_id'    => $appointment->id,
            ];

            $emailToVendor =  $mailService->send($emailData);

            return [
                'emailToCustomer' => $emailToCustomer,
                'emailToVendor' => $emailToVendor,
            ];
        } catch (\Throwable $e) {
            return response([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function addServices(Appointment $appointment, $serviceList): array
    {
        $appointmentServices = [];

        foreach ($serviceList as $service) {
            $appointmentServices[] = AppointmentService::create([
                'appointment_id' => $appointment->id,
                'service_id' => $service['service_id'],
                'service_name' => $service['service_name'],
                'quantity' => $service['quantity'] ?? 1,
                'price' => $service['price'],
                'cancel_charge' => $service['cancel_charge'] ?? 0,
                'amend_charge' => $service['amend_charge'] ?? 0,
                'state' => 'publish',
            ]);
        }
        $this->updateTotals($appointment);
        return $appointmentServices;
    }

    public function updateTotals(Appointment $appointment): void
    {
        $subTotal = $appointment->services->sum('price');
        $tax = $this->applyTax($appointment, $subTotal);
        $taxAmount = $tax->applied_amount ?? 0;


        $appointment->update([
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $subTotal + $taxAmount,
        ]);
    }

    public function generateInvoiceItems(Appointment $appointment, int $invoiceId): void
    {
        $items = [];

        foreach ($appointment->services as $service) {

            $items[] = [
                'invoice_head_id' => $invoiceId,
                'vendor_id' => $appointment->vendor_id,
                'service_id' => $service->service_id,
                'item_name' => $service->service_name ?? null,
                'service_name' => $service->service_name ?? null,
                'item_price' => $service->price,
                'quantity' => $service->quantity ?? 1,
                'amount' => $service->price,
            ];
        }

        InvoiceItem::insert($items);
    }

    protected function applyTax(Appointment $appointment, float $subTotal)
    {
        $tax = TaxMaster::where('tax_code', 'service_tax')
            ->where('vendor_id', $appointment->vendor_id)
            ->first();

        // dd($tax);
        $taxAmount = 0;
        if ($tax) {
            $taxAmount = $tax->type === 'percentage'
                ? ($tax->value / 100) * $subTotal
                : $tax->value;
            return AppointmentTax::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'type' => $tax->tax_name,
                    'tax_value' => $taxAmount,
                    'tax_code' => $tax->tax_code,
                    'applied_amount' => $taxAmount,
                ]
            );
        } else {
            return AppointmentTax::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'type' => 'Service Tax',
                    'tax_value' => 0,
                    'tax_code' => 'service_tax',
                    'applied_amount' => 0,
                ]
            );
        }
    }


    public function sendPaymentConfirmationEmail(Appointment $appointment, Payment $payment)
    {
        $templateService = new EmailTemplateService();

        $client = $appointment->client;
        $vendor = $appointment->vendor;

        if (!$client || empty($client->email)) {
            Log::warning('Missing client or email for appointment ID: ' . $appointment->id);
            return;
        }

        // Prepare service names
        $serviceNames = $appointment->services->pluck('service_name')->toArray();
        $joinedServiceNames = implode(', ', $serviceNames);


        $appointmentTime = $appointment->start_time
            ? Carbon::parse($appointment->start_time)->format('h:i A')
            : ($appointment->preferred_time ? Carbon::parse($appointment->preferred_time)->format('h:i A') : '');
        // Prepare template data
        $paymentMethod = strtolower($payment->payment_type) === "cash"
            ? "Cash"
            : (!empty($payment->cr_last4) ? (strtoupper($payment->payment_type) . " ****" . $payment->cr_last4) : $payment->payment_type);


        $data = [
            'customer_name'        => $client->fullName,
            'customer_phone'       => $client->phone_no,
            'customer_email'       => $client->email,
            'payment_method'        => $paymentMethod,
            'payment_amount'        => Formatter::priceFormat($payment->amount),
            'service_location'     => $appointment->address->add1 . ', ' .
                $appointment->address->city . ', ' .
                $appointment->address->state . ', ' .
                $appointment->address->zip . ', ' .
                strtoupper($appointment->address->country_code),
            'appointment_number'   => $appointment->appointment_no,
            'appointment_date'     => Carbon::parse($appointment->start_date)->format('M d, Y'),
            'appointment_time'     => $appointmentTime,
            'service_name'         => $joinedServiceNames,
            'cancellation_reason'  => $appointment->cancellation_reason ?? 'No reason provided',
            'company_name'         => $vendor->name,
            'company_website'      => env('APP_WEBSITE') ?? config('app.url', 'https://example.com'),
        ];

        $emailTemplate = EmailTemplate::where('code', 'payment_confirmation')->first();

        if (!$emailTemplate) {
            Log::warning('Email template not found: payment_confirmation');
            return;
        }

        try {
            // Render email subject and content
            $rendered = $templateService->load($emailTemplate, $data)->render();

            $messageService = app(MessageService::class);

            $messageService->sendMessage($appointment, $vendor, $client, $rendered["msgContent"]);

            // Instantiate mail service with vendor (loads SMTP config internally)
            $mailService = new VendorMailService($vendor);

            // Prepare email details
            $emailData = [
                'from_email'  => $vendor->contact->email,
                'from_name'   => $vendor->name,
                'to_email'    => $client->email,
                'to_name'     => $client->name ?? '',
                'subject'     => $rendered['subject'],
                'message'     => $rendered['content'], // HTML allowed
                'cc'         => [],      // optional
                'bcc'        => [],      // optional
                'attachments' => [],     // optional
                'table_name'  => 'payments',
                'table_id'    => $payment->id,
            ];


            $emailToCustomer = $mailService->send($emailData);

            $emailData = [
                'from_email'  => 'noreply@email.com',
                'from_name'   => $vendor->name,
                'to_email'    => $vendor->contact->email ?? 'sales@example.com',
                'to_name'     => $vendor->name,
                'subject'     => $rendered['vendor_subject'],
                'message'     => $rendered['vendor_content'],

                'cc'          => [],
                'bcc'         => [],
                'attachments' => [],
                'table_name'  => 'appointments',
                'table_id'    => $appointment->id,
            ];

            // $emailToVendor =  $mailService->send($emailData);


            return [
                'emailToCustomer' => $emailToCustomer,
                // 'emailToVendor' => $emailToVendor,
            ];

            return $result;
        } catch (\Throwable $e) {
            return response([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500);
        }
    }

    public function sendAppointmentUpdateEmail(Appointment $appointment)
    {
        $templateService = new EmailTemplateService();
        $client = $appointment->client;
        $vendor = $appointment->vendor;

        if (!$client || empty($client->email)) {
            Log::warning('Missing client or email for appointment ID: ' . $appointment->id);
            return;
        }

        // Prepare service names
        $serviceNames = $appointment->services->pluck('service_name')->toArray();
        $joinedServiceNames = implode(', ', $serviceNames);

        // Fallback for appointment_time
        $appointmentTime = $appointment->start_time
            ? \Carbon\Carbon::parse($appointment->start_time)->format('h:i A')
            : $appointment->preferred_time;

        // Prepare template data
        $data = [
            'customer_name'        => $client->fullName,
            'customer_phone'       => $client->phone_no,
            'customer_email'       => $client->email,
            'service_location'     => $appointment->address->add1 . ', ' .
                $appointment->address->city . ', ' .
                $appointment->address->state . ', ' .
                $appointment->address->zip . ', ' .
                strtoupper($appointment->address->country_code),
            'appointment_number'   => $appointment->appointment_no,
            'appointment_date'     => Carbon::parse($appointment->start_date)->format('M d, Y'),
            'appointment_time'     => $appointmentTime,
            'service_name'         => $joinedServiceNames,
            'cancellation_reason'  => $appointment->cancellation_reason ?? 'No reason provided',
            'company_name'         => $vendor->name,
            'company_website'      => env('APP_WEBSITE') ?? config('app.url', 'https://example.com'),
        ];

        // Load email template
        $emailTemplate = EmailTemplate::where([
            'code' => 'appointment_updated',
            "vendor_id" => $vendor->id,
        ])->first();


        if (!$emailTemplate) {
            Log::warning('Email template not found: appointment_updated');
            return;
        }

        try {
            // Render email
            $rendered = $templateService->load($emailTemplate, $data)->render();

            // Prepare email details
            $emailData = [
                'from_email'    => $vendor->contact->email,
                'from_name'     => $vendor->name,
                'to_email'      => $client->email,
                'to_name'       => $client->name ?? '',
                'subject'       => $rendered['subject'],
                'message'       => $rendered['content'], // HTML allowed
                'cc'            => [],
                'bcc'           => [],
                'attachments'   => [],
                'table_name'    => 'appointments',
                'table_id'      => $appointment->id,
            ];

            // Send using VendorMailService
            $mailService = new VendorMailService($vendor);


            $emailToCustomer = $mailService->send($emailData);

            $emailData = [
                'from_email'  => 'noreply@email.com',
                'from_name'   => $vendor->name,
                'to_email'    => $vendor->contact->email ?? 'sales@example.com',
                'to_name'     => $vendor->name,
                'subject'     => $rendered['vendor_subject'],
                'message'     => $rendered['vendor_content'],

                'cc'          => [],
                'bcc'         => [],
                'attachments' => [],
                'table_name'  => 'appointments',
                'table_id'    => $appointment->id,
            ];

            $emailToVendor =  $mailService->send($emailData);

            return [
                'emailToCustomer' => $emailToCustomer,
                'emailToVendor' => $emailToVendor,
            ];
        } catch (\Throwable $e) {
            return response([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500);
        }
    }

    public function sendAppointmentCancelledEmail(Appointment $appointment)
    {
        $templateService = new EmailTemplateService();
        $client = $appointment->client;
        $vendor = $appointment->vendor;

        if (!$client || empty($client->email)) {
            Log::warning('Missing client or email for appointment ID: ' . $appointment->id);
            return;
        }

        // header('Access-Control-Allow-Origin:*');

        // Prepare service names
        $serviceNames = $appointment->services->pluck('service_name')->toArray();
        $joinedServiceNames = implode(', ', $serviceNames);

        // Determine the correct time
        $appointmentTime = $appointment->appointment_time
            ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A')
            : $appointment->preferred_time;

        // Prepare template data
        $data = [
            'customer_name'        => $client->fullName,
            'customer_phone'       => $client->phone_no,
            'customer_email'       => $client->email,
            'service_location'     => $appointment->address->add1 . ', ' .
                $appointment->address->city . ', ' .
                $appointment->address->state . ', ' .
                $appointment->address->zip . ', ' .
                strtoupper($appointment->address->country_code),
            'appointment_number'   => $appointment->appointment_no,
            'appointment_date'     => Carbon::parse($appointment->start_date)->format('M d, Y'),
            'appointment_time'     => $appointmentTime,
            'service_name'         => $joinedServiceNames,
            'cancellation_reason'  => $appointment->cancellation_reason ?? 'No reason provided',
            'company_name'         => $vendor->name,
            'company_website'      => env('APP_WEBSITE') ?? config('app.url', 'https://example.com'),
        ];



        // Load email template
        $emailTemplate = EmailTemplate::where('code', 'appointment_cancellation')->first();

        if (!$emailTemplate) {
            Log::warning('Email template not found: appointment_cancellation');
            return;
        }

        try {
            $rendered = $templateService->load($emailTemplate, $data)->render();

            $emailData = [
                'from_email'    => $vendor->contact->email,
                'from_name'     => $vendor->name,
                'to_email'      => $client->email,
                'to_name'       => $client->name ?? '',
                'subject'       => $rendered['subject'],
                'message'       => $rendered['content'],
                'cc'            => [],
                'bcc'           => [],
                'attachments'   => [],
                'table_name'    => 'appointments',
                'table_id'      => $appointment->id,
            ];

            $mailService = new VendorMailService($vendor);
            // return $mailService->send($emailData);

            $emailToCustomer = $mailService->send($emailData);

            $emailData = [
                'from_email'  => 'noreply@email.com',
                'from_name'   => $vendor->name,
                'to_email'    => $vendor->contact->email ?? 'sales@example.com',
                'to_name'     => $vendor->name,
                'subject'     => $rendered['vendor_subject'],
                'message'     => $rendered['vendor_content'],

                'cc'          => [],
                'bcc'         => [],
                'attachments' => [],
                'table_name'  => 'appointments',
                'table_id'    => $appointment->id,
            ];

            $emailToVendor =  $mailService->send($emailData);

            return [
                'emailToCustomer' => $emailToCustomer,
                'emailToVendor' => $emailToVendor,
            ];
        } catch (\Throwable $e) {
            return response([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500);
        }
    }
}
