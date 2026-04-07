<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPaymentProfile extends Model
{
    use HasFactory;

    protected $table = 'customer_payment_profiles';
    protected $guarded = [];
}
