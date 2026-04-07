<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageService extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['service'];

    public function service()
    {
      return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function package()
    {
      return $this->belongsTo(Package::class, 'package_id', 'id');
    }
}
