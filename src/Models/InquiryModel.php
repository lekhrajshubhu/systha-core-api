<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Carbon\CarbonInterface;


class InquiryModel extends Model
{
    use HasFactory;
    protected $table = 'quote_enqs';
    protected $guarded = [];
    
    protected $casts = [
        'inquiry_info' => 'array',
        'reviewable_history' => 'array'
    ];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'quote_enqs' => static::class,
        ]);

        static::creating(function ($quoteEnq) {
            $quoteEnq->enq_no = self::generateEnqNo();
        });
    }
    public static function generateEnqNo(?CarbonInterface $date = null): string
    {
        $datePart = ($date ?? now())->format('ymd');
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ234567890';
        $suffix = '';

        for ($i = 0; $i < 4; $i++) {
            $suffix .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return 'INQ-' . $datePart . '-' . $suffix;
    }


    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class);
    }
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorModel::class);
    }

    public function inquiryServiceList(): HasMany
    {
        return $this->hasMany(InquiryServiceModel::class, 'enq_id', 'id');
    }

    public function inquiryService(): HasOne
    {
        return $this->hasOne(InquiryServiceModel::class, 'enq_id', 'id')->latestOfMany();
    }

    public function serviceAddress(): MorphOne
    {
        return $this->morphOne(AddressModel::class, 'addressable', 'table_name', 'table_id');
            // ->where('address_type', 'inquiries');
    }
    public function quotes(): HasMany
    {
        return $this->hasMany(QuoteModel::class, 'enq_id', 'id');
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
}
