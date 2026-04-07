<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CreateTemplate extends Command
{
    protected $signature = 'create:template {name?} {--vendor=systha} {--force}';
    protected $description = 'Scaffold a fully independent package module (backend + default assets) under packages/{Vendor}/{Module}';

    public function handle(): int
    {
        $fs = new Filesystem;

        // 1) Module / package name (raw input)
        $nameInput = $this->argument('name') ?: $this->ask('Package module name (e.g. Admin, AdminPanel, admin-panel)');
        if (! $nameInput) {
            $this->error('Module name is required.');
            return self::FAILURE;
        }

        /*
         * Normalize module naming:
         * - Studly for PHP namespace & folder (SecondFeature)
         * - kebab-case for composer package name & routes (second-feature)
         * - lowercase no-dash for view namespace (secondfeature)
         */
        $moduleStudly = Str::studly($nameInput);           // "SecondFeature"
        $moduleSlug   = Str::kebab($moduleStudly);         // "second-feature"
        $moduleLower  = Str::lower(str_replace('-', '', $moduleSlug)); // "secondfeature"

        // 2) Vendor name
        $vendorInput = $this->option('vendor') ?: 'myapp';

        /*
         * Normalize vendor naming:
         * - slug (lowercase, hyphen) for composer (myapp, my-company)
         * - Studly for PHP namespace & folder (MyApp, MyCompany)
         */
        $vendorSlug   = Str::slug($vendorInput);           // "myapp" / "my-company"
        $vendorStudly = Str::studly($vendorSlug);          // "Myapp" / "MyCompany"

        // Base path + namespace + asset prefix (always package)
        $basePath     = base_path("packages/{$vendorStudly}/{$moduleStudly}");
        $phpNamespace = "{$vendorStudly}\\{$moduleStudly}";
        $assetPrefix  = 'websites';

        if ($fs->exists($basePath)) {
            if (! $this->option('force')) {
                $this->error("Package module already exists at: {$basePath}");
                $this->warn('Use --force to overwrite the existing package.');
                return self::FAILURE;
            }

            $this->warn("Package already exists at {$basePath}; removing due to --force.");
            $fs->deleteDirectory($basePath);
        }

        $this->info("Creating PACKAGE module at packages/{$vendorStudly}/{$moduleStudly}");
        $this->info("Composer package name will be: {$vendorSlug}/{$moduleSlug}");

        /* -------------------------------------------------------------
         | 4) Base structure (package layout)
         * ------------------------------------------------------------- */
        $fs->makeDirectory("{$basePath}/src/Http/Controllers", 0755, true);
        $fs->makeDirectory("{$basePath}/src/Console/Commands", 0755, true);
        $fs->makeDirectory("{$basePath}/routes", 0755, true);
        $fs->makeDirectory("{$basePath}/resources/views", 0755, true);
        $fs->makeDirectory("{$basePath}/resources/assets/css", 0755, true);
        $fs->makeDirectory("{$basePath}/resources/assets/js", 0755, true);

        /* -------------------------------------------------------------
         | 5) Service Provider (package)
         * ------------------------------------------------------------- */
        $routesPathPhp = "__DIR__ . '/../routes/web.php'";
        $viewsPathPhp  = "__DIR__ . '/../resources/views'";

        $providerTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__;

use Illuminate\Support\ServiceProvider;

class __MODULE_STUDLY__ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPackageCommands();
        }

        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__ROUTES_PATH__);
        }

        $this->loadViewsFrom(__VIEWS_PATH__, '__MODULE_LOWER__');
    }

    protected function registerPackageCommands(): void
    {
        $commandPath = __DIR__ . '/Console/Commands';

        if (! is_dir($commandPath)) {
            return;
        }

        $commands = [];

        foreach (glob($commandPath . '/*.php') as $file) {
            $class = '__NAMESPACE__\\Console\\Commands\\' . pathinfo($file, PATHINFO_FILENAME);

            if (class_exists($class)) {
                $commands[] = $class;
            }
        }

        if (! empty($commands)) {
            $this->commands($commands);
        }
    }
}
PHP;

        $provider = strtr($providerTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_LOWER__'  => $moduleLower,
            '__ROUTES_PATH__'   => $routesPathPhp,
            '__VIEWS_PATH__'    => $viewsPathPhp,
        ]);

        $fs->put("{$basePath}/src/{$moduleStudly}ServiceProvider.php", $provider);

        $this->info("Service provider created: {$phpNamespace}\\{$moduleStudly}ServiceProvider");

        $buildCommandTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class Build__MODULE_STUDLY__Command extends Command
{
    protected $signature = '__MODULE_SLUG__:build';
    protected $description = 'Build and publish the __MODULE_STUDLY__ package assets';

    public function handle(): int
    {
        $packagePath = base_path('packages/__VENDOR_STUDLY__/__MODULE_STUDLY__');
        $fs          = new Filesystem;

        if (! $fs->isDirectory($packagePath)) {
            $this->error("Package not found at {$packagePath}");
            return self::FAILURE;
        }

        $this->info('Running npm install (if needed) ...');

        if (! $this->runProcess(['npm', 'install'], $packagePath)) {
            $this->error('npm install failed for __MODULE_STUDLY__ package.');
            return self::FAILURE;
        }

        $this->info('Running npm run build ...');

        if (! $this->runProcess(['npm', 'run', 'build'], $packagePath)) {
            $this->error('npm run build failed for __MODULE_STUDLY__ package.');
            return self::FAILURE;
        }

        $this->info('Publishing Vite build to public/websites/__MODULE_SLUG__/build ...');
        Artisan::call('__MODULE_SLUG__:copy-build');

        $this->info('Copying static assets to public/websites/__MODULE_SLUG__/assets ...');
        Artisan::call('__MODULE_SLUG__:copy-assets');

        $this->info('__MODULE_STUDLY__ assets built and published successfully.');
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
}
PHP;

        $buildCommand = strtr($buildCommandTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_SLUG__'   => $moduleSlug,
            '__VENDOR_STUDLY__' => $vendorStudly,
            '__VENDOR_SLUG__'   => $vendorSlug,
        ]);

        $fs->put("{$basePath}/src/Console/Commands/Build{$moduleStudly}Command.php", $buildCommand);
        $this->info("Console build command created for {$moduleStudly}.");

        $copyBuildTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class Copy__MODULE_STUDLY__BuildCommand extends Command
{
    protected $signature = '__MODULE_SLUG__:copy-build';
    protected $description = 'Copy __MODULE_STUDLY__ build output into public/websites/__MODULE_SLUG__/build';

    public function handle(): int
    {
        $fs           = new Filesystem;
        $packagePath  = base_path('packages/__VENDOR_STUDLY__/__MODULE_STUDLY__');
        $buildPath    = "{$packagePath}/resources/build";
        $publicTarget = public_path('websites/__MODULE_SLUG__/build');

        if (! $fs->isDirectory($buildPath)) {
            $this->error("Build output not found at {$buildPath}. Run __MODULE_SLUG__:build first.");
            return self::FAILURE;
        }

        if ($fs->exists($publicTarget)) {
            $fs->deleteDirectory($publicTarget);
        }

        $fs->copyDirectory($buildPath, $publicTarget);
        $this->copyManifestFiles($fs, $publicTarget);
        $this->info("__MODULE_STUDLY__ build copied to {$publicTarget}.");

        return self::SUCCESS;
    }

    protected function copyManifestFiles(Filesystem $fs, string $publicTarget): void
    {
        $manifestSource = "{$publicTarget}/.vite/manifest.json";
        $buildManifest  = "{$publicTarget}/manifest.json";
        $rootManifest   = public_path('websites/__MODULE_SLUG__/manifest.json');

        if ($fs->exists($manifestSource)) {
            $fs->copy($manifestSource, $buildManifest);
            $fs->copy($manifestSource, $rootManifest);
        } else {
            $this->warn("Vite manifest not found at {$manifestSource}. Run npm run build and re-run __MODULE_SLUG__:copy-build.");
        }
    }
}
PHP;

        $copyBuildCommand = strtr($copyBuildTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_SLUG__'   => $moduleSlug,
            '__VENDOR_STUDLY__' => $vendorStudly,
        ]);

        $fs->put("{$basePath}/src/Console/Commands/Copy{$moduleStudly}BuildCommand.php", $copyBuildCommand);
        $this->info("Console copy-build command created for {$moduleStudly}.");

        $copyAssetsTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class Copy__MODULE_STUDLY__AssetsCommand extends Command
{
    protected $signature = '__MODULE_SLUG__:copy-assets';
    protected $description = 'Copy __MODULE_STUDLY__ assets into public/websites/__MODULE_SLUG__/assets';

    public function handle(): int
    {
        $fs           = new Filesystem;
        $packagePath  = base_path('packages/__VENDOR_STUDLY__/__MODULE_STUDLY__');
        $assetsPath   = "{$packagePath}/resources/assets";
        $publicTarget = public_path('websites/__MODULE_SLUG__/assets');

        if (! $fs->isDirectory($assetsPath)) {
            $this->error("Assets directory not found at {$assetsPath}.");
            return self::FAILURE;
        }

        if ($fs->exists($publicTarget)) {
            $fs->deleteDirectory($publicTarget);
        }

        $fs->copyDirectory($assetsPath, $publicTarget);
        $this->info("__MODULE_STUDLY__ assets copied to {$publicTarget}.");

        return self::SUCCESS;
    }
}
PHP;

        $copyAssetsCommand = strtr($copyAssetsTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_SLUG__'   => $moduleSlug,
            '__VENDOR_STUDLY__' => $vendorStudly,
        ]);

        $fs->put("{$basePath}/src/Console/Commands/Copy{$moduleStudly}AssetsCommand.php", $copyAssetsCommand);
        $this->info("Console copy-assets command created for {$moduleStudly}.");

        /* -------------------------------------------------------------
         | 6) Backend: Controller + routes/web.php
         * ------------------------------------------------------------- */
        $controllerTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Http\Controllers;

