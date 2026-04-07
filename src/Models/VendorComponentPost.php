<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\VendorTemplate;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class VendorComponentPost extends Model
{
    use HasFactory;
    // protected $with = ['image'];
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([static::class => 'vendor_component_posts']);
    }
    // public function image()
    // {
    //     return $this->hasOne(EcommFile::class, 'table_id', 'id')->where('table_name', 'vendor_component_posts')->where('is_deleted', 0);
    // }

    public function page()
    {
        return $this->hasMany(FrontendMenu::class, 'page_id', 'id');
    }

    public function component()
    {
        return $this->belongsTo(VendorMenuComponent::class, 'component_id', 'id');
    }
}
