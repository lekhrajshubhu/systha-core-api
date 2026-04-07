<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $table = "categories";
    protected $guarded = [];
    protected $with = ['child'];

    public function child()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->where('is_deleted', 0);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id')->where('is_deleted', 0);
    }
}
