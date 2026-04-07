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

namespace Systha\Core\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;

class InstallCommand extends Command {

    protected $name = "vendor:install";
    protected $description = 'Install Pestcontrol frontend vendor';


    public function handle(FileSystem $files){
        $this->files = $files;

        $this->handleBinding();
        $this->handleIndexRoute();
        $this->info('Pest Vendor Install Sucessfully');
    }

    public function handleBinding()
    {
        $path = base_path()."/app/Http/Kernel.php";
        $kernal_path = $this->files->get($path);

        if(!str_contains($kernal_path,"bindings")){
            $replace_text = "verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,";
            $append_text = $replace_text."
            "."'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,";
            $kernal_path= str_replace("verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,",$append_text,$kernal_path);
            $this->files->put($path,$kernal_path);
        }
    }

    public function handleIndexRoute($path=false){
        $indexRoutePath = base_path().'/routes/web.php';
        $route_content = $this->files->get($indexRoutePath);
        if(str_contains($route_content,"'/'")){
            $replaceRoute = "'/test'";
            $newRoute = str_replace("'/'",$replaceRoute,$route_content);
            // dd($newRoute);
            $this->files->put($indexRoutePath,$newRoute);
        }
    }
}
