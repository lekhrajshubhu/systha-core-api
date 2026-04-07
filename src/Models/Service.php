<?php

namespace Systha\Core\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;



class Service extends Model
{

    protected $guarded = [];
    protected $with = ['category', 'providers'];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'services' => static::class
        ]);
    }
    public function category()
    {
        return $this->hasOne(ServiceCategory::class, 'id', 'service_category_id')->where('is_deleted', 0);
    }
    public function images()
    {
        return $this->morphMany(File::class, 'fileable', 'table_name', 'table_id');
    }
    public function providers()
    {
        return $this->belongsToMany(ServiceProvider::class, 'assigned_service_providers', 'service_id')->where('assigned_service_providers.is_deleted', 0);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function parent(){
        return $this->belongsTo(self::class, 'parent_id')
            ->select('id', 'service_name as name', 'question_text', 'is_active', 'unit_type', 'input_label', 'parent_id', 'is_multi', 'service_category_id', 'vendor_id', 'price');
    }
    
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->select('id', 'service_name as name', 'question_text', 'is_active', 'unit_type', 'input_label', 'parent_id', 'is_multi', 'service_category_id', 'vendor_id', 'price')
            ->with('children');
    }
}
