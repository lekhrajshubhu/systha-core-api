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

namespace Systha\Core\Helpers;
use DateTime;
use Illuminate\Support\Carbon;

class CmsHelper
{

    public static function menu($menu)
    {
        $menu_sort = array();
        foreach ($menu as $k => $v) {
            if (!isset($v->parent_id)) {
                $menu_sort[$v->id] = $v;
            }
        }
        foreach ($menu as $k => $v) {
            if (isset($v->parent_id) && $v->parent_id != 0) {
                if (!isset($menu_sort[$v->parent_id]->child)) {
                    $menu_sort[$v->parent_id]->child = array();
                } else {
                    $a = $menu_sort[$v->parent_id]->child;
                    $a[$v->id] = $v;
                    $menu_sort[$v->parent_id]->child = $a;
                }
            }
        }

        return $menu_sort;

    }

    public static function menufooter($menu, $menu_type = false, $parent = false)
    {

        if ($menu_type == false) {
            $menu_type = "footer";
        }
        $menu_sort = array();
        foreach ($menu as $k => $v) {
            if ($v->location_footer == strtolower($menu_type)) {
                if (!isset($v->parent_id)) {
                    $menu_sort[$v->id] = $v;
                }
            }
        }
        if ($parent) {
            foreach ($menu as $k => $v) {
                if (isset($v->parent_id) && $v->parent_id != 0) {
                    if (!isset($menu_sort[$v->parent_id]->child)) {
                        $menu_sort[$v->parent_id]->child = array();
                    } else {
                        $a = $menu_sort[$v->parent_id]->child;
                        $a[$v->id] = $v;
                        $menu_sort[$v->parent_id]->child = $a;
                    }
                }
            }
        }

        return $menu_sort;

    }

    public static function menuheader($menu, $menu_type = false, $parent = false)
    {

        if ($menu_type == false) {
            $menu_type = "header";
        }
        $menu_sort = array();
        foreach ($menu as $k => $v) {
            // dd($v);
            if ($v->menu_location == strtolower($menu_type)) {
                if (!isset($v->parent_id) || $v->parent_id == 0) {
                    $menu_sort[$v->id] = $v;
                }
            }
        }
        if ($parent) {
            foreach ($menu as $k => $v) {
                if (isset($v->parent_id) || $v->parent_id != 0) {
                    if (!isset($menu_sort[$v->parent_id]->child)) {
                        $menu_sort[$v->parent_id]->child = array();
                    } else {
                        $a = $menu_sort[$v->parent_id]->child;
                        $a[$v->id] = $v;
                        $menu_sort[$v->parent_id]->child = $a;
                    }
                }
            }
        }

        return $menu_sort;
    }


    public static function menu_group_by_parent($menu, $group_key)
    {
        $result = array();
        foreach ($menu as $val) {
            if (array_key_exists($group_key, $val)) {
                $result[$val[$group_key]][] = $val;
            } else {
                $result[""][] = $val;
            }
        }
        return $result;
    }

    public static function menu_get_parent_group($menu, $menu_code)
    {

        // $menu = $menu->toArray();

        $result = array();
        foreach ($menu as $key => $val) {
            if ($val['parent_id'] == $menu_code) {
                array_push($result, $val);
            }
        }
        $re = array();
        $re['ref'] = $result;

        return $re;
    }

    public static function splitbykey($data, $key, $type)
    {

        $date = $data->toArray();
        $result = array();
        foreach ($data as $k => $v) {
            if ($v[$key] == $type) {
                array_push($result, $v);
            }
        }

        return $result;
    }


