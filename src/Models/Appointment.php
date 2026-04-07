<?php

namespace Systha\Core\Models;


use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\InvoiceHead;
use Systha\Core\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;


class Appointment extends Model
{

    protected $guarded = [];

    // protected $appends = ['total_service', 'total_amount', 'total_tax'];

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
    public function serviceProviders()
    {
        return $this->hasMany(AppointmentServiceProvider::class);
    }

    public function provider()
    {
        return $this->hasOne(AppointmentServiceProvider::class)->latest();
    }

    public function subscription()
    {
        return $this->belongsTo(PackageSubscription::class, 'subscription_id');
    }

    public function invoices()
    {
        return $this->morphMany(InvoiceHead::class, 'invoicable');
    }

    // public function services()
    // {
    //     return $this->belongsToMany(Service::class, 'appointment_services', 'appointment_id')->withPivot('id', 'start_time', 'end_time', 'price', 'service_time', 'is_checkout')
    //         ->where('appointment_services.is_deleted', 0);
    //     // ->where('appointment_services.is_checkout', 0)
    //     // ->groupBy('service_id');
    // }
    public function services()
    {
        return $this->belongsToMany(Service::class, 'appointment_services', 'appointment_id')->withPivot('id', 'start_time', 'end_time', 'service_time', 'is_checkout')
            ->where('appointment_services.is_deleted', 0);
    }
    // public function getTotalServiceAttribute()
    // {
    //     return $this->services->sum(function ($service) {
    //         return $service->price; // Adjust this logic based on your service table
    //     });
    // }
    // public function getTotalTaxAttribute()
    // {
    //     return $this->tax ? $this->tax->applied_amount : 0; // Assume tax has an `amount` field
    // }
    // public function getTotalAmountAttribute()
    // {
    //     return $this->total_service + $this->total_tax;
    // }
    public function serviceCart()
    {
        return $this->belongsToMany(Service::class, 'appointment_services', 'appointment_id')->withPivot('id', 'start_time', 'end_time', 'price', 'service_time')
            ->where('appointment_services.is_deleted', 0)
            ->where('appointment_services.is_checkout', 0)
            ->groupBy('service_id');
    }

    public function providers()
    {
        return $this->belongsToMany(ServiceProvider::class, 'appointment_services', 'appointment_id')->withPivot('id', 'service_id', 'start_time', 'end_time')->where('appointment_services.is_deleted', 0)->groupBy('service_provider_id');
    }
    // public function technician()
    // {
    //     return $this->belongsTo(ServiceProvider::class, 'appointment_services', 'appointment_id')->withPivot('id', 'service_id', 'start_time', 'end_time')->where('appointment_services.is_deleted', 0)->groupBy('service_provider_id');
    // }
    public function technician()
    {
        return $this->belongsToMany(
            ServiceProvider::class,
            'appointment_service_providers',
            'appointment_id',
            'provider_id'
        )->withPivot('appointment_id', 'provider_id');
    }

    public function invoice()
    {
        return $this->hasOne(InvoiceHead::class, 'table_id')
            ->where('table_name', 'appointments'); // Ensure it links only to Appointments
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function tax()
    {
        return $this->hasOne(AppointmentTax::class, 'appointment_id');
    }

    // public function invoices()
    // {
    //     return $this->hasMany(InvoiceHead::class, 'table_id', 'id')->where('table_name', 'appointments');
    // }
    public function products()
    {
        return $this->hasMany(AppointmentProduct::class, 'appointment_id', 'id')->where('is_checkout', 0)->orderByDesc('created_at');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'table_id', 'id')->where('table_name', 'appointments');
    }
    public function paymentCheckouts()
    {
        return $this->hasMany(Payment::class, 'table_id', 'id')->where('table_name', 'appointments')->where('is_checkout', 1)->orderByDesc('created_at');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'table_id', 'id')->where('table_name', 'appointments')->orderByDesc('created_at');
    }

    public function address()
    {
        return $this->hasOne(Address::class, 'id', 'address_id');
    }

    // public function drInvoices()
    // {
    //     return $this->hasMany(InvoiceHead::class, 'table_id', 'id')->where('table_name', 'appointments')->where('type', "debit");
    // }
    // public function orders()
    // {
    //     return $this->hasMany(Order::class, 'table_id', 'id')->where('table_name', 'appointments');
    // }

    public function crInvoices()
    {
        return $this->hasMany(InvoiceHead::class, 'table_id', 'id')->where('table_name', 'appointments')->where('type', "credit");
    }

    // public function order()
    // {
    //     return $this->hasOne(Order::class, 'id', 'order_id');
    // }

    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable', 'table_name', 'table_id')->where('is_deleted', 0)->latest('updated_at');
    }

    public function review()
    {
        return $this->morphOne(Review::class, 'reviewable', 'table_name', 'table_id')->where('is_deleted', 0);
    }
    public function reviews()
    {
        return $this->morphMany(Review::class, 'table', 'table_name', 'table_id')->orderBy('created_at', 'desc');
    }
    public function quotation()
    {
        return $this->belongsTo(Quote::class, 'quotation_id');
    }
}
