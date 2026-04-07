<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\InvoiceHead;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PackageSubscription extends Model
{
    use HasFactory;
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'package_subscriptions' => static::class
        ]);
        static::creating(function ($subs) {
            if (!$subs->subs_no) {
                $subs->subs_no = static::generateUniqueEnquiryNo();
            }
        });
    }

    public function invoices()
    {
        return $this->hasMany(InvoiceHead::class, 'table_id', 'id')->where('table_name', 'package_subscriptions');
    }

    public static function generateUniqueEnquiryNo()
    {
        do {
            $uniqueCode = 'SUB' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        } while (self::where('subs_no', $uniqueCode)->exists());

        return $uniqueCode;
    }
    // public function invoices()
    // {
    //     return $this->morphMany(InvoiceHead::class, 'invoicable');
    // }
    public function client()
    {
        return $this->hasOne(Client::class, 'id', 'client_id')->where('is_deleted', 0)->where('state', 'publish');
    }

    public function package()
    {
        return $this->hasOne(Package::class, 'id', 'package_id')->where('is_deleted', 0)->where('state', 'publish');
    }
    public function packageItem()
    {
        return $this->hasOne(Package::class, 'id', 'package_id')->where('is_deleted', 0)->where('state', 'publish');
    }

    public function packageType()
    {
        return $this->hasOne(PackageType::class, 'id', 'package_type_id')->where('is_deleted', 0)->where('state', 'publish');
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id')->where('is_deleted', 0)->where('state', 'publish');
    }

    public function cart()
    {
        return $this->hasOne(SubscriptionCart::class, 'subscription_id');
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class, 'subscription_id');
    }

    public function payment()
    {
        return $this->hasOne(SubscriptionPayment::class, 'subscription_id');
    }

    // public function quote()
    // {
    //     return $this->hasOne(SubscriptionQuote::class, 'package_subscription_id');
    // }
}
