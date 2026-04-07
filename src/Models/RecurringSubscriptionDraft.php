<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RecurringSubscriptionDraft extends Model
{
    use HasFactory;

    protected $table = 'recurring_subscription_drafts';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function (self $draft) {
            if (empty($draft->booking_reference)) {
                $draft->booking_reference = (string) Str::uuid();
            }
        });
    }

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
}
