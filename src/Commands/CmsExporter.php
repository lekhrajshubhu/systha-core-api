<?php

namespace Systha\Core\Commands;

use ZipArchive;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Systha\Core\Models\EcommFile;
use Symfony\Component\Console\Input\InputOption;
use Systha\Core\Models\{Vendor, VendorTemplate, FrontendMenu};

class CmsExporter extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'cms:export {templateId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';
    protected $vendorId;
    protected $templateId = null;
    protected $zipArchive = null;

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production', null],
        ];
    }
    public function __construct()
    {
        parent::__construct();
        $this->start = now();
        //ask vendor

        // ask template if multiple template of given venodor

    }
    /**
     * Execute the console command.
     *
     * @param -null
     *
     * @return void
     */
    public function handle()
    {
        try {
            $hostColumn = Schema::hasColumn((new VendorTemplate)->getTable(), 'template_host') ? 'template_host' : 'host';
            if (!$this->argument('templateId')) {
                $vendors = Vendor::where(['state' => 'publish', 'is_deleted' => 0])->get();

                if ($vendors->isEmpty()) {
                    exit('No vendor found');
                } else {
                    $this->info('List of Vendors');
                    foreach ($vendors as $vendor) {
                        $this->info("ID: {$vendor->id}, Name: {$vendor->name}");
                    }
                }

                $this->vendorId = $this->ask('Please enter vendor Id');
                $vendor = Vendor::find($this->vendorId);
                if (!$vendor) {
                    exit("Vendor Not Found");
                }
                echo "Selected  vendor is " . $vendor->name;
                $vendorTemplates = VendorTemplate::where(['vendor_id' => $this->vendorId, 'is_active' => 1, 'is_deleted' => 0])->get()->pluck($hostColumn)->toArray();


                if (count($vendorTemplates) < 1) {
                    exit("No Templates assigned to selected vendor");
                }

                $host = $vendorTemplates[array_key_first($vendorTemplates)];
                if (count($vendorTemplates) > 1) {
                    $host = $this->choice(
                        'Multiple templates found for choosen venodor?',
                        $vendorTemplates,
                        0
                    );
                }

                $vendorTemplate = VendorTemplate::where(['vendor_id' => $this->vendorId, $hostColumn => $host, 'is_active' => 1, 'is_deleted' => 0])->first();
            } else {

                $this->templateId = $this->argument('templateId');
                $vendorTemplate = VendorTemplate::find($this->templateId);
            }
            echo "\n Selected Template is " . $vendorTemplate->template_name;


            $jsonData = $this->getJsonData($vendorTemplate);

            $this->writeToFile($jsonData, $vendorTemplate);

            $this->info('Cms Data Exported Sucessfully');
        } catch (\Throwable $th) {
            dd($th->getMessage(), $th->getLine(), $th->getFile());
            exit();
        }
    }

    // public function getJsonData($vendorTemplate)
    // {
    //     $data = [];

    //     $data['menus'] = FrontendMenu::with(['subMenus', 'components' => function ($component) {
    //         $component->with('posts','category');
    //     }, 'menuImage'])->where(['vendor_template_id' => $vendorTemplate->id, 'is_deleted' => 0, 'parent_id' => null])
    //     ->where('vendor_id', $this->vendorId)->get();

    //     $this->copyImages($vendorTemplate, $data);

    //     return json_encode($data, JSON_PRETTY_PRINT);
    // }
    public function getJsonData($vendorTemplate)
    {
        $data = [];

        $data['menus'] = FrontendMenu::with([
            'subMenus',
            'components' => function ($query) {
                $query->with(['posts']);
            },
            'menuImage',
            'service',
            'serviceCategory' // <-- Ensure this is the correct relationship name
        ])
            ->where([
                'vendor_template_id' => $vendorTemplate->id,
                'is_deleted' => 0,
                'parent_id' => null
            ])
            ->where('vendor_id', $this->vendorId)
            ->get()
            ->map(function ($menu) {
                $menu["service_category_name"] = "";
                if ($menu->service_category_id && $menu->serviceCategory) {
                    $menu['service_category_name'] = $menu->serviceCategory->service_category_name;
                }

                $menu["service_name"] = "";
                if ($menu->service_id && $menu->service) {
                    $menu['service_name'] = $menu->service->service_name;
                }
                // Loop through subMenus
                if ($menu->subMenus && $menu->subMenus->count()) {
                    $menu->subMenus->map(function ($subMenu) {
                        $subMenu['service_category_name'] = '';
                        if ($subMenu->service_category_id && $subMenu->serviceCategory) {
                            $subMenu['service_category_name'] = $subMenu->serviceCategory->service_category_name;
                        }
                        
                        $subMenu['service_name'] = '';
                        if ($subMenu->service_id && $subMenu->service) {
                            $subMenu['service_name'] = $subMenu->service->service_name;
                        }
                        return $subMenu;
                    });
                }
                return $menu;
            });

        $this->copyImages($vendorTemplate, $data);

        return json_encode($data, JSON_PRETTY_PRINT);
    }


    public function writeToFile($jsonContent, $vendorTemplate)
    {
        $loc_name = "vendor/systha/";
        $template_location = $vendorTemplate->template_location;
        if (!file_exists($loc_name . $template_location)) {
            exit('Choose Template doesnot exists');
        }
        $resource_location = base_path($loc_name . $template_location . "/resources");
        if (!file_exists($resource_location)) {
            mkdir($resource_location, 0777, true);
        }

        file_put_contents($resource_location . "/data.json", $jsonContent);
    }

    public function copyImages($vendorTemplate, $data)
    {
        $loc_name = "vendor/systha/";
        $template_location = $vendorTemplate->template_location;


        $resource_location = base_path($loc_name . $template_location . "/resources");


        $this->zipArchive = new ZipArchive();

        $zipFile = $resource_location . "/storage.zip";

        if ($this->zipArchive->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            exit("Unable to open file.");
        }

        foreach ($data as $key => $val) {

            $this->eachTypeImages($vendorTemplate, $val);
        }
        
        $this->zipArchive->close();
    }

    public function eachTypeImages($vendorTemplate, $data)
    {

        $loc_name = "vendor/systha/";
        $template_location = $vendorTemplate->template_location;

        //template resources path
        $resource_path  = base_path($loc_name . $template_location . "/resources/storage");

        // dd($resource_path);
        //storage path
        $storage_location = storage_path();

        $storage_location = $vendorTemplate->storage_path;


        // print_r($data);
        // dd($storage_location, $resource_path);
        foreach ($data as $k => $v) {

            if (isset($v['category_image']) && $v['category_image'] != null) {

                $this->searchStorage($v['category_image'], $storage_location, $resource_path);
            }
            if (isset($v['thumbnail'])) {


                $this->checkImage($v['thumbnail'], $storage_location, $resource_path);
            }

            if (isset($v['menu_image'])) {

                $this->checkImage($v['menu_image'], $storage_location, $resource_path);
            }
            if (isset($v['images'])) {

                $this->checkImages($v['images'], $storage_location, $resource_path);
            }


            if (isset($v['components'])) {

                $this->checkComponentImage($v['components'], $storage_location, $resource_path);
            }

            if (isset($v['child']) || isset($v['sub_menus'])) {

                $this->eachTypeImages($vendorTemplate, $v['child']);
            }
        }
    }
    public function checkImages($images, $storage_location, $resource_location)
    {
        foreach ($images as $image) {
            if ($image && isset($image['file_name'])) {
                $this->searchStorage($image['file_name'], $storage_location, $resource_location);
            }
        }
    }

    public function checkImage($image_data, $storage_location, $resource_location)
    {
        if ($image_data['file_name']) {
            $this->searchStorage($image_data['file_name'], $storage_location, $resource_location);
        }
    }



    public function checkComponentImage($components, $storage_location, $resource_location)
    {
        foreach ($components as $comp) {
            if (isset($comp['posts']) && count($comp['posts']) > 0) {
                $this->checkPostImage($comp['posts'], $storage_location, $resource_location);
            }
        }
    }

    public function checkPostImage($posts, $storage_location, $resource_location)
    {
        foreach ($posts as $post) {
            if ($post && isset($post['image'])) {
                $this->searchStorage($post['image']['file_name'], $storage_location, $resource_location);
            }
        }
    }

    public function checkOffersImage($offers, $storage_location, $resource_location)
    {
        foreach ($offers as $offer) {

            if ($offer && $offer['thumbnail']) {

                $this->searchStorage($offer['thumbnail'], $storage_location, $resource_location);
            }
        }
    }

    //search file from storage folder

    public function searchStorage($fileName, $storage_path, $resource_path)
    {


        $searched_file = $storage_path . '/' . $fileName;
        if (file_exists($searched_file)) {

            $relativeStoragePath = str_replace(storage_path() . '/', ' ', $searched_file);
            $relativeStoragePath = $searched_file;

            $relativeStoragePath = explode("/", $relativeStoragePath);
            $start = array_search('storage', $relativeStoragePath);

            $fromStorageOnward = array_slice($relativeStoragePath, $start);


            $result = implode("/", $fromStorageOnward);


            $new_path = $resource_path . '/' . $fileName;

            $this->zipArchive->addFile($searched_file, $result);
        } else {
            $this->searchInChildFolder($fileName, $storage_path, $resource_path);
        }
    }

    protected function searchInChildFolder($fileName, $storage_path, $resource_path)
    {
        $child_folders = scandir($storage_path);

        foreach ($child_folders as $folder) {
            if (is_dir($storage_path . '/' . $folder)) {
                if (!in_array(strtolower($folder), [".", "..", "framework", "logs"])) {
                    $new_storage_path = $storage_path . '/' . $folder;
                    $new_resource_path = $resource_path . '/' . $folder;
                    //$this->zipArchive->addEmptyDir($resource_path.'/'.$folder);
                    $this->searchStorage($fileName, $new_storage_path, $new_resource_path);
                }
            }
        }
    }
}
