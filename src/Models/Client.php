<?php

namespace Systha\Core\Models;

use App\Model\NotificationDepartment;
use Systha\Core\Traits\Chat;
use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\Vendor;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Systha\Core\Traits\StoreAudit;
use App\Model\UserNotificationDepartmentRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PDO;

class Client extends Authenticatable implements JWTSubject
{
    use Notifiable,  StoreAudit, Chat;
    protected $guarded = [];
    protected $with = ['contact', 'image', 'address', 'stripeProfile'];
    protected $appends = ['isLatest', 'fullName', 'avatar'];
    protected $hidden = ['password'];
    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'clients' => static::class
        ]);
    }
    public function stripeProfile()
    {
        return $this->hasOne(StripeCustomer::class, 'client_id', 'id');
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getAvatarAttribute()
    {
        if (!$this->template || !$this->image) {
            return asset('images/noimage.webp');
        }

        return route('user.avatar', ['file_name' => $this->image->file_name]);
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

    // public function company()
    // {
    //     return $this->hasOne(Company::class, 'id', 'company_id')->where('is_deleted', 0);
    // }

    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id')->where('is_deleted', 0);
    }

    public function fullname($mname = true)
    {
        if ($mname) {
            return ucwords(join(" " . $this->mname . " ", [$this->fname, $this->lname]));
        }
        return ucwords(join(" ", [$this->fname, $this->lname]));
    }

    public function firstandlastname()
    {
        return ucwords(join(" ", [$this->fname, $this->lname]));
    }

    public function getFirstNameAttribute()
    {
        return $this->fname;
    }

    public function getMiddleNameAttribute()
    {
        return $this->mname;
    }

    public function getLastNameAttribute()
    {
        return $this->lname;
    }

    public function getPersonalEmailAttribute()
    {
        return $this->email;
    }

    public function getFnameAttribute($value)
    {
        return ucfirst($value);
    }

    public function getLnameAttribute($value)
    {
        return ucfirst($value);
    }

    public function getMobileNoAttribute()
    {
        return $this->phone_no;
    }

    public function image()
    {
        return $this->morphOne(EcommFile::class, 'fileable', 'table_name', 'table_id')->where('type', 'profile');
    }

    public function attachments()
    {
        return $this->morphMany(EcommFile::class, 'fileable', 'table_name', 'table_id')->where('type', 'attachment');
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable', 'table_name', 'table_id')->where('is_deleted', 0)->latest('updated_at');
    }


    public function basicAddr()
    {
        return $this->morphOne(Address::class, 'table_name', 'table_name', 'table_id')->where('is_deleted', 0);
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable', 'table_name', 'table_id')->where('is_deleted', 0);
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable', 'table_name', 'table_id')
            ->select([
                'id',
                'table_name',
                'table_id',
                'add1',
                'add2',
                'city',
                'state',
                'zip',
                'country_code as country'
            ])
            ->where('address_type', 'primary');
    }



    // public function contact()
    // {
    //     return $this->morphOne(Contact::class, 'contactable', 'table_name', 'table_id')->where('is_deleted', 0);
    // }
    public function contact()
    {
        return $this->morphOne(Contact::class, 'contactable', 'table_name', 'table_id')
            ->select('id', 'fname', 'lname', 'vendor_code', 'contact_type', 'email', 'mobile_no', 'phone_no');
    }
    // public function getAvatarAttribute()
    // {
    //     if ($this->contact && $this->contact->avatar) {
    //         return $this->contact->avatar;
    //     }

    //     return asset('images/default.jpg');
    // }

    public function comm_preferences()
    {
        return $this->belongsToMany('App\Model\Lookup\Lookup', 'client_communication_preferences', 'client_id', 'comm_preference_id')
            ->where('lookups.is_deleted', 0);
    }

    public function getIsLatestAttribute()
    {
        if ($this->id == Client::latest('updated_at')->first()->id) {
            return true;
        }
        return false;
    }

    public function getFullNameAttribute()
    {
        return ucwords(join(" ", [$this->fname, $this->lname]));
    }

    public function paymentProfiles()
    {
        return $this->hasMany(CustomerPaymentProfile::class);
    }

    // /**
    //  * @param STRING $medium (accepts two values is_sms | is_email | is_flash)
    //  */
    // public function notification_medium($medium = '', $code)
    // {
    //     if (!in_array($medium, ['is_sms', 'is_email', 'is_flash'])) return false;
    //     if (is_string($code)) {
    //         $department = NotificationDepartment::where('code', $code)->where('is_deleted', 0)->where('state', 'publish')->orderByDesc('created_at')->first();
    //     } else {
    //         $department = $code;
    //     }
    //     if (!$department) return false;
    //     return UserNotificationDepartmentRule::where('client_id', $this->id)->where('notif_dep_id', $department->id)
    //         ->where($medium, 1)->exists();
    // }

    // public function giftcard()
    // {
    //     return $this->hasMany(GiftCard::class)->where('is_deleted', 0);
    // }

    public function appointments()
    {
        return $this->hasMany(Appointment::class)->where(['is_deleted' => 0]);
    }

    public function getSenderEmail()
    {
        return $this->contact->email ?: $this->email;
    }

    function chatUsersSelect()
    {
        return Vendor::query()
            ->select(['name', 'id', 'profile_pic'])
            ->leftJoin($this->archiveChatQuery(), function ($join) {
                $join->on('vendors.id', 'ac.table_id');
                $join->where('ac.table_name', 'vendors');
            })
            ->with('contact');
    }
    // public function leaves()
    // {
    //     return $this->hasMany(LeaveApplication::class, 'client_id')->where(['is_deleted' => 0, 'state' => 'publish']);
    // }


    // public function services()
    // {
    //     return $this->belongsToMany(Service::class, 'assigned_service_staffs', 'client_id');
    // }

    // public function enquiries()
    // {
    //     return $this->morphMany(Enq::class, 'enquirable', 'table_name', 'table_id');
    // }
}
