<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorModel extends Model
{
    use HasFactory;
    protected $table = 'vendors';
    protected $guarded = [];

    protected $appends = ['logo'];

     public function address()
    {
        return $this->morphOne(AddressModel::class, 'addressable', 'table_name', 'table_id');
    }

    public function getLogoAttribute(): string
    {
        if (empty($this->profile_pic)) {
            return asset('images/noimage.webp');
        }

        return route('media.show', ['filename' => $this->profile_pic]);
    }
}
