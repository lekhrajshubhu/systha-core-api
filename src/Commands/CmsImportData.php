<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Systha\Core\Models\{
    Vendor,
    VendorTemplate,
    FrontendMenu,
    VendorMenuComponent,
    VendorComponentPost,
    StaticContent,
    EcommFile,
    Category
};

class CmsImportData extends Command
{
    protected $signature = 'cms:import-data';
    protected $description = 'Import template data for a selected vendor and template.';

    public function handle()
    {
        $vendors = Vendor::where(['state' => 'publish', 'is_deleted' => 0])->get();

        if ($vendors->isEmpty()) {
            $this->error('No vendors found.');
            return;
        }

        $this->info("Available Vendors:");
        foreach ($vendors as $vendor) {
            $this->line("ID: {$vendor->id} | Name: {$vendor->name}");
        }

        $vendorId = $this->ask("Enter the Vendor ID");
        $vendor = Vendor::find($vendorId);

        if (!$vendor) {
            $this->error("Vendor not found.");
            return;
        }

        $templates = VendorTemplate::where(['vendor_id' => $vendorId, 'is_active' => 1, 'is_deleted' => 0])->get();

        if ($templates->isEmpty()) {
            $this->error("No active templates found for this vendor.");
            return;
        }

        $templateOptions = $templates->pluck('template_name', 'id')->toArray();
        $templateName = $this->choice("Select a Template", $templateOptions);

        // Reverse lookup to get the ID from the name
        $templateId = array_search($templateName, $templateOptions);

        $vendorTemplate = VendorTemplate::find($templateId);

        // dd($vendorTemplate);
        if (!$vendorTemplate) {
            $this->error("Template not found.");
            return;
        }

        $this->info("Selected Template: {$vendorTemplate->template_name}");

        $dataPath = base_path("vendor/systha/{$vendorTemplate->template_location}/resources/data.json");

        if (!file_exists($dataPath)) {
            $this->error("data.json not found.");
            return;
        }

        $jsonData = json_decode(file_get_contents($dataPath), true);
        $this->importMenus($jsonData['menus'], $vendor, $vendorTemplate);

        $this->unZipStorage($vendorTemplate);

        $this->info("Data imported successfully.");
    }

    protected function importMenus($menus, $vendor, $template)
    {
        DB::transaction(function () use ($menus, $vendor, $template) {
            foreach ($menus as $menu) {
                $this->importMenu($menu, $vendor->id, $template->id, null);
            }
        });
    }

    protected function importMenu($menu, $vendorId, $templateId, $parentId = null)
    {
        $menu['vendor_template_id'] = $templateId;
        $menu['vendor_id'] = $vendorId;
        $menu['parent_id'] = $parentId;

        if ($menu['category_id']) {
            $category = Category::where('old_category_id', $menu['category_id'])->first();
            $menu['category_id'] = $category ? $category->id : null;
        }

        $dbMenu = FrontendMenu::create(Arr::except($menu, ['id', 'menu_image', 'components', 'content', 'sub_menus', 'category', 'created_at', 'updated_at']));

        if (!empty($menu['components'])) {
            $this->importComponents($menu['components'], $templateId, $dbMenu->id);
        }

        if (!empty($menu['content'])) {
            $this->importContent($menu['content'], $templateId, $dbMenu->id, $vendorId);
        }

        if (!empty($menu['sub_menus'])) {
            foreach ($menu['sub_menus'] as $sub) {
                $this->importMenu($sub, $vendorId, $templateId, $dbMenu->id);
            }
        }
    }

    protected function importComponents($components, $templateId, $menuId)
    {
        foreach ($components as $comp) {
            $comp['vendor_template_id'] = $templateId;
            $comp['page_id'] = $menuId;

            $dbComp = VendorMenuComponent::create(Arr::except($comp, ['id', 'posts', 'created_at', 'updated_at']));

            foreach ($comp['posts'] ?? [] as $post) {
                $image = $post['image'] ?? null;
                $post = Arr::except($post, ['id', 'image', 'created_at', 'updated_at']);
                $post['vendor_template_id'] = $templateId;
                $post['component_id'] = $dbComp->id;
                $post['page_id'] = $menuId;

                $dbPost = VendorComponentPost::create($post);

                if (!empty($image)) {
                    $image['table_name'] = 'vendor_component_posts';
                    $image['table_id'] = $dbPost->id;
                    EcommFile::create(Arr::except($image, ['id', 'created_at', 'updated_at', 'folder_name']));
                }
            }
        }
    }

    protected function importContent($content, $templateId, $menuId, $vendorId)
    {
        $content = Arr::except($content, ['id', 'created_at', 'updated_at']);
        $content['vendor_template_id'] = $templateId;
        $content['frontend_menu_id'] = $menuId;
        $content['vendor_id'] = $vendorId;
        StaticContent::create($content);
    }

    protected function unZipStorage($vendorTemplate)
    {
        $zipPath = base_path("vendor/systha/{$vendorTemplate->template_location}/resources/storage.zip");
        $targetPath = preg_replace('#/storage$#', '', rtrim($vendorTemplate->storage_path, '/'));

        if (file_exists($zipPath)) {
            $zip = new ZipArchive;
            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($targetPath);
                $zip->close();
                $this->info("Storage unzipped to: {$targetPath}");
            } else {
                $this->error("Failed to unzip storage.");
            }
        }
    }
}
