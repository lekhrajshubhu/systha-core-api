<?php

namespace Systha\Core\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAvailableTimeslot extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'vendor_available_timeslots';
}
