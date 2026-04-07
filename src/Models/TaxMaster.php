<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;

class TaxMaster extends Model
{
    protected $guarded = [];

    public function vendor(){
        return $this->belongsTo(Vendor::class,'vendor_id','id')->where('vendors.is_deleted',0);
    }
    protected $hidden = [
        'created_at',
        'deleted_at',
        'updated_at',
        'is_deleted',
        'is_active',
        'userc_id',
        'useru_id',
        'userd_id',
        'is_employee',
        'year_id',
	];
}
