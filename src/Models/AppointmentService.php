<?php

namespace Systha\Core\Models;

use Systha\Core\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\Appointment;
use Systha\Core\Models\ServiceProvider;


class AppointmentService extends Model
{
     
    protected $guarded=[];
    protected $with = ['appointment', 'provider', 'service'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