    public static function group_by_cato($menu, $group_key, $comp_type = false)
    {

        //  $menu = $menu->toArray();
        $result = array();
        foreach ($menu as $m => $z):
            $m = $z->posts->toArray();
            foreach ($m as $val):
                if (array_key_exists($group_key, $val)) {
                    $result[$z->component_name][$val[$group_key]][] = $val;
                } else {
                    $result[""][] = $val;
                }
            endforeach;
        endforeach;

        if ($comp_type && isset($result[$comp_type])) {
            return $result[$comp_type];
        }

        return $result;
    }
    public static function numberFormat($price)
    {
        // if null
        if (is_null($price)) {
            return "$00.00";
        } else {

            return "$" . number_format($price, 2);
        }
    }
    public static function dateFormat($dateString)
    {
        // if null
        // $dateString = "2024-05-27"; // Input date string
        $date = new DateTime($dateString); // Create DateTime object
        $formattedDate = $date->format('M d, Y'); // Format the date
        return $formattedDate;
    }
    public static function dateFormat2($dateString)
    {
        // if null
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);

        // Format the date into m/d/Y g:ia
        return $date->format('m/d/Y g:i A');
    }
    public static function formatDateTime($dateString)
    {
        // if null
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);

        // Format the date into m/d/Y g:ia
        return $date->format('m/d/Y g:i A');
    }
    public static function dateFormat1($dateString)
    {
        // Check if the date string is null or empty
        if (empty($dateString)) {
            return '-'; // Return a dash if the date is null or empty
        }

        // Try to create a DateTime object, catch invalid date formats
        try {
            $date = new DateTime($dateString); // Create DateTime object
            $formattedDate = $date->format('m/d/Y'); // Format the date
            return $formattedDate;
        } catch (\Exception $e) {
            return '-'; // Return a dash if the date is invalid
        }
    }

    public static function shortText($string, $length)
    {
        if (strlen($string) <= $length) {
            echo $string;
        } else {
            return substr($string, 0, $length) . '...';
        }
    }
    public static function formatTime($time)
    {
        // Check if the time is null or empty
        if (empty($time)) {
            return "-"; // Return error message if time is empty or null
        }

        // Create a DateTime object from the input time (H:i:s format)
        $dateTime = DateTime::createFromFormat('H:i:s', $time);

        // Check if the time is valid
        if (!$dateTime || $dateTime->format('H:i:s') !== $time) {
            return "-"; // Return error message if the time is invalid
        }

        // Format the time with AM/PM
        return $dateTime->format('g:i A');
    }

    public static function formatTimestamp($timestamp)
    {
        // Check if the input is a valid integer timestamp
        if (!is_numeric($timestamp) || (int) $timestamp != $timestamp) {
            return "n/a";
        }

        // Format the date
        $formattedDate = date('m/d/Y', $timestamp);

        return $formattedDate;
    }

    /**
     * Get the time ago in a human-readable format
     *
     * @param string $datetime
     * @return string
     */
    public static function timeAgo($datetime)
    {
        $currentTime = Carbon::now(); // current time (you can use Carbon's now())
        $createdAt = Carbon::parse($datetime); // convert input datetime to Carbon instance
        $difference = $currentTime->diffInMinutes($createdAt); // difference in minutes

        if ($difference < 1) {
            return "just now";
        }

        if ($difference < 60) {
            return "{$difference} minute" . ($difference > 1 ? 's' : '') . " ago";
        }

        $differenceInHours = $currentTime->diffInHours($createdAt);
        if ($differenceInHours < 24) {
            return "{$differenceInHours} hour" . ($differenceInHours > 1 ? 's' : '') . " ago";
        }

        $differenceInDays = $currentTime->diffInDays($createdAt);
        if ($differenceInDays < 7) {
            return "{$differenceInDays} day" . ($differenceInDays > 1 ? 's' : '') . " ago";
        }

        $differenceInWeeks = $currentTime->diffInWeeks($createdAt);
        if ($differenceInWeeks < 4) {
            return "{$differenceInWeeks} week" . ($differenceInWeeks > 1 ? 's' : '') . " ago";
        }

        $differenceInMonths = $currentTime->diffInMonths($createdAt);
        if ($differenceInMonths < 12) {
            return "{$differenceInMonths} month" . ($differenceInMonths > 1 ? 's' : '') . " ago";
        }

        $differenceInYears = $currentTime->diffInYears($createdAt);
        return "{$differenceInYears} year" . ($differenceInYears > 1 ? 's' : '') . " ago";
    }
}


