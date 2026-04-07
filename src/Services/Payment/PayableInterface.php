<?php

namespace Systha\Core\Services\Payment;

interface PayableInterface{

    public function pay(array $data);

}