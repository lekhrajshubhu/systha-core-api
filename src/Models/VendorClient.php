<?php

namespace Systha\Core\Models;

use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Systha\Core\Models\Client;
use Systha\Core\Models\Vendor;

class VendorClient extends Authenticatable implements JWTSubject
{
    protected $table = 'vendor_client';
    protected $guarded = [];

     protected $hidden = [
        'password',
	];
     protected static function booted()
    {
        static::creating(function (self $vendorClient) {
            if (empty($vendorClient->uuid)) {
                $vendorClient->uuid = (string) Str::uuid();
            }
        });
    }

     public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
