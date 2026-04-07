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

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $table = "site_settings";
    public $related;
    public $related_id;
    protected $guarded = [];
}
