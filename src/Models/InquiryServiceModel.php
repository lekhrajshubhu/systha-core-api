<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InquiryServiceModel extends Model
{
    use HasFactory;
    protected $table = 'quote_enq_services';
    protected $guarded = [];


    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(InquiryModel::class, 'enq_id', 'id');
    }
}
