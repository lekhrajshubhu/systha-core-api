<?php

namespace Systha\Core\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enq extends Model
{
    protected $guarded = [];

    protected $appends = ['enq_type','fullName'];

    public function enquirable(){
        return $this->morphTo();
    }

    public function getEnqTypeAttribute(){
        return str_replace('_', ' ', $this->type);
    }

    public function getFullNameAttribute()
    {
        return ucwords(join(" ", [$this->fname, $this->lname]));
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'table_id', 'id')->where('table_name', "Enq")->where('is_deleted', 0)->latest('updated_at');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function files()
    {
        return $this->morphMany(EcommFile::class, 'fileable', 'table_name', 'table_id')->where('type', 'enq');
    }
}
