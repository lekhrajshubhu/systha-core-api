<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Offer extends Model
{
    use HasFactory;

    protected $table = 'offers';
    protected $guarded = [];
    public function products()
    {
        return $this->hasManyThrough(Product::class, OfferProduct::class, 'offer_id', 'id', 'id', 'product_id');
    }

    public function vendorTemplate()
    {
        return $this->belongsTo(VendorTemplate::class, 'vendor_template_id', 'id');
    }
}
