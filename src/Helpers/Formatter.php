<?php

namespace Systha\Core\Helpers;

use Systha\Core\Models\Vendor;
use Systha\Core\Models\SiteSetting;
use Systha\Core\Models\VendorDefault;
use Systha\Core\Models\DefaultCompany;
use Systha\Core\Models\VendorPaymentCredential;

class Formatter
{
    public static function formatPrice($amount): string
    {
        return '$' . number_format((float)$amount, 2);
    }

    public static function bladeDate($date)
    {
        if (!is_null($date)) {
            return date('jS M, Y', strtotime($date));
        }
        return null;
    }

    /**
     * Format amount to 2 decimal places
     *
     * @param float $amount
     * @return string
     */
    public static function amount_format(float $amount)
    {
        return number_format($amount, 2, '.', '');
    }

    // ---- Copied from CustomHelper.php ----

    public static function getVendorDefault($id, $property)
    {
        $vendor = Vendor::find($id);
        $default = VendorDefault::where('property', $property)
            ->where('vendor_id', $vendor->id)
            ->first();
        return $default ? $default->value : null;
    }

    public static function formatToTime($dateToFormat)
    {
        return date_create($dateToFormat)->format('h:i A');
    }

    public static function dateFormat($date)
    {
        return !is_null($date) ? date('Y-m-d', strtotime($date)) : null;
    }

    public static function viewBladeDate($date)
    {
        return !is_null($date) ? date('M j, Y', strtotime($date)) : null;
    }

    public static function priceFormat($price)
    {
        $price = $price ? "$" . number_format($price, 2) : "$" . number_format(0, 2);
        return $price;
    }

    public static function formatTime($dateToFormat)
    {
        return date_create($dateToFormat)->format('h:i A');
    }

    public static function formatToUsDate($dateToFormat)
    {
        return date_create($dateToFormat)->format(self::settingsValue('dateFormat') ?? 'm/d/Y');
    }

    public static function settingsValue($code)
    {
        $setting = SiteSetting::where('code', $code)->where('is_deleted', 0)->first();
        return $setting ? $setting->value : null;
    }

    public static function getVendorPaymentCredetial($vendor_id, $name)
    {
        $credential = VendorPaymentCredential::where('name', $name)
            ->where('vendor_id', $vendor_id)
            ->where('is_deleted', 0)
            ->where('status', 'publish')
            ->first();
        return $credential ?: null;
    }

    public static function defaultCompany($prop, $returnValue = null)
    {
        return self::getDefault($prop) ?: $returnValue;
    }

    public static function getDefault($property)
    {
        $default = DefaultCompany::where('property', $property)->first();
        return $default ? $default->value : null;
    }

    public static function saveUpdate($target, $data = [], $save = true)
    {
        foreach ($data as $key => $value) {
            $target->$key = $value;
        }
        $save = $save ? $target->save() : false;
        return $target;
    }

    public static function defaultCompanyName()
    {
        $company = DefaultCompany::where('property', "company_name")->first();
        return $company ? $company->value : "Shubhutech";
    }
}
