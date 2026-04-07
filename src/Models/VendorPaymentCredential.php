<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPaymentCredential extends Model
{

    protected $guarded = [];
    protected $hidden = [
        'created_at',
        'deleted_at',
        'is_active',
        'is_deleted',
        'is_button',
        'userc_id',
        'useru_id',
        'userd_id',
        'val2',
        'val3',
        'val4',
        'val5',
        'val6',
	];
}
