<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array',
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'service_category_id', 'id')->where('is_deleted', 0);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }
}
