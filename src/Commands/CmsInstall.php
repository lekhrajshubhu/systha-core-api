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

use ZipArchive;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Systha\Core\Models\Service;
use Illuminate\Support\Facades\Schema;
use Systha\Core\Models\TemplateComponent;
use Symfony\Component\Console\Input\InputOption;
use Systha\Core\Models\{ServiceCategory, Vendor};
use Systha\Core\Models\{VendorTemplate, FrontendMenu, VendorMenuComponent, VendorComponentPost, StaticContent, EcommFile, Category};

class CmsInstall extends Command
{

    protected $name = "cms:install-old";
    protected $description = 'Install connects to main system';
    protected $pkgname = "";
    protected $template = "";
    protected $vendor = "";
    protected $templateLocation;
    protected $storageLocation = '';

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in productionn', null]
        ];
    }

    public function handle()
    {
        $vendors = Vendor::where(['state' => 'publish', 'is_deleted' => 0])->get();

        if ($vendors->isEmpty()) {
            exit('No vendor found');
        } else {
            $this->info('List of Vendors');
            foreach ($vendors as $vendor) {
                $this->info("ID: {$vendor->id}, Name: {$vendor->name}");
            }
        }

        $this->vendor = $this->ask("Enter vendor Id of template you want to install");
        $vendor = Vendor::find($this->vendor);
        if (!$vendor) {
            echo "Selected Vendor Not Found \n";
        }
        echo "Selected vendor is " . $vendor->name . "\n";
        $confirmation = $this->confirm('Setup New Vendor Template?');
        $hostColumn = Schema::hasColumn((new VendorTemplate)->getTable(), 'template_host') ? 'template_host' : 'host';
        if ($confirmation) {
            $template_name = $this->ask("Enter Template Name");
            $template_host = $this->ask("Enter Template Host");
            $template_location = $this->ask("Enter Template Location");
            $this->storageLocation = $this->ask("Enter Storage Location");
            $vendorTemplate = VendorTemplate::updateOrCreate([
                'vendor_id' => $vendor->id,
                $hostColumn => $template_host
            ], [
                'template_name' => $template_name,
                'template_location' => $template_location,
                'storage_path' => $this->storageLocation,
                'is_active' => 1
            ]);
        } else {
            $vendorTemplates = VendorTemplate::where(['vendor_id' => $this->vendor, 'is_active' => 1, 'is_deleted' => 0])->get()->pluck($hostColumn)->toArray();
            if (count($vendorTemplates) < 1) {
                exit("Template not found");
            }

            $host = $vendorTemplates[array_key_first($vendorTemplates)];
            if (count($vendorTemplates) > 1) {
                $host = $this->choice(
                    'Multiple templates found for choosen venodor?',
                    $vendorTemplates,
                    0
                );
            }

            $vendorTemplate = VendorTemplate::where(['vendor_id' => $this->vendor, $hostColumn => $host, 'is_active' => 1, 'is_deleted' => 0])->first();
        }


        $this->templateLocation = "vendor/systha/" . $vendorTemplate->template_location;
        $this->storageLocation = $vendorTemplate->storage_path;
        $deletion = $this->deleteData($vendorTemplate);
        //if($deletion){

        $datas = $this->getJsonData($vendorTemplate);
        //dd(sizeof($datas['menus']));
        $imported = $this->importData($datas, $vendorTemplate);
        $this->unZipStorageFile($vendorTemplate);

        echo "\ncompleted";
    }

    protected function handletemp()
    {
        $this->getAllComponents();
        $this->info('template install sucessfully');
        // get temp name by input command

        // go to systan/temp folder /componnetn

        // get all files directory (eg code available under vendpackages cms template controler)

        // insert file name into newly created database  (id, temp_name, comp_name, vendor_id)
        //  componentname should be filename without extension
        // template name should be same as tempfolder

    }

    public function getAllComponents()
    {
        $loc_name = "vendor/systha/";
        $template_location = $this->pkgname;
        $components = [];
        if (is_dir(base_path($loc_name . $template_location . "/src/views/components"))) {
            is_dir(base_path($loc_name . $template_location . "/src/views/components"));
            $comps = scandir(base_path($loc_name . $template_location . "/src/views/components"));

            $counter = 1;
            foreach ($comps as $key => $value) {
                if ($value != '.' && $value != '..') {
                    if (is_dir(base_path($loc_name . $template_location . "\\src\\views\\components\\{$value}"))) {
                        $all_comps = scandir(base_path($loc_name . $template_location . "\\src\\views\\components\\{$value}"));
                        foreach ($all_comps as $key => $value) {
                            if (strpos($value, '.blade.php')) {
                                $no_ext = explode('.', $value)[0];
                                $sep = explode('_', $no_ext);
                                $file_name = implode(' ', $sep);
                                array_push($components, ['id' => $counter,  'text' => $file_name, 'file_code' => $no_ext, 'vendor_id' => $this->vendor->id, 'template_id' => $this->template->id]);
                                $counter++;
                            }
                        }
                    } else {
                        if (strpos($value, '.blade.php')) {
                            $no_ext = explode('.', $value)[0];
                            $sep = explode('_', $no_ext);
                            $file_name = implode(' ', $sep);
                            array_push($components, ['id' => $counter, 'text' => $file_name, 'file_code' => $no_ext, 'vendor_id' => $this->vendor->id, 'template_id' => $this->template->id]);
                            $counter++;
                        }
                    }
                }
            }
        }

        foreach ($components as $cmp) {
            TemplateComponent::updateOrCreate(['text' => $cmp['text'], 'file_code' => $cmp['file_code']], Arr::except($cmp, ['component_name']));
        }
    }

    public function getVendor()
    {

        $temp = VendorTemplate::where('template_location', $this->pkgname)->where('is_active', 1)->where('is_deleted', 0)->first();
        $this->template = $temp;
        $this->vendor = $temp->vendor;
    }

    public function getJsonData($vendorTemplate)
    {

        $importFileLocation = base_path("vendor/systha/{$vendorTemplate->template_location}/resources/data.json");

        if (!file_exists($importFileLocation)) {
            exit('Data doesnot found');
        }

        $datas = file_get_contents($importFileLocation);
        return json_decode($datas, true);
    }

    public function deleteData($vendorTemplate)
    {

        //Artisan::call('cms:export',['templateId'=>$vendorTemplate->id]);
        //$backupFileLocation = base_path("vendor/systha/{$vendorTemplate->template_location}/resources/data_backup.json");

        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $menus = FrontendMenu::where(['vendor_template_id' => $vendorTemplate->id, 'vendor_id' => $this->vendor])->get();
            if (count($menus) > 0) {
                foreach ($menus as $menu) {

                    $components = VendorMenuComponent::where('page_id', $menu->id)->get();

                    foreach ($components as $comp) {
                        VendorComponentPost::where('component_id', $comp->id)->delete();
                        if ($comp['image'] && count($comp['image']) > 0) {
                            EcommFile::where('id', $comp['image']['id'])->delete();
                        }
                        $comp->delete();
                    }
                    Category::where('id', $menu->category_id)->delete();
                    StaticContent::where('frontend_menu_id', $menu->id)->delete();
                    FrontendMenu::where('id', $menu->id)->delete();
                    if ($menu['menu_image'] && count($menu['menu_image']) > 1) {
                        EcommFile::where('id', $menu['menu_image']['id'])->delete();
                    }
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::commit();
            return true;
        } catch (\Exception $e) {
            exit($e->getMessage());

            //return false;
            DB::rollBack();
        }
    }

    public function importDataBackup($datas, $vendorTemplate)
    {
        DB::beginTransaction();
        try {
            foreach ($datas as $menu) {

                // dd(Arr::except($menu,['id','components','contents','created_at','updated_at']));
                $menu['vendor_template_id'] = $vendorTemplate->id;
                $db_menu = FrontendMenu::create(Arr::except($menu, ['id', 'menu_image', 'components', 'contents', 'created_at', 'updated_at']));
                if ($menu['menu_image']) {

                    $menu_image = Arr::except($menu['menu_image'], ['id', 'created_at', 'updated_at']);
                    $menu_image['table_name'] = 'frontend_menus';
                    $menu_image['table_id'] = $db_menu->id;
                    EcommFile::create($menu_image);
                }

                foreach ($menu['components'] as $comp) {

                    $comp['page_id'] = $db_menu->id;
                    $comp['vendor_template_id'] = $vendorTemplate->id;
                    $db_comp = VendorMenuComponent::create(Arr::except($comp, ['id', 'posts', 'created_at', 'updated_at']));
                    foreach ($comp['posts'] as $post) {
                        $image = $post['image'];

                        $post = Arr::except($post, ['id', 'image', 'created_at', 'updated_at']);
                        $post['page_id'] = $db_menu->id;
                        $post['component_id'] = $db_comp->id;
                        $post['vendor_template_id'] = $vendorTemplate->id;
                        $db_post = VendorComponentPost::create($post);
                        if (count($image) > 0) {
                            $image['table_name'] = 'vendor_component_posts';
                            $image['table_id'] = $db_post->id;
                            EcommFile::create(Arr::except($image, ['id', 'created_at', 'updated_at']));
                        }
                    }
                }

                foreach ($menu['contents'] as $content) {
                    $content = Arr::except($content, ['id', 'created_at', 'updated_at']);
                    $content['frontend_menu_id'] = $db_menu->id;
                    $content['vendor_template_id'] = $vendorTemplate->id;
                    $db_content = StaticContent::create($content);
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            dd($e->getMessage(), $e->getLine());
            exit($e->getMessage());
            // return response(['message' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()], 500);
            return false;
            DB::rollBack();
        }
    }

    public function importData($data, $vendorTemplate)
    {
        if (count($data['menus']) > 0) {
            $this->importMenus($data['menus'], $vendorTemplate->id);
        }

        return true;
    }

    public function unZipStorageFile($vendorTemplate)
    {

        $zipFileLocation  = base_path("vendor/systha/{$vendorTemplate->template_location}/resources/storage.zip");
        $copyLocation = $this->storageLocation;

        // $copyLocation = preg_replace('#/storage$#', '', rtrim($this->storageLocation, '/'));
        // dd($copyLocation);
        $zip = new ZipArchive;
        if (file_exists($zipFileLocation)) {
            $res = $zip->open($zipFileLocation);
            if ($res == TRUE) {
                $zip->extractTo($copyLocation);
                $zip->close();
            } else {
                exit('unzip Process failed');
            }
        }
    }

    public function deleteBackupJsonFile($vendorTemplate)
    {
        $backupJsonFileLocation = base_path($this->templateLocation . "/resources/data_backup.json");
        if (file_exists($backupJsonFileLocation)) {
            unlink($backupJsonFileLocation);
        }
    }

    public function importMenus($menus, $templateId)
    {
        DB::beginTransaction();
        try {
            foreach ($menus as $menu) {
                $this->importMenu($menu, $templateId);
            }
            DB::commit();
        } catch (\Exception $e) {
            dd($e->getMessage(), $e->getLine());
            exit($e->getMessage());
            // return response(['message' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()], 500);
            return false;
            DB::rollBack();
        }
    }



    public function importMenu($menu, $templateId, $categoryId = null, $parentId = null)
    {

        $menu['vendor_template_id'] = $templateId;
        $menu['vendor_id'] = $this->vendor;
        $menu['parent_id'] = $parentId;
        if ($menu['category_id'] != null) {
            $db_category = Category::where('old_category_id', $menu['category_id'])->first();

            $menu['category_id'] = $db_category->id;
        }

        if (isset($menu["service_category_id"])) {
            $serviceCategory = ServiceCategory::find((int)$menu["service_category_id"]);
            if (!$serviceCategory) {

                $serviceCategory = ServiceCategory::create([
                    "service_category_name" => $menu["service_category_name"],
                    "vendor_id" => $menu["vendor_id"]
                ]);
                $menu["service_category_id"] = $serviceCategory->id;
            }
        }
        if (isset($menu["service_id"])) {
            $service = Service::where('service_name', $menu["service_name"])->first();
            if (!$service) {

                $service = $menu["service"];

                $service = Service::create([
                    "service_name" => $service["service_name"],
                    "slug" => $service["slug"],
                    "question_text" => $service["question_text"],
                    "vendor_id" => $service["vendor_id"]
                ]);
                $menu["service_id"] = $service->id;
            }
        }
        unset($menu["service_category_name"]);
        unset($menu["service_category"]);
        unset($menu["service"]);
        unset($menu["service_name"]);
        $db_menu = FrontendMenu::create(Arr::except($menu, ['id', 'menu_image', 'components', 'content', 'sub_menus', 'category', 'created_at', 'updated_at']));

        // dd($db_menu);
        if (isset($menu['components']) && count($menu['components']) > 0) {
            $this->importMenuComponent($menu['components'], $db_menu->vendor_template_id, $db_menu->id);
        }
        if (isset($menu->content) && $menu->content) {
            $this->importMenuContent($menu->content, $db_menu->vendor_template_id, $db_menu->id);
        }
        if (isset($menu['sub_menus']) && count($menu['sub_menus']) > 0) {

            $this->importChildMenu($menu['sub_menus'], $db_menu->vendor_template_id, $db_menu->id);
        }
    }

    public function importChildMenu($menus, $templateId, $parentId = null)
    {

        foreach ($menus as $key => $menu) {
            $menu['vendor_template_id'] = $templateId;
            $menu['vendor_id'] = $this->vendor;
            $menu['parent_id'] = $parentId;
            if ($menu['category_id'] != null) {
                $db_category = Category::where('old_category_id', $menu['category_id'])->first();
                $menu['category_id'] = $db_category->id;
            }
            if (isset($menu["service_category_name"])) {
                $serviceCategory = ServiceCategory::where('service_category_name', $menu["service_category_name"])->first();
                if (!$serviceCategory) {
                    $serviceCategory = ServiceCategory::create([
                        "service_category_name" => $menu["service_category_name"],
                        "vendor_id" => $menu["vendor_id"]
                    ]);
                    $menu["service_category_id"] = $serviceCategory->id;
                }
            }
            if (isset($menu["service_id"])) {
                $service = Service::where('service_name', $menu["service_name"])->first();
                if (!$service) {
                    
                    $service = $menu["service"];
                    
                    $service = Service::create([
                        "service_name" => $service["service_name"],
                        "slug" => $service["slug"],
                        "question_text" => $service["question_text"],
                        "vendor_id" => $service["vendor_id"]
                    ]);
                    $menu["service_id"] = $service->id;
                }
            }
            // if (isset($menu["service_category_id"])) {
            //     $serviceCategory = ServiceCategory::find((int)$menu["service_category_id"]);
            //     if (!$serviceCategory) {
            //         $serviceCategory = ServiceCategory::create([
            //             "service_category_name" => $menu["service_category_name"],
            //             "vendor_id" => $menu["vendor_id"]
            //         ]);
            //         $menu["service_category_id"] = $serviceCategory->id;
            //     }
            // }
            unset($menu["service_category_name"]);
            unset($menu["service_category"]);
            unset($menu["service"]);
            unset($menu["service_name"]);

            $db_menu = FrontendMenu::create(Arr::except($menu, ['id', 'menu_image', 'components', 'content', 'sub_menus', 'category', 'created_at', 'updated_at']));
            if (isset($menu['components']) && count($menu['components']) > 0) {
                $this->importMenuComponent($menu['components'], $db_menu->vendor_template_id, $db_menu->id);
            }

            if (isset($menu->content) && $menu->content) {
                $this->importMenuContent($menu->content, $db_menu->vendor_template_id, $db_menu->id);
            }



            if (isset($menu['sub_menus']) && count($menu['sub_menus']) > 0) {

                $this->importChildMenu($menu['sub_menus'], $db_menu->vendor_template_id, $db_menu->id);
            }
        }
    }

    public function importMenuComponent($components, $templateId, $menuId)
    {
        try {
            //code...
            foreach ($components as $comp) {
                $comp['page_id'] = $menuId;
                $comp['vendor_template_id'] = $templateId;
                $db_comp = VendorMenucomponent::create(Arr::except($comp, ['id', 'posts', 'created_at', 'updated_at']));
                // dd($db_comp);
                if (isset($comp['posts']) && count($comp['posts']) > 0) {
                    foreach ($comp['posts'] as $post) {
                        $image = $post['image'];

                        $post = Arr::except($post, ['id', 'image', 'created_at', 'updated_at']);
                        $post['page_id'] = $menuId;
                        $post['component_id'] = $db_comp->id;
                        $post['vendor_template_id'] = $templateId;
                        // dd($post);
                        $db_post = VendorComponentPost::create($post);
                        if (!empty($image)) {
                            $image['table_name'] = 'vendor_component_posts';
                            $image['table_id'] = $db_post->id;
                            EcommFile::create(Arr::except($image, ['id', 'created_at', 'updated_at', 'folder_name']));
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function importMenuContent($content, $templateId, $menuId)
    {
        $content['vendor_id'] = $this->vendor;
        $content = Arr::except($content, ['id', 'created_at', 'updated_at']);
        $content['frontend_menu_id'] = $menuId;
        $content['vendor_template_id'] = $templateId;
        $db_content = StaticContent::create($content);
        return $db_content;
    }

    public function importMenuCategory($category)
    {
        if ($category['vendor_id'] != 0) {
            $category['vendor_id'] = $this->vendor;
        }
        $db_category = Category::create(Arr::except($category, ['id', 'child', 'created_at', 'updated_at']));

        if (count($category['child']) > 0) {
            $this->importMenuChildCategory($category['child'], $db_category->id);
        }

        return $db_category;
    }

    public function importMenuChildCategory($categories, $parentId)
    {
        foreach ($categories as $child_category) {
            $child_category['parent_id'] = $parentId;
            $db_child_category = Category::create(Arr::except($child_category, ['id', 'child', 'created_at', 'updated_at']));
            if (count($child_category['child']) > 0) {
                $this->importMenuChildCategory($child_category['child'], $db_child_category->id);
            }
        }
    }

    public function storeImage($image, $category = 'products', $id, $type = 'thumbnail')
    {
        if ($image && $image != null) {
            $image['table_name'] = $category;
            $image['table_id'] = $id;
            $image['type'] = $type;
            EcommFile::create(Arr::except($image, ['id', 'created_at', 'updated_at']));
        }
    }

    public function storeAttachments($attachments, $category = 'products', $id)
    {

        foreach ($attachments as $attachment) {

            $this->storeImage($attachment, $category, $id, 'attachment');
        }
    }
}
