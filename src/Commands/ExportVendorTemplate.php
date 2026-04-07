<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Systha\Core\Models\Service;
use Illuminate\Support\Facades\Storage;
use Systha\Core\Models\VendorTemplate;
use Systha\Core\Models\ServiceCategory;

class ExportVendorTemplate extends Command
{
    protected $signature = 'export:vendor-template {--templateId=}';
    protected $description = 'Export vendor_template menus and contents as JSON';

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


        // dd($template->template_location);
        $this->info("Exporting template: {$template->template_name}");

        $menus = $template->menus()->with(['subMenus'])->get();

        $menuData = $menus->map(fn($menu) => $this->transformMenu($menu))->toArray();

        $data = [
            'template_name' => $template->template_name,
            'menus' => $menuData,
            'contents' => $template->contents->toArray()
        ];

        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/vendor_template.json");

        // Ensure the directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (File::exists($filePath)) {
            $backupDir = base_path("vendor/systha/{$template->template_location}/resources/data_backup");
            $date = date('Y-m-d_h-i');
            $backupBase = pathinfo($filePath, PATHINFO_FILENAME);
            $backupExt = pathinfo($filePath, PATHINFO_EXTENSION);
            $backupName = $backupBase . '-' . $date . ($backupExt ? '.' . $backupExt : '');
            $backupPath = $backupDir . '/' . $backupName;

            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Move the old file to the data_backup directory
            File::move($filePath, $backupPath);
        }


        // Now write new JSON data
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Export complete: {$filePath}");
    }


    private function transformMenu($menu)
    {
        $serviceCategoryName = '';
        $serviceName = '';

        if ($menu->service_category_id) {
            $serviceCategory = ServiceCategory::find($menu->service_category_id);
            $serviceCategoryName = $serviceCategory?->service_category_name ?? '';
        }

        if ($menu->service_id) {
            $service = Service::find($menu->service_id);
            $serviceName = $service?->service_name ?? '';
        }

        return [
            'menu_name' => $menu->menu_name,
            'menu_code' => $menu->menu_code,
            'service_category_id' => $menu->service_category_id,
            'service_category_name' => $serviceCategoryName,
            'service_id' => $menu->service_id,
            'service_name' => $serviceName,
            'detailpage_link' => $menu->detailpage_link,
            'menu_location' => $menu->menu_location,
            'location_footer' => $menu->location_footer,
            'type' => $menu->type,
            'parent_id' => $menu->parent_id,
            'target' => $menu->target,
            'icon' => $menu->icon,
            'link' => $menu->link,
            'section' => $menu->section,
            'seq_no' => $menu->seq_no,
            'is_active' => $menu->is_active,
            'components' => $menu->components->map(function ($component) {
                return [
                    'component_name' => $component->component_name,
                    'title' => $component->title,
                    'data_mapper' => $component->data_mapper,
                    'page_id' => $component->page_id,
                    'ref_post' => $component->ref_post,
                    'bg_color' => $component->bg_color,
                    'title_color' => $component->title_color,
                    'seq_no' => $component->seq_no,
                    'type' => $component->type,
                    'description' => $component->description,
                    'vendor_template_post_menu' => $component->vendor_template_post_menu,
                    'link' => $component->link,
                    'data_mapper' => $component->data_mapper,
                    'posts' => $component->posts->map(function ($post) {
                        return [
                            'title' => $post->title,
                            'sub_title' => $post->sub_title, // or use storage path if needed
                            'slug' => $post->slug,
                            'title_color' => $post->title_color,
                            'sub_title_color' => $post->sub_title_color,
                            'highlight' => $post->highlight,
                            'content' => $post->content,
                            'description' => $post->description,
                            'short_description' => $post->short_description,
                            'post_image_link' => $post->post_image_link,
                            'likes' => $post->likes,
                            'comments' => $post->comments,
                            'post_image' => $post->post_image,
                            'post_image_link' => $post->post_image_link,
                            'seq_no' => $post->seq_no,
                            'button_label' => $post->button_label,
                            'link_url' => $post->link_url,
                            'video' => $post->video,
                            'is_active' => $post->is_active,
                            'is_publish' => $post->is_publish,
                            'data_mapper' => $post->data_mapper,
                        ];
                    })
                ];
            }),
            'sub_menus' => $menu->subMenus->map(fn($sub) => $this->transformMenu($sub))->toArray(),

        ];
    }
}
