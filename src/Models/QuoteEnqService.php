<?php


namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuoteEnqService extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['service'];
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
