<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversationMember extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user(){
        return $this->morphTo(__FUNCTION__,"table_name","table_id");
    }
}
