<?php

namespace Systha\Core\Helpers;

use Systha\Core\Models\Vendor;
use Systha\Core\Models\SiteSetting;
use Systha\Core\Models\VendorDefault;
use Systha\Core\Models\VendorPaymentCredential;

class VendorDefaultHelper
{
    public static function vendorDefault($id, $property)
    {
        $vendor = Vendor::find($id);
        $default = VendorDefault::where('property', $property)
            ->where('vendor_id', $vendor->id)
            ->first();
        return $default ? $default->value : null;
    }


    public static function settingsValue($code)
    {
        $setting = SiteSetting::where('code', $code)->where('is_deleted', 0)->first();
        return $setting ? $setting->value : null;
    }

    public static function vendorPaymentCredential($vendor_id, $name)
    {
        $credential = VendorPaymentCredential::where('name', $name)
            ->where('vendor_id', $vendor_id)
            ->where('is_deleted', 0)
            ->where('status', 'publish')
            ->first();
        return $credential ?: null;
    }
}
