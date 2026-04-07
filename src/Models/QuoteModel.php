<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;


class QuoteModel extends Model
{
    use HasFactory;
    protected $table = 'quotes';
    protected $guarded = [];

     protected static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'quotes' => static::class
        ]);
    }

    public function inquiry(){
        return $this->belongsTo(InquiryModel::class, 'enq_id', 'id');
    }
    public function vendor(){
        return $this->belongsTo(VendorModel::class, 'vendor_id', 'id');
    }
    public function client(){
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }

     public function sections(){
        return $this->hasMany(QuoteSection::class, 'quote_id', 'id')->where('is_deleted', 0);
    }

}
