<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDefault extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = "vendor_defaults";

    public function vendor(){
        return $this->belongsTo(Vendor::class,'vendor_id','id')->where('vendors.is_deleted',0);
    }

}
