<?php

namespace Systha\Core\Models;

use App\Model\File;
use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\Client;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'chat_messages' => static::class,
            'vendors' => Vendor::class,
            'clients' => Client::class,
            'service_providers'=> ServiceProvider::class,
        ]);
    }

    public function from()
    {
        return $this->morphTo(null, 'table_from', 'table_from_id');
    }

    public function to()
    {
        return $this->morphTo(null, 'table_to', 'table_to_id');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'attachable', 'table_name', 'table_id')->where('is_deleted', 0);
    }

    public function client()
    {
        return $this->morphTo(Client::class, 'table_from', 'table_from_id');
    }

    public function sentBy($model)
    {
        return $this->table_from == $model->getTable() && $this->table_from_id == $model->getKey();
    }
}
