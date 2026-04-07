<?php

namespace Systha\Core\Models;

use Systha\Core\Models\Offer;
use Illuminate\Database\Eloquent\Model;

class VendorTemplate extends Model
{

    protected $guarded = [];
    protected $appends = ['base_url'];

    public function parentMenus()
    {
        return $this->hasMany(FrontendMenu::class)->where(['is_deleted' => 0, 'parent_id' => NULL]);
    }

    public function menus()
    {
        return $this->hasMany(FrontendMenu::class)->where(['is_deleted' => 0, 'is_active' => 1])->orderBy('seq_no', "ASC");
    }

    public function contents()
    {
        return $this->hasMany(StaticContent::class)->where('is_deleted', 0);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'vendor_template_id', 'id')->where('is_deleted', 0)->where('status', 'active');
    }

    public function productReviews()
    {
        return $this->hasMany(Review::class, 'template_id', 'id')->where(['table_name' => 'products', 'is_deleted' => 0]);
    }
    public function referenceTemplate()
    {
        return $this->belongsTo(self::class, 'ref_template_id');
    }
    public function parentTemplate()
    {
        return $this->belongsTo(self::class, 'id','ref_template_id');
    }

    public function getBaseUrlAttribute()
    {
        $host = $this->template_host ?? $this->host ?? null;
        if (!$host) {
            return null;
        }

        if (preg_match('#^https?://#i', $host)) {
            return $host;
        }

        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
        return $scheme . '://' . $host;
    }
}
