<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Systha\Core\Models\FrontendMenu;
use Systha\Core\Models\Service;
use Systha\Core\Models\VendorTemplate;
use Systha\Core\Models\ServiceCategory;
use Systha\Core\Models\StaticContent;
use Systha\Core\Models\VendorComponentPost;
use Systha\Core\Models\VendorMenuComponent;

class ImportVendorTemplate extends Command
{
    protected $signature = 'import:vendor-template {--templateId=}';
    protected $description = 'Import vendor template menus and contents from storage/app/vendor_template.json';


    public function handle()
    {
        $templateId = $this->option('templateId');

        if ($templateId) {
            $template = VendorTemplate::with('vendor')->find($templateId);
        } else {
            // Prompt selection if no templateId provided
            $templates = VendorTemplate::where(['is_deleted'=>0,'is_active'=>1])->with('vendor')->get();

            if ($templates->isEmpty()) {
                $this->error('No vendor templates found.');
                return 1;
            }

            $choice = $this->choice(
                'Select a vendor template to unzip for:',
                $templates->map(fn($t) => "{$t->id} - {$t->template_name}")->toArray()
            );

            $templateId = (int) explode(' - ', $choice)[0];
            $template = VendorTemplate::with('vendor')->find($templateId);
        }

        if (!$template || !$template->vendor) {
            $this->error("Vendor or Template not found.");
            return 1;
        }

        $vendorId = $template->vendor_id;
        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/vendor_template.json");

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        // Ask if fresh or update
        $installType = $this->choice(
            'Do you want a fresh install (delete all data) or update existing data?',
            ['fresh', 'update'],
            1 // default to 'update'
        );

        if ($installType === 'fresh') {
            $this->warn("Deleting all existing menus, components, and posts for vendor ID {$vendorId} and template ID {$templateId}...");

            $pageIds = FrontendMenu::where([
                'vendor_template_id' => $templateId,
                'vendor_id' => $vendorId,
            ])->pluck('id')->toArray();


            $componentIds = VendorMenuComponent::whereIn('page_id', $pageIds)
                ->pluck('id')
                ->toArray();


            // dd($componentIds);

            // Delete posts
            // VendorComponentPost::whereIn('component_id', function ($query) use ($vendorId, $templateId) {
            //     $query->select('id')->from('vendor_menu_components')
            //         ->whereIn('page_id', function ($q) use ($vendorId, $templateId) {
            //             $q->select('id')->from('frontend_menus')
            //                 ->where('vendor_id', $vendorId)
            //                 ->where('vendor_template_id', $templateId);
            //         });
            // })->delete();

            VendorComponentPost::whereIn('page_id', $pageIds)->delete();


            VendorComponentPost::whereIn('component_id', $componentIds)->delete();

            VendorMenuComponent::whereIn('page_id', $pageIds)->delete();


            // Delete components
            // VendorMenuComponent::whereIn('page_id', function ($q) use ($vendorId, $templateId) {
            //     $q->select('id')->from('frontend_menus')
            //         ->where('vendor_id', $vendorId)
            //         ->where('vendor_template_id', $templateId);
            // })->delete();



            StaticContent::whereIn('frontend_menu_id', $pageIds)->delete();

            // Delete menus
            FrontendMenu::where('vendor_id', $vendorId)
                ->where('vendor_template_id', $templateId)
                ->delete();

            $this->info("All existing data deleted.");
        }

        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        $this->info("Importing into template ID: {$template->id}, Vendor ID: {$vendorId}");

        foreach ($data['menus'] as $menuData) {
            $this->importMenu($menuData, $template->id, $vendorId);
        }

        $this->info("Vendor template imported successfully.");
        return 0;
    }



