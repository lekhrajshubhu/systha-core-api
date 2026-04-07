<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuoteService extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['service'];
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
