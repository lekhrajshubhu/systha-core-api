<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderDate extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function slots(){
        return $this->hasMany(ProviderTimeslot::class,'provider_date_id');
    }
    public function provider(){
        return $this->belongsTo(ServiceProvider::class,'provider_id');
    }
}
