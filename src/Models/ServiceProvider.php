<?php

namespace Systha\Core\Models;


use App\Model\Lookup\Lookup;
use Illuminate\Support\Facades\DB;
use Systha\Core\Traits\Chat;
use Systha\Core\Models\Skill;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Address;
use Systha\Core\Models\Contact;
use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\EcommFile;
use Systha\Subscription\Model\Subscription;
use Systha\Core\Models\PreferredLocation;
use Systha\Core\Models\ServiceProviderExperience;

class ServiceProvider extends Model {
    use Chat;
    protected $guarded = [];
    protected $appends = ['name'];
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function getNameAttribute(){
        return $this->fname." ".$this->lname;
    }
    public function attachments()
    {
        return $this->morphMany(EcommFile::class, 'fileable', 'table_name', 'table_id')->where('type', 'attachment');
    }
    // public function subscription()
    // {
    //     return $this->morphOne(Subscription::class, 'subscribable', 'table_name', 'table_id')->where('is_active', 1)->where('is_deleted', 0);
    // }
    public function address()
    {
        return $this->morphOne(Address::class, 'table_name', 'table_name', 'table_id')->where('is_deleted', 0);
    }
    // public function preferredLocation()
    // {
    //     return $this->hasMany(PreferredLocation::class, 'service_provider_id', 'id')->where('is_deleted', 0);
    // }
    // public function education()
    // {
    //     return $this->hasMany(Lookup::class, 'id', 'education_level');
    // }
    // public function workExperience()
    // {
    //     return $this->hasMany(ServiceProviderExperience::class, 'provider_id', 'id')->where('is_deleted', 0)->orderByDesc('created_at');
    // }
    public function contact()
    {
        return $this->morphOne(Contact::class, 'contactable', 'table_name', 'table_id')->where('is_deleted', 0);
    }
    public function files()
    {
        return $this->morphMany(EcommFile::class, 'table_name', 'table_name', 'table_id')->where('ecomm_files.is_deleted', 0);
    }
    // public function skills(){
    //     return $this->hasMany(Skill::class, 'service_provider_id', 'id')->orderByDesc('created_at');
    //     //
    // }
    // public function jobApplied(){
    //     return $this->hasMany(JobApplication::class,'technician_id');
    // }
    function chatUsersSelect()
    {
        return Vendor::query()
            ->select([DB::raw('concat(fname, " ", ifnull(lname, "")) as name'), 'clients.id', 'files.file_name as profile_pic'])
            ->leftJoin($this->archiveChatQuery(), function($join) {
                $join->on('clients.id', 'ac.table_id');
                $join->where('ac.table_name', 'clients');
            })
            ->leftJoin('files', function($join) {
                $join->on('files.table_id', 'clients.id')->where('files.table_name', 'clients')->where('files.is_deleted', 0)->where('files.type', 'profile');
            });
    }

    public function services(){
        return $this->belongsToMany(Service::class,'assigned_service_providers','service_provider_id');
    }
}
