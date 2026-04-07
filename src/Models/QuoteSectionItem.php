<?php
namespace Systha\Core\Models;



use Illuminate\Database\Eloquent\Model;

class QuoteSectionItem extends Model
{
    protected $guarded = [];
    public function section()
    {
        return $this->belongsTo(
            QuoteSection::class,
            'section_id',
            'id'
        );
    }

    // Parent item (if this is a child row)
    public function parent()
    {
        return $this->belongsTo(
            self::class,
            'parent_id',
            'id'
        );
    }

    // Child rows (sub-items / indented rows)
    public function children()
    {
        return $this->hasMany(
            self::class,
            'parent_id',
            'id'
        )->orderBy('sort_order');
    }
}
