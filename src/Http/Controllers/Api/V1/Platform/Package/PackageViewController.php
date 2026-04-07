<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Package;



use Illuminate\Routing\Controller;

/**
 * @group Platform
 * @subgroup Packages
 */
class PackageViewController extends Controller
{

    protected $storagePath = "/Users/lekhraj/Herd/blank8/storage";

     public function packageImage($image)
    {
        $location = ($this->storagePath . '/packages/images/' . $image);
        if (file_exists($location)) {
            return response()->file($location);
        } else {
            return response()->file(public_path("/images/noimage.png"));
        }
    }
}

