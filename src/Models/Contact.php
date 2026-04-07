<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Systha\Core\Models\LocationLog;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Systha\Core\Notification\ResetVendorPasswordNotification;

class Contact extends Authenticatable implements JWTSubject
{
    use Notifiable;

	protected $guarded = [];

	protected $guard = 'contact';
	protected $appends = ['fullName'];
    protected $hidden = [
        'password', 'remember_token',
	];

    public static function boot() {
        parent::boot();
        Relation::morphMap([
            'clients' => Client::class,
            'vendors' => Vendor::class,
            'service_providers' => ServiceProvider::class,
        ]);
    }


     public function getLogoAttribute()
    {
        if (!$this->template || !$this->profile_pic) {
            return asset('images/noimage.webp');
        }

        return route('user.avatar', ['file_name' => $this->profile_pic]);
    }

    public function sendPasswordResetNotification($token)
    {
        // $this->notify(new ResetVendorPasswordNotification($token));
    }

	public function getVendorAttribute() {
		if($vendor = Vendor::find($this->table_id)) {
			return $vendor;
		}
		return null;
	}
    public function getFullNameAttribute()
    {
        return ucwords(join(" ", [$this->fname, $this->mname, $this->lname]));
    }

    public function owner()
    {
        return $this->morphTo('owner', 'table_name', 'table_id');
    }

    public function user() {
        switch($this->attributes['table_name']) {
            case 'clients' : return Client::query()->find($this->attributes['table_id']);
            case 'vendors' : return Vendor::query()->find($this->attributes['table_id']);
            case 'service_providers' : return ServiceProvider::query()->find($this->attributes['table_id']);
        }
    }
    public function profile() {
        switch($this->attributes['table_name']) {
            case 'clients' : return Client::query()->find($this->attributes['table_id']);
            case 'vendors' : return Vendor::query()->find($this->attributes['table_id']);
            case 'service_providers' : return ServiceProvider::query()->find($this->attributes['table_id']);
        }
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
    public function activeLocation(){
        // return null;
        // return LocationLog::where([
        //     "table_name" => $this->attributes['table_name'],
        //     "table_id" => $this->table_id,
        //     "is_active" =>1,
        // ])
        // ->orderBy('id','desc')
        // ->first();
    }
}
