<?php

namespace Systha\Core\Http\Controllers\ServicePackage;

use Systha\Core\Models\Package;
use Systha\Core\Models\Service;
use Systha\Core\Http\Controllers\BaseController;

class ServicePackageController extends BaseController
{
    public function index()
    {
        $packages = Package::whereNotNull('package_name')
        ->where([
                "vendor_id"=>$this->vendor->id,
                "is_deleted"=>0
            ])
            ->get();
        $services = Service::whereNotNull('service_name')->get();

        // dd($packages);
        return view($this->viewPath.'::components.template.service_package',compact('packages','services'));
    }

    public function detail($id){
        dd($id);
    }
}