    /**
     * Recursive import of menus with submenus, components, posts.
     */
    private function importMenu(array $menuData, int $templateId, int $vendorId, ?int $parentId = null)
    {
        // If service_category_name exists, find its id and override service_category_id
        $serviceCategoryId = null;
        $serviceId = null;
        if (!empty($menuData['service_category_name'])) {
            $serviceCategory = ServiceCategory::where([
                'service_category_name' => $menuData['service_category_name'],
                'vendor_id' => $vendorId
            ])
                ->first();
            if ($serviceCategory) {
                $serviceCategoryId = $serviceCategory->id;
            }
        }

        if (!empty($menuData['service_name'])) {
            $serviceItem = Service::where([
                'service_name' => $menuData['service_name'],
                'vendor_id' => $vendorId
            ])
                ->first();
            if ($serviceItem) {
                $serviceId = $serviceItem->id;
            }
        }

        $menuInsertData = [
            'menu_name' => $menuData['menu_name'] ?? null,
            'service_category_id' => $serviceCategoryId ?? ($menuData['service_category_id'] ?? null),
            'service_id' => $serviceId,
            'detailpage_link' => $menuData['detailpage_link'] ?? null,
            'menu_location' => $menuData['menu_location'] ?? null,
            'location_footer' => $menuData['location_footer'] ?? null,
            'type' => $menuData['type'] ?? null,
            'parent_id' => $parentId,
            'target' => $menuData['target'] ?? null,
            'icon' => $menuData['icon'] ?? null,
            'link' => $menuData['link'] ?? null,
            'section' => $menuData['section'] ?? null,
            'seq_no' => $menuData['seq_no'] ?? null,
            'is_active' => $menuData['is_active'] ?? 1,
            'is_deleted' => 0,
        ];


        // Use menu_code + vendor_id + vendor_template_id as the unique identifier
        $menu = FrontendMenu::updateOrCreate(
            [
                'menu_code' => $menuData['menu_code'] ?? null,
                'vendor_id' => $vendorId,
                'vendor_template_id' => $templateId,
            ],
            $menuInsertData
        );

        // Import components if any
        if (!empty($menuData['components'])) {
            foreach ($menuData['components'] as $componentData) {
                $componentInsertData = [
                    'title' => $componentData['title'] ?? null,
                    'data_mapper' => $componentData['data_mapper'] ?? null,
                    'ref_post' => $componentData['ref_post'] ?? null,
                    'bg_color' => $componentData['bg_color'] ?? null,
                    'title_color' => $componentData['title_color'] ?? null,
                    'seq_no' => $componentData['seq_no'] ?? null,
                    'type' => $componentData['type'] ?? null,
                    'description' => $componentData['description'] ?? null,
                    'vendor_template_post_menu' => $componentData['vendor_template_post_menu'] ?? null,
                    'link' => $componentData['link'] ?? null,
                    'data_mapper' => $componentData['data_mapper'] ?? null,
                ];

                // Match by component_name + page_id (menu id). Add other unique fields if needed.
                $component = VendorMenuComponent::updateOrCreate(
                    [
                        'component_name' => $componentData['component_name'] ?? null,
                        'page_id' => $menu->id,
                    ],
                    $componentInsertData
                );

                // Import posts if any
                if (!empty($componentData['posts'])) {
                    foreach ($componentData['posts'] as $postData) {
                        $postInsertData = [

                            'title' => $postData['title'] ?? null,
                            'sub_title' => $postData['sub_title'] ?? null,
                            'title_color' => $postData['title_color'] ?? null,
                            'sub_title_color' => $postData['sub_title_color'] ?? null,
                            'highlight' => $postData['highlight'] ?? null,
                            'content' => $postData['content'] ?? null,
                            'description' => $postData['description'] ?? null,
                            'short_description' => $postData['short_description'] ?? null,
                            'post_image_link' => $postData['post_image_link'] ?? null,
                            'likes' => $postData['likes'] ?? 0,
                            'comments' => $postData['comments'] ?? 0,
                            'post_image' => $postData['post_image'] ?? null,
                            'seq_no' => $postData['seq_no'] ?? null,
                            'button_label' => $postData['button_label'] ?? null,
                            'link_url' => $postData['link_url'] ?? null,
                            'video' => $postData['video'] ?? null,
                            'is_active' => $postData['is_active'] ?? 1,
                            'is_publish' => $postData['is_publish'] ?? 1,
                            'data_mapper' => $postData['data_mapper'] ?? null,
                        ];

                        // Match by slug + component_id
                        VendorComponentPost::updateOrCreate(
                            [
                                'title' => $postData['title'] ?? null,
                                'slug' => $postData['slug'] ?? null,
                                'component_id' => $component->id,
                            ],
                            $postInsertData
                        );
                    }
                }
            }
        }

        // Recursively import sub menus if any
        if (!empty($menuData['sub_menus'])) {
            // dd("jhere");
            foreach ($menuData['sub_menus'] as $subMenuData) {
                $this->importMenu($subMenuData, $templateId, $vendorId, $menu->id);
            }
        }
    }
}
