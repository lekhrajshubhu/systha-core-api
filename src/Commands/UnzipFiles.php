<?php

namespace Systha\Core\Commands;

use ZipArchive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Systha\Core\Models\VendorTemplate;

class UnzipFiles extends Command
{
    protected $signature = 'unzip:files {--templateId=}';
    protected $description = 'Unzip template.zip into separate storage folders: services, packages, CMS';

    public function handle()
    {
        $templateId = $this->option('templateId');

        if ($templateId) {
            $template = VendorTemplate::with('vendor')->find($templateId);
        } else {
            // Prompt selection if no templateId provided
            $templates = VendorTemplate::with('vendor')->get();

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

        if (empty($template->storage_path)) {
            $this->error("Template storage path is empty.");
            return 1;
        }

        // Ensure base storage path exists and is writable.
        try {
            File::ensureDirectoryExists($template->storage_path, 0775, true, true);
        } catch (\Throwable $e) {
            $this->error("Failed to create base storage path: {$template->storage_path}");
            $this->error($e->getMessage());
            return 1;
        }
        if (!is_writable($template->storage_path)) {
            @chmod($template->storage_path, 0775);
        }
        if (!is_writable($template->storage_path)) {
            $this->error("Storage path is not writable: {$template->storage_path}");
            $this->error("Please fix permissions (e.g., chown/chmod) and retry.");
            return 1;
        }

        // $zipPath = storage_path('app/exports/template.zip');

        $zipPath = base_path("vendor/systha/{$template->template_location}/resources/data/template.zip");

        if (!File::exists($zipPath)) {
            $this->error("❌ template.zip not found at: {$zipPath}");
            return 1;
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            $this->error("❌ Failed to open template.zip");
            return 1;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $zipEntry = $zip->getNameIndex($i);
            $sourceStream = $zip->getStream($zipEntry);

            if (!$sourceStream) {
                $this->warn("⚠️ Unable to extract file: {$zipEntry}");
                continue;
            }

            $pathParts = explode('/', $zipEntry, 2);
            if (count($pathParts) < 2) {
                $this->warn("⚠️ Skipping unexpected entry: {$zipEntry}");
                continue;
            }

            [$folder, $relativePath] = $pathParts;

            $allowed = ['services', 'packages', 'CMS', 'vendors'];
            if (!in_array($folder, $allowed)) {
                $this->warn("⚠️ Unknown folder '{$folder}' in: {$zipEntry}");
                continue;
            }

            $destinationPath = $template->storage_path . "/{$folder}/{$relativePath}";

            $destinationDir = dirname($destinationPath);
            try {
                File::ensureDirectoryExists($destinationDir, 0775, true, true);
            } catch (\Throwable $e) {
                $this->error("Failed to create directory: {$destinationDir}");
                $this->error($e->getMessage());
                return 1;
            }
            if (!is_writable($destinationDir)) {
                $this->error("Directory is not writable: {$destinationDir}");
                $this->error("Please fix permissions (e.g., chown/chmod) and retry.");
                return 1;
            }

            $bytes = @file_put_contents($destinationPath, stream_get_contents($sourceStream));
            if ($bytes === false) {
                $this->warn("⚠️ Failed to write: {$destinationPath}");
                continue;
            }

            $this->info("✅ Extracted: {$folder}/{$relativePath}");
        }

        $zip->close();
        $this->info("🎉 template.zip successfully extracted to individual folders.");
        return 0;
    }
}
