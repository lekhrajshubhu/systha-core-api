<?php

namespace Systha\Core\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AddressModel extends Model
{
    protected $table = "addresses";
    protected $guarded = [];
  
    public function addressable(): MorphTo
    {
        return $this->morphTo(null, 'table_name', 'table_id');
    }

}