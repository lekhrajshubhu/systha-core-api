<?php

namespace Systha\Core\Http\Controllers\File;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected $storagePath = "/Users/lekhraj/Herd/blank8/storage";

    private function getStoragePath(string $relativePath): string
    {
        $base = rtrim($this->storagePath, DIRECTORY_SEPARATOR);
        $relative = ltrim($relativePath, DIRECTORY_SEPARATOR);

        return $base . DIRECTORY_SEPARATOR . $relative;
    }
    //  previewImage
    public function showImage(Request $request, $fileName)
    {
  
        if (!$fileName) {
            return response()->file(public_path("/websites/golo/assets/images/noimage.png"));
        }
        $location = 'venndors/attachments/' . $fileName;
        $fullPath = $this->getStoragePath($location);
        if (file_exists($fullPath)) {
            return response()->file($fullPath);
        } else {

            return response()->file(public_path("/websites/golo/assets/images/noimage.png"));
        }
    }
}
