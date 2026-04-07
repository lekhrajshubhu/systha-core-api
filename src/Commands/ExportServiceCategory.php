<?php

namespace Systha\Core\Commands;


use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Systha\Core\Models\Vendor;
use Illuminate\Support\Facades\Storage;
use Systha\Core\Models\VendorTemplate;
use Systha\Core\Models\ServiceCategory;

class ExportServiceCategory extends Command
{
    // Add --vendor option here
    protected $signature = 'export:category {--templateId=}';
    protected $description = 'Export service_categories with their services for a selected tmeplate to a JSON file';

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

        $this->info("Exporting categories for vendor ID: {$vendorId}");

       

        $categories = ServiceCategory::with(['services.parent'])
            ->where([
                'vendor_id' => $vendorId,
                'is_deleted' =>0
            ])
            ->select('id', 'service_category_name', 'description', 'vendor_id')
            ->get();

        $filtered = $categories->map(function ($category) {
            return [
                'service_category_name' => $category->service_category_name,
                'description' => $category->description,
                'services' => $category->services
                    // Sort so parent services come first
                    ->sortBy(function ($service) {
                        return $service->parent_id ? 1 : 0;
                    })
                    ->values()
                    ->map(function ($service) {
                        $serviceData = [
                            'service_name' => $service->service_name,
                            'slug' => Str::slug($service->service_name),
                            'question_text' => $service->question_text,
                            'type' => $service->type,
                            'unit_type' => $service->unit_type,
                            'parent_id' => $service->parent_id,
                            'description' => $service->description,
                            'price' => $service->price,
                            'state' => $service->state,
                            'is_active' => $service->is_active,
                        ];

                        if ($service->parent_id && $service->relationLoaded('parent') && $service->parent) {
                            $serviceData['parent_name'] = $service->parent->name;
                        }

                        return $serviceData;
                    })
                    ->toArray(),
            ];
        });



        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/service_categories.json");

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

        // Write file directly
        file_put_contents($filePath, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Export complete: {$filePath}");
    }
}
