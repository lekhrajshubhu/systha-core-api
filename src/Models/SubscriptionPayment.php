<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $table = 'subscription_payments';
    protected $guarded = [];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function cart()
    {
        return $this->belongsTo(SubscriptionCart::class, 'cart_id');
    }
}
