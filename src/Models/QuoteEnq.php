<?php

namespace Systha\Core\Models;

use App\User;
use Illuminate\Support\Str;
use Systha\Core\Models\Note;
use Systha\Core\Models\Client;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\EcommFile;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class QuoteEnq extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['category', 'client', 'vendor'];
    protected $append = ['prefered_date','prefered_time'];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'quote_enqs' => static::class,
        ]);

        static::creating(function ($quoteEnq) {
            // Generate a unique enq_no
            $quoteEnq->enq_no = self::generateEnqNo();
        });
    }
    public static function generateEnqNo()
    {
        return 'ENQ' . strtoupper(Str::random(4));
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id')->where('is_deleted', 0);
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id')->where('is_deleted', 0);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'enq_id', 'id')->where('is_deleted', 0)->latest('created_at');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'table_id', 'id')->where('table_name', "quot_enqs")->where('is_deleted', 0)->latest('updated_at');
    }

    public function files()
    {
        return $this->morphMany(EcommFile::class, 'table_name', 'table_name', 'table_id')->where('ecomm_files.is_deleted', 0);
    }

    public function attachmentUsages(): MorphMany
    {
        return $this->morphMany(AttachmentUsageModel::class, 'usable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(AttachmentUsageModel::class, 'usable')
            ->with('attachment');
    }

    public function services()
    {
        return $this->hasMany(QuoteEnqService::class, 'enq_id', 'id')->where('is_deleted', 0);
    }

    public function inquiryService(): HasOne
    {
        return $this->hasOne(QuoteEnqService::class, 'enq_id', 'id')
            ->where('is_deleted', 0)
            ->latestOfMany();
    }

    public function getPreferedDateAttribute(){
        if(!empty($this->attributes['preferred_datetime'])){
            return explode(' ',$this->attributes['preferred_datetime'])[0];
        }
        return null;
    }

      public function enqServices()
    {
        return $this->hasMany(QuoteEnqService::class, 'enq_id', 'id')->where('is_deleted', 0);
    }

    
    public function getPreferedTimeAttribute(){
        if(!empty($this->attributes['preferred_datetime'])){
            return explode(' ',$this->attributes['preferred_datetime'])[1];
        }
        return null;
    }

}
