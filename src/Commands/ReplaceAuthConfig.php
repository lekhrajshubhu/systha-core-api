<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ReplaceAuthConfig extends Command
{
    protected $signature = 'auth:replace-config';
    protected $description = 'Replaces config/auth.php and routes/web.php with versions from systha/core package';

    public function handle()
    {
        $packagePath = base_path('vendor/systha/core');

        // Define source and destination files
        $replacements = [
            [
                'source' => $packagePath . '/config/auth.php',
                'destination' => config_path('auth.php'),
                'label' => 'config/auth.php',
            ],
            [
                'source' => $packagePath . '/config/web.php',
                'destination' => base_path('routes/web.php'),
                'label' => 'routes/web.php',
            ],
        ];

        foreach ($replacements as $file) {
            if (!File::exists($file['source'])) {
                $this->error("❌ Source file not found: {$file['source']}");
                continue;
            }

            try {
                File::copy($file['source'], $file['destination']);
                $this->info("✅ Replaced {$file['label']} with the version from the package.");
            } catch (\Exception $e) {
                $this->error("❌ Failed to replace {$file['label']}: " . $e->getMessage());
            }
        }

        // Copy publishable public build assets to main public folder
        $buildSource = $packagePath . '/Publishable/public/build';
        $buildDestination = public_path('build');

        if (!File::exists($buildSource)) {
            $this->error("❌ Build folder not found: {$buildSource}");
        } else {
            try {
                if (File::exists($buildDestination)) {
                    File::deleteDirectory($buildDestination);
                }

                File::copyDirectory($buildSource, $buildDestination);
                $this->info('✅ Copied build assets to public/build.');
            } catch (\Exception $e) {
                $this->error('❌ Failed to copy build assets: ' . $e->getMessage());
            }
        }

        // Re-cache config after changes
        $this->callSilent('config:cache');
        $this->info("⚙️ Config cache refreshed.");

        return Command::SUCCESS;
    }
}
