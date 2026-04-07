<?php

namespace Systha\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $user_id;
    public $user;
    public $url;
    public $logo;
    public $password;
    public $model;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $logo, $password)
    {
        $this->model = $user;
        $this->user= $user;
        $this->logo= $logo;
        $this->user_id= $this->user->id;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@admin.com')
        ->subject('Reset Password')
        ->view("pesttemp::mail.password_reset_email");
    }
}
