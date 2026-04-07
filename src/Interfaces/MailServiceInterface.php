<?php

namespace Systha\Core\Interfaces;


interface MailServiceInterface{
    public function sendUsingMailable($mailable,$to);

    public function sendUsingEmailTemplate($code,$merge_data,$to);
}
