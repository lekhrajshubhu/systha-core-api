<?php


namespace Systha\Core\Helpers;

use Systha\Core\Models\DefaultCompany;

class DefaultCompanyHelper
{
    /**
     * Get a property value from the default_companies table.
     *
     * @param string $property
     * @return mixed|null
     */
    public static function getDefault(string $property)
    {
        $default = DefaultCompany::where('property', $property)->first();
        return $default ? $default->value : null;
    }

    /**
     * Get a property value or return a fallback value.
     *
     * @param string $prop
     * @param mixed $returnValue
     * @return mixed|null
     */
    public static function defaultCompany($prop, $returnValue = null)
    {
        return self::getDefault($prop) ?: $returnValue;
    }

    /**
     * Get the default company name.
     *
     * @return string
     */
    public static function defaultCompanyName()
    {
        $company = DefaultCompany::where('property', "company_name")->first();
        return $company ? $company->value : "Shubhutech";
    }
}