<?php

namespace Systha\Core\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class ServiceGroupModel extends Model
{
    protected $table = "service_groups";
    protected $guarded = [];
    protected $casts = [
        'meta' => 'array',
    ];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'service_groups' => static::class,
        ]);
    }

    public function getMorphClass()
    {
        return 'service_groups';
    }

    public function files()
    {
        return $this->morphMany(AttachmentUsageModel::class, 'usable')->orderBy('id', 'desc');
    }
    public function bannerUsage()
    {
        return $this->morphOne(AttachmentUsageModel::class, 'usable')
            ->orderByRaw("CASE WHEN JSON_EXTRACT(meta, '$.is_default') IN (true, 1, '1') THEN 1 ELSE 0 END DESC")
            ->orderBy('id', 'desc');
    }
}
