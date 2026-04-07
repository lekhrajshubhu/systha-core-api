<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;

class StripeCustomer extends Model
{
    protected $guarded = [];

    public function paymentMethods()
    {
        return $this->hasMany(StripePaymentMethod::class)->where('is_deleted',0);
    }

    public function defaultPaymentMethod()
    {
        return $this->hasOne(StripePaymentMethod::class, 'stripe_customer_id')
            ->where('is_default', 1)
            ->where('is_deleted', 0);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
