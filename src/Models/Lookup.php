<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Lookup extends Model
{
     
    protected $guarded = [];
    protected $casts = [
        'features' => 'array'
    ];
}
