<?php

namespace Systha\Core\Lib\Services\Payment;

interface PayableInterface{

    public function pay(array $data);

}
