<?php

namespace Systha\Core\Commands;


use Illuminate\Console\Command;
use Systha\Core\Models\VendorTemplate;
use Systha\Core\Models\VendorLookup;

class ImportPackageCategory extends Command
{
    protected $signature = 'import:package-category {--templateId=}';
    protected $description = 'Import package categories from vendor_lookups for a selected vendor from JSON';

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

        $this->info("Importing package categories for vendor: {$template->template_name}");

        $vendorId = $template->vendor_id;

        $mode = $this->choice(
            'Import mode?',
            ['fresh (delete + insert)', 'update (no delete)'],
            1
        );

        if ($mode === 'fresh (delete + insert)') {
            $this->warn("Deleting existing package categories for vendor: {$vendorId}...");
            VendorLookup::where([
                'vendor_id' => $vendorId,
                'code' => 'package_categories',
            ])->delete();
        }

        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/package_categories.json");

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $json = file_get_contents($filePath);
        $categories = json_decode($json, true);

        if (!is_array($categories)) {
            $this->error("Invalid JSON in: {$filePath}");
            return 1;
        }

        foreach ($categories as $entry) {
            $code = $entry['code'] ?? 'package_categories';
            if ($code !== 'package_categories') {
                continue;
            }

            VendorLookup::updateOrCreate(
                [
                    'vendor_id' => $vendorId,
                    'code' => $code,
                    'value' => $entry['value'] ?? null,
                ],
                [
                    'icon_md' => $entry['icon_md'] ?? null,
                    'icon_fa' => $entry['icon_fa'] ?? null,
                    'icon_svg' => $entry['icon_svg'] ?? null,
                    'description' => $entry['description'] ?? null,
                    'is_active' => $entry['is_active'] ?? 1,
                    'is_deleted' => 0,
                ]
            );
        }


        $this->info("Import complete for vendor: {$template->template_name}");
    }
}
