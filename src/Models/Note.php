<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use App\Model\Lookup\Lookup;
use App\Model\File;
use App\User;

class Note extends Model
{
    protected $guarded = [];
    public function noteType()
    {
        return $this->belongsTo(Lookup::class, "note_type", "id")->where("is_deleted", 0);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'table_name', "table_name");
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'userc_id', 'id')
            ->where('is_deleted', 0);
    }
}
