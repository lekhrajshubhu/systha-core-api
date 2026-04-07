<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\BlogCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HomeRemedy extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category()
    {
        return $this->hasOne(BlogCategory::class, 'id', 'category_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