use Illuminate\Routing\Controller;

class __MODULE_STUDLY__Controller extends Controller
{
    public function index()
    {
        return view('__MODULE_LOWER__::index');
    }
}
PHP;

        $controller = strtr($controllerTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_LOWER__'  => $moduleLower,
        ]);

        $controllerPath = "{$basePath}/src/Http/Controllers/{$moduleStudly}Controller.php";
        $fs->put($controllerPath, $controller);

        $routesTemplate = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;
use __NAMESPACE__\Http\Controllers\__MODULE_STUDLY__Controller;

Route::get('/__MODULE_SLUG__', [__MODULE_STUDLY__Controller::class, 'index'])
    ->name('__MODULE_SLUG__.index');
PHP;

        $routes = strtr($routesTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_SLUG__'   => $moduleSlug,
        ]);

        $fs->put("{$basePath}/routes/web.php", $routes);
        $this->info("Backend (routes + controller) created under: {$basePath}");

        /* -------------------------------------------------------------
         | 7) Blade view shell
         * ------------------------------------------------------------- */
        $bladeTemplate = <<<'BLADE'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>__MODULE_STUDLY__ Module</title>

    @vite(['resources/assets/css/app.css', 'resources/assets/js/app.js'], 'websites/__MODULE_SLUG__/build')
