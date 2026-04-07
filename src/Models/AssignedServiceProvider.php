<?php

namespace Systha\Core\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedServiceProvider extends Model
{
    use HasFactory;
    protected $guarded =[];

    protected $with = ['provider', 'service'];
    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id', 'id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
