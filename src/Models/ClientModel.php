<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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


    public function inquiries(): HasMany
    {
        return $this->hasMany(InquiryModel::class);
    }
}
