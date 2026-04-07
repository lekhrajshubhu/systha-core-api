<?php

/**
 * THIS INTELLECTUAL PROPERTY IS COPYRIGHT Ⓒ 2020
 * SYSTHA TECH LLC. ALL RIGHT RESERVED
 * -----------------------------------------------------------
 * sales@systhatech.com
 * 512 903 2202
 * www.systhatech.com
 * -----------------------------------------------------------
 */

namespace Systha\Core\Models;

use Systha\Core\Traits\StoreAudit;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use StoreAudit;
    public $related;
    public $related_id;
    protected $guarded = [];

    public function fileType()
    {
        if (file_exists(storage_path($this->path))) {
            $extension =   mime_content_type(storage_path($this->path));
            $exp = explode("/", $extension);
            return $exp[0];
        }
        return "file";
    }

    public function fileable()
    {
        $this->morphTo();
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'table_id', 'id')
            ->where('table_name', 'Package_Thumb');
    }
}
