<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageType extends Model
{
    use HasFactory;
    protected $guarded = [];
      protected $appends = ['type_and_duration'];
    // protected $with = ['vendor'];
    public function packageService()
    {
        return $this->hasMany(PackageService::class, 'service_id', 'id')->where('is_deleted', 0);
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    // Custom attribute
    protected function typeAndDuration(): Attribute
    {
        return Attribute::get(function () {
            $type = optional($this->package)->type_name;
            $duration = optional($this->package)->duration;

            return trim(implode(' – ', array_filter([$type, $duration])));
        });
    }
}
