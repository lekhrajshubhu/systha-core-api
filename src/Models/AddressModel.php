<?php

namespace Systha\Core\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AddressModel extends Model
{
    protected $table = "addresses";
    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
    ];
  
    public function addressable(): MorphTo
    {
        return $this->morphTo(null, 'table_name', 'table_id');
    }

}