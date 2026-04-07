<?php

namespace Systha\Core\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAvailability extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function timeslots()
    {
        return $this->hasMany(VendorAvailableTimeslot::class)->where('is_deleted', 0);
    }
}
