<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FormInstall extends Command
{
    protected $signature = 'form:install {--templateLocation=}';
    protected $description = 'Install form components and SCSS into the given template location';

    public function handle()
    {
        // Check if the option is provided, otherwise ask
        $template_location = $this->option('templateLocation') 
            ?: $this->ask("Enter Template Location | e.g. tempcleaning");

        $baseVendorPath = base_path("vendor/systha/");
        $source = $baseVendorPath . "core/Publishable/views";
        $destination = $baseVendorPath . $template_location . "/src/views/";
        $destinationScss = $baseVendorPath . $template_location . "/resources/scss/";

        if (!File::exists($destination)) {
            $this->error("{$template_location} does not exist.");
            return;
        }

        $folders = [
            'components' => $destination . 'components/',
            'forms'      => $destination . 'forms/',
            'scss'       => $destinationScss,
        ];

        foreach ($folders as $folder => $destPath) {
            $sourcePath = $source . "/{$folder}";

            if (File::exists($sourcePath)) {
                File::ensureDirectoryExists($destPath);

                foreach (File::files($sourcePath) as $file) {
                    File::copy($file->getPathname(), $destPath . $file->getFilename());
                }

                $this->info(ucfirst($folder) . " files copied successfully.");
            } else {
                $this->warn("Source {$folder} folder does not exist.");
            }
        }
    }
}
