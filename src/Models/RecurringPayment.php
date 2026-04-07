<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringPayment extends Model
{
    protected $table = 'recurring_payments';
    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
        'processed_at' => 'datetime',
        'raw' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(RecurringSubscription::class, 'recurring_subscription_id');
    }
}
