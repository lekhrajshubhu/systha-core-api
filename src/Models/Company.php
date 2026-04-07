<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;


class Company extends Model
{
    protected $table = 'companies';
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'companies' => static::class
        ]);

        static::creating(function (self $company) {
            if (empty($company->code)) {
                do {
                    $code = (string) Str::uuid();
                } while (static::where('code', $code)->exists());

                $company->code = $code;
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(Client::class, 'company_users', 'company_id', 'client_id')
            ->where('company_users.is_deleted', 0);
    }
    public function primaryContact()
    {
        return $this->hasOneThrough(
            Client::class,
            CompanyUser::class,
            'company_id',   // Foreign key on company_users
            'id',           // Foreign key on clients
            'id',           // Local key on companies
            'client_id'     // Local key on company_users
        )->where('company_users.is_deleted', 0)
            ->where('company_users.is_primary', 1);
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(AddressModel::class, 'addressable', 'table_name', 'table_id')->where('is_deleted', 0);
    }

    public function defaultAddress(): MorphOne
    {
        return $this->morphOne(AddressModel::class, 'addressable', 'table_name', 'table_id')
            ->where('is_default', true);
    }


    public function attachmentUsages(): MorphMany
    {
        return $this->morphMany(AttachmentUsageModel::class, 'usable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(AttachmentUsageModel::class, 'usable')
            ->with('attachment');
    }

    public function primaryLogo()
    {
        return $this->morphOne(AttachmentUsageModel::class, 'usable')
            ->where('meta->type', 'logo')
            ->where('meta->is_primary', true)
            ->with('attachment');
    }

}
