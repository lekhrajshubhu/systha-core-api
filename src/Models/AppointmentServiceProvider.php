<?php


namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AppointmentServiceProvider extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['provider', 'timeslot', 'slot'];

    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id');
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    public function slot()
    {
        return $this->belongsTo(ProviderTimeslot::class, 'timeslot_id');
    }
    public function timeslot()
    {
        return $this->belongsTo(VendorAvailableTimeslot::class, 'slot_id');
    }
}
