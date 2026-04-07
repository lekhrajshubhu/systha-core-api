<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ClientModel extends Model
{
    use HasFactory;
    protected $table = 'clients';
    protected $guarded = [];
    protected $appends = ['avatar'];

    public function getMorphClass()
    {
        return 'clients';
    }

    public function getAvatarAttribute()
    {
        if (!$this->template || !$this->image) {
            return asset('images/noimage.webp');
        }

        return route('user.avatar', ['file_name' => $this->image->file_name]);
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

    public function address()
    {
        return $this->morphOne(AddressModel::class, 'addressable', 'table_name', 'table_id')
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
            ->where('is_default', true);
    }



    public function inquiries(): HasMany
    {
        return $this->hasMany(InquiryModel::class);
    }
}
