<?php


namespace Systha\Core\Services;

use Illuminate\Support\Facades\Schema;
use Systha\Core\Models\VendorTemplate;



class BaseService{


    public $template;
    public $vendor;
    public $menus;
    public function __construct()
    {

        $h = request()->getHttpHost();
        $host = $h;
        if (strpos($h, 'www.') !== false) {
            $indexof = strpos($h, 'www.') + 4;
            $host = substr($h, $indexof, strlen($h) - 1);
        }
        $hostColumn = Schema::hasColumn((new VendorTemplate)->getTable(), 'template_host') ? 'template_host' : 'host';
        $temp = VendorTemplate::where('is_active', 1)->where('is_deleted', 0)->where($hostColumn,$host)->first();
        if (!$temp) {
            return redirect('/admin');
        }

        $this->template = $temp;
        $this->vendor = $temp->vendor;
        $this->menus = $temp->menus;
    }
}
