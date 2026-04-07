<?php
/**
 * Created by PhpStorm.
 * User: REACT DEV RAKESH
 * Date: 11/15/2019
 * Time: 3:22 PM
 */

namespace Systha\Core\Lib\Services;

use App\Mail\Log\LogMail;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Arr;
use App\Mail\Support\TaskSubmission;
use Illuminate\Support\Facades\Crypt;

interface MailCredentialInterface{
    public function host();
    public function port();
    public function username();
    public function password();
    public function encryption();
    public function fromName();
}

class MailTransporter implements MailCredentialInterface
{
    private const HOST = 'smtp_host';
    private const PORT = 'smtp_port';
    private const USERNAME = 'smtp_username';
    private const PASSWORD = 'smtp_password';
    private const ENCRYPTION = 'smtp_encryption';
    private const FROMNAME = 'from_name';
    private const FROMEMAIL = 'from_email';
//    private const TASK = 'task';

    private $config;
    private $mailableMail;

    public function __construct($config, $mailableMail){
        $this->config = $config;
        $this->mailableMail = $mailableMail;
        $this->handle();
    }

    private  function handle(){
        $swiftMailer = $this->setSwiftMailer();
        $mailer = $this->setMailer($swiftMailer);
        if($this->cc()){
            $mailer
            ->to($this->to())
            ->cc($this->cc())
            ->send($this->mailableMail->from($this->fromEmail(), $this->fromName()));
        }else{
            $mailer
                ->to($this->to())
                ->send($this->mailableMail);
        }
    }

    private function setSwiftMailer(){
        $transport = new \Swift_SmtpTransport($this->host(), $this->port(), $this->encryption());
        // dd($transport);
        $transport->setUsername($this->username());
        $transport->setPassword($this->password());
        return new \Swift_Mailer($transport);
    }

    private function setMailer($swiftMailer){
        $view = app()->get('view');
        $events = app()->get('events');
        // dd($swiftMailer);
        $mailer = new Mailer('',$view, $swiftMailer, $events);
        $mailer->alwaysFrom($this->fromEmail(), $this->fromName());
        return $mailer;
    }

    public function host()
    {
        //dd($this->config);
       return array_get($this->config, STATIC::HOST);
    }

    public function port()
    {
        return array_get($this->config, STATIC::PORT);
    }

    public function username()
    {
        return array_get($this->config, STATIC::USERNAME);
    }

    public function fromEmail()
    {
        return array_get($this->config, STATIC::FROMEMAIL);
    }

    public function password()
    {
        return  Crypt::decryptString(array_get($this->config, STATIC::PASSWORD));
    }

    public function encryption()
    {
        return array_get($this->config, STATIC::ENCRYPTION);
    }

    public function fromName()
    {
        return array_get($this->config, STATIC::FROMNAME);
    }

//    public function task(){
//        return array_get($this->config, STATIC::TASK);
//    }

    public function to(){
        return array_get($this->config, 'to');
    }

    public function cc(){
        return array_get($this->config, 'cc');
    }

}
