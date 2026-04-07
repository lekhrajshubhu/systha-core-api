<?php
namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;



class QuoteSection extends Model
{
    protected $guarded = [];

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(QuoteSectionItem::class, 'section_id', 'id')->where('is_deleted', 0)->orderBy('sort_order', 'asc');
    }
}
