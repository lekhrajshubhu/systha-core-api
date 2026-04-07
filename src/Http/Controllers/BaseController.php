<?php

/**
 * THIS INTELLECTUAL PROPERTY IS COPYRIGHT Ⓒ 2020
 * SYSTHA TECH LLC. ALL RIGHT RESERVED
 * -----------------------------------------------------------
 * SALES@SYSTHATECH.COM
 * 512 903 2202
 * WWW.SYSTHATECH.COM
 * -----------------------------------------------------------
 */


namespace Systha\Core\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Package;
use Illuminate\Support\Facades\Schema;
use Systha\Core\Models\FrontendMenu;
use Systha\Core\Models\VendorDefault;
use Systha\Core\Models\VendorTemplate;

class BaseController extends Controller
{

    public $template, $vendor, $viewPath;
    public function __construct()
    {
        if (app()->runningInConsole()) {
            return;
        }
        $host = $this->resolveHost();
        $temp = $this->resolveTemplate($host);

        if ($temp) {
            $this->template = $temp;
            $this->vendor = Vendor::find($temp->vendor_id);


            $this->viewPath = $this->template->template_location;
             view()->share([
                'viewPath' => $this->viewPath,
            ]);
        } else {
            $this->viewPath = "golo";
            view()->share([
                'viewPath' => $this->viewPath,
            ]);
        }
    }

    private function resolveHost()
    {
        $host = request()->getHttpHost();
        if (strpos($host, 'www.') === 0) {
            return substr($host, 4);
        }
        return $host;
    }

    private function resolveTemplate($host)
    {
        $hostColumn = Schema::hasColumn('vendor_templates', 'template_host') ? 'template_host' : 'host';

        $template = DB::table('vendor_templates')
            ->where($hostColumn, $host)
            ->where([
                'is_active' => 1,
                'is_deleted' => 0,
                'is_default' => 1,
            ])
            ->first();
        return $template;
    }

}
