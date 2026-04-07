<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecurringSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recurring_subscriptions';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(ClientModel::class, 'client_id');
    }

    public function vendor()
    {
        return $this->belongsTo(VendorModel::class, 'vendor_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function plan()
    {
        return $this->belongsTo(PackageType::class, 'plan_id');
    }

    public function draft()
    {
        return $this->belongsTo(RecurringSubscriptionDraft::class, 'draft_id');
    }
}
