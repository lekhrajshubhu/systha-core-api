<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AttachmentUsageModel extends Model
{
    protected $table = 'attachment_usages';
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function attachment() : BelongsTo
    {
        return $this->belongsTo(AttachmentModel::class);
    }

    public function usable() : MorphTo
    {
        return $this->morphTo();
    }
}
