<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class RemovePackage extends Command
{
    protected $signature = 'remove:package {name?} {--vendor=} {--force}';
    protected $description = 'Remove a SPA package module (backend + Vue3 + Vuetify3) from packages/{Vendor}/{Module}';

    public function handle(): int
    {
        $fs = new Filesystem;

        // 1) Module / package name (raw input)
        $nameInput = $this->argument('name') ?: $this->ask('Package module name to remove (e.g. Admin, AdminPanel, admin-panel)');
        if (! $nameInput) {
            $this->error('Module name is required.');
            return self::FAILURE;
        }

        /*
         * Normalize module naming
         */
        $moduleStudly = Str::studly($nameInput);           // "SecondFeature"
        $moduleSlug   = Str::kebab($moduleStudly);         // "second-feature"

        // 2) Vendor name
        $vendorInput = $this->option('vendor') ?: 'systha';

        $vendorSlug   = Str::slug($vendorInput);           // "systha" / "my-company"
        $vendorStudly = Str::studly($vendorSlug);          // "Myapp" / "MyCompany"

        // Base paths (must match make:package)
        $basePathSlug         = base_path("packages/{$vendorSlug}/{$moduleSlug}");
        $basePathStudly       = base_path("packages/{$vendorStudly}/{$moduleStudly}");
        $vendorBasePathSlug   = base_path("packages/{$vendorSlug}");
        $vendorBasePathStudly = base_path("packages/{$vendorStudly}");
        $packagesRootPath     = base_path('packages');
        $publicAssetsPath     = public_path("websites/{$moduleSlug}");
        $publicAltAssetsPath  = public_path("website/{$moduleSlug}");
        $fullPackageName      = "{$vendorSlug}/{$moduleSlug}";
        $rootComposerPath     = base_path('composer.json');
        $bootstrapCacheDir    = base_path('bootstrap/cache');

        // Determine which path exists (prefer lowercase, fallback to Studly for backward compatibility)
        if ($fs->exists($basePathSlug)) {
            $basePath = $basePathSlug;
            $vendorBasePath = $vendorBasePathSlug;
            $pathType = 'lowercase';
        } elseif ($fs->exists($basePathStudly)) {
            $basePath = $basePathStudly;
            $vendorBasePath = $vendorBasePathStudly;
            $pathType = 'StudlyCase';
            $this->warn("Found package in old StudlyCase format. Consider updating to lowercase.");
        } else {
            $this->error("Package module directory does not exist at either: {$basePathSlug} or {$basePathStudly}");
            return self::FAILURE;
        }

        $this->info("Preparing to remove package module:");
        $this->line("  Vendor:        {$vendorStudly} ({$vendorSlug})");
        $this->line("  Module:        {$moduleStudly} ({$moduleSlug})");
        $this->line("  Package name:  {$fullPackageName}");
        $this->line("  Package path:  " . $this->relativePath($basePath) . " ({$pathType})");
        $this->line("  Assets path:   " . $this->relativePath($publicAssetsPath));
        $this->line("  Legacy path:   " . $this->relativePath($publicAltAssetsPath));
        $this->line('');

        if (! $fs->exists($basePath)) {
            $this->error("Package module directory does not exist at: {$basePath}");
            return self::FAILURE;
        }

        if (! $this->option('force')) {
            if (! $this->confirm('This will delete the package directory, its assets, and remove it from Composer. Continue?')) {
                $this->warn('Aborted by user.');
                return self::FAILURE;
            }
        }

        /* -------------------------------------------------------------
         | 0.5) Tell Composer to forget the package (MOST IMPORTANT)
         |      This updates composer.json + composer.lock + installed list
         * ------------------------------------------------------------- */
        $this->info("Running: composer remove {$fullPackageName} --no-scripts (if required)...");

        $removeOk = $this->runProcess(
            ['composer', 'remove', $fullPackageName, '--no-scripts'],
            base_path()
        );

        if (! $removeOk) {
            $this->warn("composer remove {$fullPackageName} failed or package not required.");
            $this->warn("Falling back to manual composer.json cleanup.");

            // Fallback: manually remove from composer.json require
            if ($fs->exists($rootComposerPath)) {
                $rootJson = json_decode($fs->get($rootComposerPath), true) ?: [];

                if (isset($rootJson['require'][$fullPackageName])) {
                    unset($rootJson['require'][$fullPackageName]);

                    $fs->put(
                        $rootComposerPath,
                        json_encode($rootJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    );

                    $this->info("Manually removed {$fullPackageName} from root composer.json require section.");
                }
            }
        } else {
            $this->info("composer remove {$fullPackageName} completed.");
        }

        /* -------------------------------------------------------------
         | 2) Delete package directory under packages/Vendor/Module
         * ------------------------------------------------------------- */
        $this->info("Deleting package module directory: " . $this->relativePath($basePath));

        if (! $fs->deleteDirectory($basePath)) {
            $this->error("Failed to delete directory: {$basePath}");
            return self::FAILURE;
        }

        if ($fs->exists($vendorBasePath) && $this->isDirectoryEmpty($vendorBasePath, $fs)) {
            $fs->deleteDirectory($vendorBasePath);
            $this->info("Vendor directory is now empty and has been removed: " . $this->relativePath($vendorBasePath));
        }

        if ($fs->exists($packagesRootPath) && $this->isDirectoryEmpty($packagesRootPath, $fs)) {
            $fs->deleteDirectory($packagesRootPath);
            $this->info('Root "packages" directory is now empty and has been removed.');
        }

        /* -------------------------------------------------------------
         | 2.5) If no packages left, remove path repository from composer.json
         * ------------------------------------------------------------- */
        if (! $fs->exists($packagesRootPath)) {
            if ($fs->exists($rootComposerPath)) {
                $rootJson = json_decode($fs->get($rootComposerPath), true) ?: [];

                if (isset($rootJson['repositories']) && is_array($rootJson['repositories'])) {
                    $repositories = $rootJson['repositories'];
                    $updated = false;

                    foreach ($repositories as $key => $repo) {
                        if (isset($repo['type']) && $repo['type'] === 'path' &&
                            isset($repo['url']) && $repo['url'] === 'packages/*/*') {
                            unset($repositories[$key]);
                            $updated = true;
                            break;
                        }
                    }

                    if ($updated) {
                        $rootJson['repositories'] = array_values($repositories); // reindex

                        $fs->put(
                            $rootComposerPath,
                            json_encode($rootJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                        );

                        $this->info('Removed path repository "packages/*/*" from composer.json as no local packages remain.');
                    }
                }
            }
        }

        /* -------------------------------------------------------------
         | 3) Delete built assets under public/websites/{module-slug}
         * ------------------------------------------------------------- */
        if ($fs->exists($publicAssetsPath)) {
            $this->info("Deleting built assets directory: " . $this->relativePath($publicAssetsPath));

            if (! $fs->deleteDirectory($publicAssetsPath)) {
                $this->warn("Failed to delete assets directory: {$publicAssetsPath}. You can remove it manually.");
            }
        } else {
            $this->line("Assets directory not found (already removed?): " . $this->relativePath($publicAssetsPath));
        }

        if ($fs->exists($publicAltAssetsPath)) {
            $this->info("Deleting legacy assets directory: " . $this->relativePath($publicAltAssetsPath));

            if (! $fs->deleteDirectory($publicAltAssetsPath)) {
                $this->warn("Failed to delete legacy assets directory: {$publicAltAssetsPath}. Remove manually if needed.");
            }
        } else {
            $this->line("Legacy assets directory not found (already removed?): " . $this->relativePath($publicAltAssetsPath));
        }

        $this->line('');

        /* -------------------------------------------------------------
         | 4) HARD CLEAR: bootstrap/cache PHP files (packages.php, services.php, etc.)
         * ------------------------------------------------------------- */
        if ($fs->exists($bootstrapCacheDir)) {
            $this->info('Clearing Laravel bootstrap/cache PHP files ...');

            foreach ($fs->files($bootstrapCacheDir) as $file) {
                if (str_ends_with($file->getFilename(), '.php')) {
                    $fs->delete($file->getPathname());
                }
            }

            $this->info('bootstrap/cache cleared (PHP cache files removed).');
        }

        $this->line('');

        /* -------------------------------------------------------------
         | 5) Composer dump-autoload (NO SCRIPTS) + Laravel cache clear
         * ------------------------------------------------------------- */
        $this->info('Running "composer dump-autoload --no-scripts"...');

        if (! $this->runProcess(['composer', 'dump-autoload', '--no-scripts'], base_path())) {
            $this->warn('composer dump-autoload --no-scripts failed. Run it manually if autoload issues occur.');
        }

        $this->info('Clearing Laravel caches via "php artisan optimize:clear"...');
        $this->runProcess(['php', 'artisan', 'optimize:clear'], base_path());

        $this->info('Laravel caches cleared and rebuilt.');

        $this->line('');
        $this->info('✅ Package module removed completely (code, assets, Composer, and discovery manifest).');
        $this->line('');

        return self::SUCCESS;
    }

    protected function runProcess(array $command, string $cwd): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && $command[0] === 'npm') {
            $command[0] = 'npm.cmd';
        }

        $process = new Process($command, $cwd);
        $process->setTimeout(null);

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->isSuccessful();
    }

    protected function relativePath(string $path): string
    {
        return str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
    }

    protected function isDirectoryEmpty(string $path, Filesystem $fs): bool
    {
        $files = $fs->files($path);
        $dirs  = $fs->directories($path);

        return empty($files) && empty($dirs);
    }
}
