<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $guarded = [];
    protected static $recordEvents = ['updated', 'created'];
}
