<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Package extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['lowest_price', 'plan_recurring'];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'packages' => static::class,
        ]);
    }


    public function packageType()
    {
        return $this->hasMany(PackageType::class, 'package_id', 'id')->where('is_deleted', 0);
    }
    public function plans()
    {
        return $this->hasMany(PackageType::class, 'package_id', 'id')->where('is_deleted', 0);
    }

    public function lowestPricedPlan()
    {
        return $this->hasOne(PackageType::class, 'package_id', 'id')
            ->where('is_deleted', 0)
            ->orderBy('amount', 'asc');
    }


    public function getLowestPriceAttribute()
    {
        $plan = $this->lowestPricedPlan()->first();

        return $plan->amount ?? 0;
    }

    public function getPlanRecurringAttribute()
    {
        $plan = $this->lowestPricedPlan()->first();

        return $plan->type_name ?? null;
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id')->where('is_deleted', 0);
    }
    public function coupon()
    {
        return $this->hasOne(Coupon::class, 'id', 'coupon_id')->where('is_deleted', 0);
    }

    public function packageServices()
    {
        return $this->hasMany(PackageService::class, 'package_id', 'id')->where('is_deleted', 0);
    }
    public function services()
    {
        return $this->hasMany(PackageService::class, 'package_id', 'id')->where('is_deleted', 0);
    }

    public function files()
    {
        return $this->morphMany(EcommFile::class, 'table_name', 'table_name', 'table_id')->where('ecomm_files.is_deleted', 0);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->package_thumb) {
            return Storage::disk('media')->url('packages/images/' . $this->package_thumb);
        }

        return Storage::disk('media')->url('images/noimage.png');
    }

    //   Route::get('/package-image/{file_name}', [PackageViewController::class, 'packageImage'])->name('package.thumb');

    public function thumb()
    {
        return $this->hasOne(File::class, 'table_id', 'id')
            ->where('table_name', 'Package_Thumb');
    }
}
