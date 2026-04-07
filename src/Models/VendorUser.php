<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Systha\vendorpackage\Models\Vendor;

class VendorUser extends Model
{
    protected $table = 'vendor_users';

    protected $guarded = ['id'];
    protected $casts = [
        'vendor_id' => 'integer',
        'user_id' => 'integer',
        'is_deleted' => 'boolean',
    ];
    protected $fillable = [
        'vendor_id',
        'user_id',
        'role_id',
        'username',
        'email',
        'password',
        'remember_token',
        'status',
        'last_login_at',
        'is_deleted',
        'deleted_at',
    ];

    public function vendor()
    {
        return $this->belongsTo(VendorModel::class, 'vendor_id');
    }
}
