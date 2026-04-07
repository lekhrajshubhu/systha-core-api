<?php

namespace Systha\Core\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class EcommFile extends Model
{
    protected $guarded = [];
    protected $table = 'ecomm_files';

    public function fileable(){
        $this->morphTo(__FUNCTION__,'table_name','table_id');
    }


}
