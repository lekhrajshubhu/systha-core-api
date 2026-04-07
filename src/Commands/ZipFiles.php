<?php

namespace Systha\Core\Commands;

use ZipArchive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Systha\Core\Models\Package;
use Systha\Core\Models\Service;
use Systha\Core\Models\VendorTemplate;

class ZipFiles extends Command
{
    protected $signature = 'zip:files {--templateId=}';
    protected $description = 'Zip the thumb images of packages for the vendor of selected vendor template';

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

        // $vendorId = $template->vendor_id;


        // $zipPath = storage_path("app/exports/template.zip");
        // $zipPath = base_path("vendor/systha/{$template->template_location}/resources/template.zip");
        // File::ensureDirectoryExists(dirname($zipPath));

        // $zip = new ZipArchive;
        $zipPath = base_path("vendor/systha/{$template->template_location}/resources/data/template.zip");

        if (File::exists($zipPath)) {
            $backupDir = base_path("vendor/systha/{$template->template_location}/resources/data_backup");
            $date = date('Y-m-d_h-i');
            $backupBase = pathinfo($zipPath, PATHINFO_FILENAME);
            $backupExt = pathinfo($zipPath, PATHINFO_EXTENSION);
            $backupName = $backupBase . '-' . $date . ($backupExt ? '.' . $backupExt : '');
            $newZipPath = $backupDir . '/' . $backupName;

            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Move the old file to the data_backup directory
            File::move($zipPath, $newZipPath);
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("❌ Failed to create zip file.");
            return 1;
        }

        $packageFiles = $this->zipPackageThumbs($template, $zip);
        $serviceFiles = $this->zipServiceFiles($template, $zip);
        $templateFiles = $this->zipTemplateFiles($template, $zip);
        $vendorFiles = $this->zipVendorFolder($template, $zip);

        $zip->close();

        $this->info("✅ Zipped {$packageFiles} package thumb images.");
        $this->info("✅ Zipped {$serviceFiles} service images.");
        $this->info("✅ Zipped {$templateFiles} template images.");
        $this->info("✅ Zipped {$vendorFiles} vendor files.");
        $this->info("🎉 Zip file created at: {$zipPath}");

        return 0;
    }

    /**
     * Zip package thumb images and return number of files added
     */
    private function zipPackageThumbs(VendorTemplate $template, ZipArchive $zip): int
    {
        $vendor = $template->vendor;

        $packages = Package::where([
            "vendor_id" => $vendor->id,
            "is_deleted" => 0,
        ])->get();

        if ($packages->isEmpty()) {
            $this->warn("No packages found for vendor ID: {$vendor->id}");
            return 0;
        }

        $addedFiles = 0;

        foreach ($packages as $package) {
            $paths = [];

            if ($package->package_thumb) {
                $paths[] = $template->storage_path . "/packages/images/" . $package->package_thumb;
            }

            if ($package->thumb) {
                $paths[] = $template->storage_path . "/packages/images/" . $package->thumb->file_name;
            }

            foreach ($paths as $fileFullPath) {
                if (File::exists($fileFullPath)) {
                    $filenameInZip = "packages/images/" . basename($fileFullPath);
                    $zip->addFile($fileFullPath, $filenameInZip);
                    $addedFiles++;
                } else {
                    $this->warn("Package image not found: {$fileFullPath}");
                }
            }
        }

        return $addedFiles;
    }


    private function zipServiceFiles(VendorTemplate $template, ZipArchive $zip): int
    {
        $vendor = $template->vendor;

        $services = Service::where([
            "vendor_id" => $vendor->id,
            "is_deleted" => 0,
        ])->get();

        if ($services->isEmpty()) {
            $this->warn("No services found for vendor ID: {$vendor->id}");
            return 0;
        }

        $addedFiles = 0;

        foreach ($services as $service) {
            if ($service->image) {
                $fileFullPath = $template->storage_path . "/services/images/" . $service->image;

                if (File::exists($fileFullPath)) {
                    $filenameInZip = "services/images/" . basename($fileFullPath);
                    $zip->addFile($fileFullPath, $filenameInZip);
                    $addedFiles++;
                } else {
                    $this->warn("Service image not found: {$fileFullPath}");
                }
            }
        }

        return $addedFiles;
    }


    private function zipTemplateFiles(VendorTemplate $template, ZipArchive $zip): int
    {
        $vendor = $template->vendor;
        $menus = $template->menus()->with([
            'subMenus.components.posts',
            'components.posts'
        ])->get();

        $addedFiles = 0;

        foreach ($menus as $menu) {
            $addedFiles += $this->exportFiles($menu, $template, $zip);
        }

        return $addedFiles;
    }

    private function exportFiles($menu, VendorTemplate $template, ZipArchive $zip): int
    {
        $addedFiles = 0;

        // Export files from components of this menu
        foreach ($menu->components as $component) {
            foreach ($component->posts as $post) {
                if ($post->post_image) {
                    $imagePath = $template->storage_path . "/CMS/post/{$post->post_image}";

                    if (File::exists($imagePath)) {
                        $pathInZip = "CMS/post/" . basename($imagePath);
                        $zip->addFile($imagePath, $pathInZip);
                        $addedFiles++;
                    } else {
                        $this->warn("Missing post image: {$imagePath}");
                    }
                }
            }
        }

        // Recursively export from submenus
        foreach ($menu->subMenus as $subMenu) {
            $addedFiles += $this->exportFiles($subMenu, $template, $zip);
        }

        return $addedFiles;
    }

    /**
     * Zip the entire vendor folder recursively and return number of files added
     */
    private function zipVendorFolder(VendorTemplate $template, ZipArchive $zip): int
    {
        $basePath = $template->storage_path."/venndors";

        if (!File::exists($basePath)) {
            $this->warn("Vendor folder not found: {$basePath}");
            return 0;
        }

        $addedFiles = 0;
        $files = File::allFiles($basePath);
        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $zipPath = "venndors/" . $relativePath;
            $zip->addFile($file->getRealPath(), $zipPath);
            $addedFiles++;
        }

        return $addedFiles;
    }
}
