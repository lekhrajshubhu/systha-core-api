<?php

namespace Systha\Core\Commands;


use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Service;
use Illuminate\Support\Facades\Storage;
use Systha\Core\Models\VendorTemplate;
use Systha\Core\Models\ServiceCategory;

class ImportServiceCategory extends Command
{
    protected $signature = 'import:category {--templateId=}';
    protected $description = 'Import service_categories with services for a selected vendor from JSON';

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

        $this->info("Importing categories for vendor: {$template->template_name}");

        $vendorId = $template->vendor_id;

        // 🔹 STEP 1: Delete existing data for the vendor
        $this->warn("Deleting existing services and categories for vendor: {$vendorId}...");
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Service::where('vendor_id', $vendorId)->delete();
        ServiceCategory::where('vendor_id', $vendorId)->delete();

        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/service_categories.json");

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $json = file_get_contents($filePath);
        $categories = json_decode($json, true);

        $nameToId = [];

        foreach ($categories as $catData) {
            // Create or update the category
            $category = ServiceCategory::updateOrCreate(
                [
                    'vendor_id' => $vendorId,
                    'service_category_name' => $catData['service_category_name'],
                ],
                [
                    'description' => $catData['description'],
                ]
            );
            $category->services()->delete();

            $services = $catData['services'];
            $nameToId = [];

            // Pass 1: Insert/update all services without parent_id
            foreach ($services as $service) {
                $createdService = Service::updateOrCreate(
                    [
                        'service_name' => $service['service_name'],
                        'slug' => $service['slug'],
                        'service_category_id' => $category->id,
                    ],
                    [
                        'price' => $service['price'],
                        'vendor_id' => $vendorId,
                        'parent_id' => null,  // Initially no parent
                    ]
                );

                // Store the inserted/updated service ID for reference
                $nameToId[$service['service_name']] = $createdService->id;
            }

            // Pass 2: Update parent_id for services that have a parent_name
            foreach ($services as $service) {
                if (!empty($service['parent_name'])) {
                    $childName = $service['service_name'];
                    $parentName = $service['parent_name'];

                    // Make sure parent exists in nameToId
                    if (isset($nameToId[$parentName]) && isset($nameToId[$childName])) {
                        Service::where('id', $nameToId[$childName])
                            ->update(['parent_id' => $nameToId[$parentName]]);
                    }
                }
            }
        }


        $this->info("Import complete for vendor: {$template->template_name}");
    }
}
