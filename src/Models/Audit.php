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

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Audit extends Model
{
    protected $guarded = [];
    protected $with = ['auditted_by'];

    public static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'audits' => static::class
        ]);
    }
    public function auditted_by()
    {
        return $this->belongsTo(User::class, 'userc_id', 'id');
    }

    public function issue(){

        return $this->belongsTo('App\Model\Publication\Issue','table_id','id')
            ->where('issues.is_deleted',0);

    }
}
