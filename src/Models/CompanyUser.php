<?php

namespace Systha\Core\Models;



use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    protected $table = 'company_users';

    protected $guarded = ['id'];
    protected $casts = [
        'company_id' => 'integer',
        'client_id' => 'integer',
        'user_id' => 'integer',
        'is_deleted' => 'boolean',
        'is_primary' => 'boolean',
        'last_login_at' => 'datetime',
    ];
    protected $fillable = [
        'company_id',
        'client_id',
        'user_id',
        'role_id',
        'username',
        'email',
        'password',
        'remember_token',
        'status',
        'is_primary',
        'last_login_at',
        'is_deleted',
        'deleted_at',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $companyUser) {
            if (!empty($companyUser->username)) {
                return;
            }

            $base = '';

            if (!empty($companyUser->fname)) {
                // Use provided first name when available
                $base = preg_replace('/[^A-Z0-9]/i', '', $companyUser->fname);
            } elseif (!empty($companyUser->email)) {
                // Fall back to email prefix
                $base = strtok($companyUser->email, '@') ?: '';
                $base = preg_replace('/[^A-Z0-9]/i', '', $base);
            }

            // Drop confusing characters
            $base = str_replace(['1', 'I'], '', strtoupper($base));

            // Ensure we have something to work with
            if ($base === '') {
                $base = 'USER';
            }

            // Trim base so total length (base + random) stays at 8
            $maxBaseLen = 8 - 4; // reserve 4 for random suffix
            $base = substr($base, 0, $maxBaseLen);

            // Append 4-char random, uppercase, excluding 1 and I
            $alphabet = 'ABCDEFGHJKLMNOPQRSTUVWXYZ023456789'; // removed I and 1
            $len = strlen($alphabet) - 1;
            $random = '';
            for ($i = 0; $i < 4; $i++) {
                $random .= $alphabet[random_int(0, $len)];
            }

            // Pad base if it became shorter than 4, to ensure total 8 chars
            if (strlen($base) < $maxBaseLen) {
                while (strlen($base) < $maxBaseLen) {
                    $base .= $alphabet[random_int(0, $len)];
                }
            }

            $companyUser->username = strtoupper(substr($base . $random, 0, 8));
        });
    }
}
