<?php

namespace Systha\Core\Models;


use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Relation;


class AppointmentModel extends Model
{

    protected $table = 'appointments';
    protected $guarded = [];


    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'appointments' => static::class
        ]);

        static::creating(function ($appointment) {
            $lastAppointment = self::where('vendor_id', $appointment->vendor_id)
                ->latest('id')
                ->first();

            $lastNumber = $lastAppointment ? intval(str_replace('APPT-', '', $lastAppointment->appointment_no)) : 0;

            $appointment->appointment_no = 'APPT-' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        });
    }

}
