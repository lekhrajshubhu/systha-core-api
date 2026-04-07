<?php

namespace Systha\Core\Mail;

use App\Model\File;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEnquiryRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public  $enquiry;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $enq)
    {
        $this->enquiry = $enq;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('pesttemp::mail.enquiry_request_mail', [
            'enquiry' => $this->enquiry
        ])->from(default_company('company_email'), default_company('company_name'));
    }
}
