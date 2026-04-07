<?php

namespace Systha\Core\Models;

use Systha\Core\Models\Note;
use Systha\Core\Models\Client;
use Systha\Core\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Quote extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['client', 'vendor', 'taxes', 'quoteServices', 'notes', 'fees', 'unreadNotes'];
    protected $appends = ["total_amount", "tax_amount", "grand_total"];

    protected static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'quotes' => static::class
        ]);
    }
    public function getTotalAmountAttribute()
    {
        return $this->quoteServices()->sum('price');
    }

    public function getTaxAmountAttribute()
    {
        $total = $this->total;
        $vendor = Vendor::find($this->vendor_id);
        $tax = $vendor->serviceTax;
        if (!$tax) {
            return 0;
        }
        return $tax->type === 'percentage'
            ? ($total * $tax->value) / 100
            : $tax->value;
    }

    public function getGrandTotalAttribute()
    {
        return $this->total_amount + $this->tax_amount;
    }

    public function quoteEnq()
    {
        return $this->belongsTo(QuoteEnq::class, 'enq_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'vendor_id','id');
    }

    public function quoteServices()
    {
        return $this->hasMany(QuoteService::class, 'quote_id', 'id')->where(['is_deleted' => 0, "is_converted" => 0]);
    }
    public function quoteServicesAll()
    {
        return $this->hasMany(QuoteService::class, 'quote_id', 'id')->where('is_deleted', 0);
    }

    // public function mails()
    // {
    //     return $this->hasMany(EmailLog::class, 'table_id', 'id')->where('table_name', 'Quote')->where('is_deleted', 0)->latest('created_at');
    // }

    public function taxes()
    {
        return $this->morphMany(Tax::class, 'taxable', 'table_name', 'table_id');
    }

    public function fees()
    {
        return $this->morphMany(Fee::class, 'feesable', 'table_name', 'table_id');
    }

    public function tax()
    {
        return $this->morphOne(Tax::class, 'taxable', 'table_name', 'table_id')->where('is_deleted', 0);
    }

    // public function template()
    // {
    //     return $this->hasOne(EmailTemplate::class, 'id', 'template_id')->where('is_deleted', 0);
    // }

    public function notes()
    {
        return $this->hasMany(Note::class, 'table_id', 'id')->where('table_name', "quotes")->where('is_deleted', 0)->latest('updated_at');
    }

    public function unreadNotes()
    {
        return $this->hasMany(Note::class, 'table_id', 'id')->where('table_name', "quotes")->where('is_deleted', 0)->latest('updated_at');
    }

    public function sections(){
        return $this->hasMany(QuoteSection::class, 'quote_id', 'id')->where('is_deleted', 0);
    }
}
