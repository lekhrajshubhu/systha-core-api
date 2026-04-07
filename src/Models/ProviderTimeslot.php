<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderTimeslot extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = [
        'created_at', 'updated_at',
    ];
    protected $with=['providerDate'];

    public function providerDate(){
        return $this->belongsTo(ProviderDate::class);
    }
}
