<?php

namespace Systha\Core\Models;

use Systha\Salon\Models\Quote;
use Illuminate\Support\Facades\DB;
use Systha\Core\Traits\Chat;
use App\Model\NotificationDepartment;
use Systha\Core\Models\Company;

use Illuminate\Database\Eloquent\Model;

use Systha\Core\Models\Subscription;
use Systha\Core\Models\VendorTemplate;
use App\Model\UserNotificationDepartmentRule;
use Illuminate\Database\Eloquent\Relations\Relation;


class Vendor extends Model
{
    use Chat;
    protected $guarded = [];
    protected $with = ['defaultPaymentCredential', 'salesTax', 'address', 'contact'];
    protected $hidden = [
        'deleted_at',
        'is_active',
        'is_deleted',
        'menu_speciality',
        'occasion_speciality',
        'state',
        'status',
        'url',
        'userc_id',
        'userd_id',
        'useru_id',
        'updated_at',
        'created_at',
        'client_id'
    ];
    protected $appends = ["logo","banner"];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'vendors' => static::class,
        ]);
    }

    public function template()
    {
        return $this->hasOne(VendorTemplate::class, 'vendor_id', 'id');
    }

    public function getLogoAttribute()
    {
        if (!$this->template || !$this->profile_pic) {
            return asset('images/noimage.webp');
        }

        return route('media.show', ['filename' => $this->profile_pic]);
    }

     public function getBannerAttribute()
    {
        $bannerImage = $this->files()
            ->where('image_types', 'banner')
            ->latest('id')
            ->first();
        
        return route('media.show',['filename' => $bannerImage ? $bannerImage->file_name:'noimage.png']);
    }



    public function contact()
    {
        return $this->morphOne(Contact::class, 'contactable', 'table_name', 'table_id');
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable', 'table_name', 'table_id')->where('contacts.is_deleted', 0);
    }

    public function defaultPaymentCredential()
    {
        return $this->hasOne(VendorPaymentCredential::class, 'vendor_id', 'id')->where('vendor_payment_credentials.is_default', 1);
    }
    public function paymentCredential()
    {
        return $this->hasOne(VendorPaymentCredential::class, 'vendor_id', 'id')->where('vendor_payment_credentials.is_default', 1);
    }

    public function files()
    {
        return $this->morphMany(EcommFile::class, 'table_name', 'table_name', 'table_id')->where('ecomm_files.is_deleted', 0);
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable', 'table_name', 'table_id');
    }

    public function menus()
    {
        return $this->hasMany(FrontendMenu::class)->where('is_deleted', 0);
    }

    /**
     * @param STRING $medium (accepts two values is_sms | is_email | is_flash)
     */


    public function salesTax()
    {
        return $this->hasOne(TaxMaster::class, 'vendor_id')->where('tax_code', 'sales_tax')->where('is_deleted', 0);
    }
    /**
     * FrontEnd Menus
     */
    public function frontendMenus()
    {
        return $this->hasMany(FrontendMenu::class, 'vendor_id', 'id')->where(['is_active' => true, 'is_deleted' => false])->orderBy('seq_no');
    }

    public function templates()
    {
        return $this->hasMany(VendorTemplate::class)->where('is_deleted', 0);
    }

    public function getSenderEmail()
    {
        return $this->contact ? $this->contact->email : $this->url;
    }

    function chatUsersSelect()
    {
        return Client::query()
            ->select([DB::raw('concat(fname, " ", ifnull(lname, "")) as name'), 'clients.id', 'files.file_name as profile_pic'])
            ->leftJoin($this->archiveChatQuery(), function ($join) {
                $join->on('clients.id', 'ac.table_id');
                $join->where('ac.table_name', 'clients');
            })
            ->leftJoin('files', function ($join) {
                $join->on('files.table_id', 'clients.id')->where('files.table_name', 'clients')->where('files.is_deleted', 0)->where('files.type', 'profile');
            });
    }

    public function sliders()
    {
        return $this->morphMany(EcommFile::class, 'table', 'table_name', 'table_id')
            ->where(['is_deleted' => false, 'image_types' => 'slider']);
    }
    /**
     * Banner Image
     */
    public function bannerImage()
    {
        return $this->morphOne(EcommFile::class, 'table', 'table_name', 'table_id')
            ->where(['is_deleted' => false, 'image_types' => 'banner'])
            ->orderBy('id');
    }
    public function getBannerImageFileAttribute()
    {
        return $this->bannerImage ? $this->bannerImage->file_name : $this->profile_pic;
    }

    public function serviceCategories()
    {
        return $this->hasMany(ServiceCategory::class, 'vendor_id', 'id')->where('is_deleted', 0);
    }
    public function packageList()
    {
        return $this->hasMany(Package::class, 'vendor_id', 'id')->where('is_deleted', 0);
    }
}
