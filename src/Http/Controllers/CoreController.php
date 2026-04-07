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

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Systha\Core\Models\Vendor;

use App\Http\Controllers\Controller;;

use Illuminate\Support\Facades\Schema;
use Systha\Core\Models\VendorTemplate;

class CoreController extends Controller
{

    protected $template;
    protected $vendor;
    protected $menus;

    public function __construct()
    {

        $h = request()->getHttpHost();
        $host = $h;
        if (strpos($h, 'www.') !== false) {
            $indexof = strpos($h, 'www.') + 4;
            $host = substr($h, $indexof, strlen($h) - 1);
        }
        $hostColumn = Schema::hasColumn((new VendorTemplate)->getTable(), 'template_host') ? 'template_host' : 'host';
        $temp = VendorTemplate::where('is_active', 1)->where('is_deleted', 0)->where($hostColumn, $host)->first();
        if (!$temp) {
            return redirect('/admin');
        }

        $this->template = $temp;
        $this->vendor = $temp->vendor;
        $this->menus = $temp->menus;
    }

    public function getFile(Request $request)
    {
        //dd($this->template->storage_path) ;
        if (!$request->file) {
            return response()->file(public_path("/images/noimage.png"));
        }

        $location = $this->template->storage_path . '/' . $request->path . '/' . $request->file;

        if (file_exists($location)) {

            return response()->file($location);
        } else {

            return response()->file(public_path("/images/noimage.png"));
        }
    }

    public function postImage(Request $request, $fileName)
    {
        $location = $this->template->storage_path . '/CMS/post/' . $fileName;

        if (file_exists($location)) {

            return response()->file($location);
        } else {

            return response()->file(public_path("/images/noimage.webp"));
        }
    }
    public function packageImage(Request $request, $fileName)
    {
        // Prevent directory traversal
        $fileName = basename($fileName);


        $location = $this->template->storage_path . '/packages/images/' . $fileName;

        if (file_exists($location)) {
            return response()->file($location);
        }

        return response()->file(public_path('images/noimage.webp'));
    }
    public function serviceImage(Request $request, $fileName)
    {
        if ($fileName) {

            $location = $this->template->storage_path . '/services/images/' . $fileName;

            if (file_exists($location)) {

                return response()->file($location);
            } else {

                return response()->file(public_path("/images/noimage.webp"));
            }
        }
        return response()->file(public_path("/images/noimage.webp"));
    }
    public function logoImage(Request $requese)
    {

        $location = $this->template->storage_path . '/venndors/attachments/' . $this->vendor->profile_pic;

        if (file_exists($location)) {

            return response()->file($location);
        } else {

            return response()->file(public_path("/images/noimage.webp"));
        }
        return response()->file(public_path("/images/noimage.webp"));
    }
     public function getImage(Request $request, $fileName)
    {

        
        $location = $this->template->storage_path . '/venndors/attachments/' .$fileName;

        if (file_exists($location)) {

            return response()->file($location);
        } else {

            return response()->file(public_path("/images/noimage.webp"));
        }
        return response()->file(public_path("/images/noimage.webp"));
    }
    public function vendorLogo($file_name)
    {

        $path = $this->vendor->template->storage_path . '/venndors/attachments/' . $file_name;

        if (file_exists($path)) {
            return response()->file($path);
        }

        return response()->file(public_path('images/noimage.webp'));
    }
    public function avatar($file_name)
    {

        $path = $this->vendor->template->storage_path . '/client/profile/' . $file_name;

        if (file_exists($path)) {
            return response()->file($path);
        }

        return response()->file(public_path('images/default.jpg'));
    }
}
