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

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';
    protected $guarded = [];

    public function getTempHtmlAttribute()
    {
        return html_entity_decode($this->attributes['temp_html']);
    }

    static function code($code) {
        $temp_code = Schema::hasColumn((new EmailTemplate())->getTable(), 'temp_code') ? 'temp_code' : 'code';
        return static::query()->where($temp_code, $code)->first();
    }
}