</head>
<body>
    <div class="module-wrapper">
        <h1>__MODULE_STUDLY__ Module</h1>
        <p>Customize this view at <code>packages/__VENDOR_STUDLY__/__MODULE_STUDLY__/resources/views/index.blade.php</code>.</p>
    </div>
</body>
</html>
BLADE;

        $blade = strtr($bladeTemplate, [
            '__MODULE_STUDLY__'   => $moduleStudly,
            '__MODULE_SLUG__'     => $moduleSlug,
            '__ASSET_PREFIX__'    => $assetPrefix,
            '__VENDOR_STUDLY__'   => $vendorStudly,
        ]);

        $fs->put("{$basePath}/resources/views/index.blade.php", $blade);
        $this->info("Blade view created at: {$basePath}/resources/views/index.blade.php");

        /* -------------------------------------------------------------
         | 8) Default assets (CSS/JS)
         * ------------------------------------------------------------- */
        $assetsBase = "{$basePath}/resources/assets";

        $cssTemplate = <<<'CSS'
:root {
    color-scheme: light;
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    background-color: #f8fafc;
    color: #0f172a;
}

.module-wrapper {
    margin: 0 auto;
    max-width: 720px;
    padding: 3rem 1.5rem;
}
CSS;

        $jsTemplate = <<<'JS'
document.addEventListener('DOMContentLoaded', () => {
    console.info('Default module assets loaded. Customize resources/assets/js/app.js for your package.');
});
JS;

        $fs->put("{$assetsBase}/css/app.css", $cssTemplate);
        $fs->put("{$assetsBase}/js/app.js", $jsTemplate);

        $demoCssTemplate = <<<'CSS'
/* Demo stylesheet to show automatic asset discovery */
.demo-card {
    background-color: #e2e8f0;
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
    margin-top: 1.5rem;
}
CSS;

        $demoJsTemplate = <<<'JS'
