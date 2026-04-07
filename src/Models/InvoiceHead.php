<?php

namespace Systha\Core\Models;



use Illuminate\Support\Facades\DB;
use Systha\Core\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceHead extends Model
{
    protected $guarded = [];
    protected $with = ['client', 'appointment'];

    /**
     * Bootstrap the model and enforce the morph map.
     */
    protected static function boot()
    {
        parent::boot();

        // Define the morph map for better readability
        Relation::enforceMorphMap([
            'package_subscriptions' => PackageSubscription::class,
            'appointments' => Appointment::class,
        ]);

        static::creating(function ($invoice) {
            $invoice->invoice_no = self::generateUniqueInvoiceNo();
        });
    }


    protected static function generateUniqueInvoiceNo()
    {
        return DB::transaction(function () {
            // Lock the table to prevent race conditions
            $latestInvoice = self::where('is_deleted', 0)
                ->where('invoice_no', 'LIKE', '#INV-%')
                ->orderByDesc('created_at')
                ->lockForUpdate()
                ->first();

            if (!$latestInvoice || !preg_match('/#INV-(\d+)/', $latestInvoice->invoice_no, $matches)) {
                $iterate = 1;
            } else {
                $iterate = (int) $matches[1] + 1;
            }

            $newInvoiceNo = "#INV-" . str_pad($iterate, 5, '0', STR_PAD_LEFT);

            // Ensure this invoice number is unique before finalizing
            while (self::where('invoice_no', $newInvoiceNo)->exists()) {
                $iterate++;
                $newInvoiceNo = "#INV-" . str_pad($iterate, 5, '0', STR_PAD_LEFT);
            }

            return $newInvoiceNo;
        });
    }

    /**
     * Define the polymorphic relation for invoicable.
     */
    public function invoicable()
    {
        return $this->morphTo(null, 'table_name', 'table_id');
    }
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'table_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'invoice_id');
    }
}
