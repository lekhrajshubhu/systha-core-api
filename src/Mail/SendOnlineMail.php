<?php

namespace Systha\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Systha\Core\Models\File;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOnlineMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject, $content_message, $id,$parameters;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $subject, $content_message, $id,$parameters)
    {
        $this->subject = $subject;
        $this->content_message = $content_message;
        $this->id = $id;
        $this->parameters = $parameters;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $files = File::where('table_name', 'Mail')->where('table_id', $this->id)->get();
        $mail = $this->view('core::mail.onlineMail')->subject($this->subject);
        foreach($files as $file){
            // if(!file_exists(storage_path('mail'. DIRECTORY_SEPARATOR .$file->file_name))) continue ;
            // $mail->attach(storage_path('mail'. DIRECTORY_SEPARATOR .$file->file_name));
            if(!file_exists($this->parameters['storage_path']. DIRECTORY_SEPARATOR .'mail'. DIRECTORY_SEPARATOR .$file->file_name)) continue ;
            $mail->attach($this->parameters['storage_path']. DIRECTORY_SEPARATOR .'mail'. DIRECTORY_SEPARATOR .$file->file_name);
        }

        return $mail;
    }
}