document.addEventListener('DOMContentLoaded', () => {
    console.info('Demo JS file loaded automatically from resources/assets/js/demo.js');
});
JS;

        $fs->put("{$assetsBase}/css/demo.css", $demoCssTemplate);
        $fs->put("{$assetsBase}/js/demo.js", $demoJsTemplate);

        $this->info("Default assets created under {$assetsBase}.");

        /* -------------------------------------------------------------
         | 9) Module-local package.json + vite.config.js (Node)
         * ------------------------------------------------------------- */
        $this->info('Creating module-local package.json and vite.config.js ...');

        $nodePackageJson = <<<'JSON'
{
  "private": true,
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  },
  "devDependencies": {
    "vite": "^5.0.0"
  }
}
JSON;

        $fs->put("{$basePath}/package.json", $nodePackageJson);

        $viteConfigTemplate = <<<'JS'
import { defineConfig } from 'vite';
import path from 'node:path';

const flattenName = (key) => {
  const normalized = key.replace(/\\/g, '/');
  if (normalized.startsWith('resources/assets/')) {
    return normalized.replace('resources/assets/', '').replace(/\//g, '-').replace(/\.[^.]+$/, '');
  }
  return normalized.replace(/\//g, '-').replace(/\.[^.]+$/, '');
};

const inputEntries = {
  'resources/assets/js/app.js': path.resolve(__dirname, 'resources/assets/js/app.js'),
  'resources/assets/js/demo.js': path.resolve(__dirname, 'resources/assets/js/demo.js'),
  'resources/assets/css/app.css': path.resolve(__dirname, 'resources/assets/css/app.css'),
  'resources/assets/css/demo.css': path.resolve(__dirname, 'resources/assets/css/demo.css'),
};

export default defineConfig({
  root: __dirname,
  publicDir: false,
  build: {
    outDir: 'resources/build',
    manifest: true,
    emptyOutDir: true,
    rollupOptions: {
      input: inputEntries,
      output: {
        entryFileNames: (chunk) => {
          const key = chunk.facadeModuleId
            ? chunk.facadeModuleId.replace(__dirname + '/', '')
            : chunk.name;
          return `assets/${flattenName(key)}.js`;
        },
        assetFileNames: (chunkInfo) => {
          const ext = chunkInfo.name && chunkInfo.name.includes('.') ? '[extname]' : '.css';
          return `assets/${flattenName(chunkInfo.name || 'asset')}${ext}`;
        },
      },
    },
  },
  server: {
    port: 5175,
    strictPort: false,
  },
});
JS;

        $fs->put("{$basePath}/vite.config.js", $viteConfigTemplate);

        $this->info("Module-local Vite setup created at: {$basePath}");

        /* -------------------------------------------------------------
         | 10) Run npm install + npm run build
         * ------------------------------------------------------------- */
        $this->info('Running "npm install" inside package module (first time may take a while)...');

        if (! $this->runProcess(['npm', 'install'], $basePath)) {
            $this->error('npm install failed inside package module. You can run it manually:');
            $this->line("  cd " . $this->relativePath($basePath) . " && npm install && npm run build");
            return self::FAILURE;
        }

        $this->info('Running "npm run build" inside package module...');

        if (! $this->runProcess(['npm', 'run', 'build'], $basePath)) {
            $this->error('npm run build failed inside package module. Run it manually:');
            $this->line("  cd " . $this->relativePath($basePath) . " && npm run build");
            return self::FAILURE;
        }

        $packageBuildPath  = "{$basePath}/resources/build";
        $packageAssetsPath = "{$basePath}/resources/assets";
        $publicAssetsPath  = public_path("{$assetPrefix}/{$moduleSlug}");
        $publicBuildPath   = "{$publicAssetsPath}/build";
        $publicManifest    = "{$publicAssetsPath}/manifest.json";
        $publicAssetsCopy  = "{$publicAssetsPath}/assets";

        if ($fs->exists($publicAssetsPath)) {
            $fs->deleteDirectory($publicAssetsPath);
        }

        if (! $fs->exists($packageBuildPath)) {
            $this->warn("Build output not found at {$packageBuildPath}. Skipping publish to public path.");
        } else {
            $fs->copyDirectory($packageBuildPath, $publicBuildPath);

            $manifestSource = "{$publicBuildPath}/.vite/manifest.json";
            if ($fs->exists($manifestSource)) {
                $fs->copy($manifestSource, "{$publicBuildPath}/manifest.json");
                $fs->copy($manifestSource, $publicManifest);
            } else {
                $this->warn("Vite manifest not found at {$manifestSource}. Run npm run build again if @vite fails.");
            }

            $this->info("Assets built at {$packageBuildPath} and published to {$publicBuildPath}.");
        }

        if ($fs->exists($packageAssetsPath)) {
            $fs->copyDirectory($packageAssetsPath, $publicAssetsCopy);
            $this->info("Static assets copied to {$publicAssetsCopy}.");
        } else {
            $this->warn("Assets directory not found at {$packageAssetsPath}. Skipping static copy.");
        }

        /* -------------------------------------------------------------
         | 11) Package PHP composer.json + root composer.json integration
         * ------------------------------------------------------------- */
        $this->info('Creating PHP composer.json for the package ...');

        $fullPackageName = "{$vendorSlug}/{$moduleSlug}";

        // package composer.json
        $composerData = [
            'name'        => $fullPackageName,
            'description' => "Package {$moduleStudly}",
            'type'        => 'library',
            'autoload'    => [
                'psr-4' => [
                    "{$phpNamespace}\\" => 'src/',
                ],
            ],
            'extra'       => [
                'laravel' => [
                    'providers' => [
                        "{$phpNamespace}\\{$moduleStudly}ServiceProvider",
                    ],
                ],
            ],
        ];

        $composerJson = json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $fs->put("{$basePath}/composer.json", $composerJson);

        $this->info("Package composer.json created at: {$basePath}/composer.json");

        // Update ROOT composer.json so composer require/update works
        $rootComposerPath = base_path('composer.json');

        if ($fs->exists($rootComposerPath)) {
            $rootJson = json_decode($fs->get($rootComposerPath), true) ?: [];

            // Ensure repositories[path] entry exists
            $pathRepo = [
                'type'    => 'path',
                'url'     => 'packages/*/*',
                'options' => ['symlink' => true],
            ];

            $repositories = $rootJson['repositories'] ?? [];

            $alreadyHasPathRepo = false;
            foreach ($repositories as $repo) {
                if (
                    isset($repo['type'], $repo['url']) &&
                    $repo['type'] === 'path' &&
                    $repo['url'] === 'packages/*/*'
                ) {
                    $alreadyHasPathRepo = true;
                    break;
                }
            }

            if (! $alreadyHasPathRepo) {
                $repositories[]           = $pathRepo;
                $rootJson['repositories'] = $repositories;
                $this->info('Added path repository "packages/*/*" to root composer.json.');
            }

            // Ensure require entry exists
            if (! isset($rootJson['require'])) {
                $rootJson['require'] = [];
            }

            if (! isset($rootJson['require'][$fullPackageName])) {
                // *@dev allows usage even if minimum-stability is stable
                $rootJson['require'][$fullPackageName] = '*@dev';
                $this->info("Added {$fullPackageName}: \"*@dev\" to root composer.json require section.");
            }

            $fs->put(
                $rootComposerPath,
                json_encode($rootJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );

            $this->info('Root composer.json updated.');
        } else {
            $this->warn('Root composer.json not found; please add path repository and require entry manually.');
        }

        $this->line('');

        /* -------------------------------------------------------------
         | 10) Run composer update + clear cache automatically
         * ------------------------------------------------------------- */
        $this->info("Running composer update for package {$fullPackageName} ...");

        if (! $this->runProcess(['composer', 'update', $fullPackageName], base_path())) {
            $this->error("composer update failed. Run manually: composer update {$fullPackageName}");
        } else {
            $this->info("composer update completed successfully.");
        }

        $this->info('Clearing Laravel route cache...');
        $this->runProcess(['php', 'artisan', 'route:clear'], base_path());

        $this->info('Clearing Laravel optimize cache...');
        $this->runProcess(['php', 'artisan', 'optimize:clear'], base_path());

        $this->info('Laravel caches cleared.');

        $this->info('✅ Package module created and wired into composer.');

        $this->line('');
        $this->line('Final steps (if something failed above):');
        $this->line("1) composer update {$fullPackageName}");
        $this->line('2) php artisan route:clear');
        $this->line("3) php artisan serve");
        $this->line("4) Open: http://localhost:8000/{$moduleSlug}");
        return self::SUCCESS;
    }

    protected function runProcess(array $command, string $cwd): bool
    {
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

}
