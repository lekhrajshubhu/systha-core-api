<?php

namespace Systha\Core\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Systha\Core\Models\VendorTemplate;
use Systha\Core\Models\VendorLookup;

class ExportPackageCategory extends Command
{
    // Add --vendor option here
    protected $signature = 'export:package-category {--templateId=}';
    protected $description = 'Export package categories from vendor_lookups for a selected template to a JSON file';

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

        $this->info("Exporting package categories for vendor ID: {$vendorId}");

       

        $lookups = VendorLookup::where([
            'vendor_id' => $vendorId,
            'code' => 'package_categories',
            'is_deleted' => 0,
        ])
            ->select(
                'code',
                'value',
                'icon_md',
                'icon_fa',
                'icon_svg',
                'description',
                'is_active'
            )
            ->get();



        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/package_categories.json");

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
        file_put_contents($filePath, json_encode($lookups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Export complete: {$filePath}");
    }
}
