<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AppointmentProduct extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $with=['product'];
    public function product(){
        return $this->belongsTo(Inventory::class,'inv_id')->select('id','name','inv_stock','selling_price as price');
    }
}
