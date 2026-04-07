<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class SetupTemplates extends Command
{
    protected $signature = 'setup:templates';
    protected $description = 'Run setup commands for all Systha packages';

    public function handle(): int
    {
        $packagesPath = base_path('vendor/systha');
        $commands = collect(File::directories($packagesPath))
            ->map(fn (string $path) => basename($path))
            ->reject(fn (string $slug) => $slug === 'core')
            ->filter(function (string $slug) use ($packagesPath) {
                $classFile = $packagesPath . '/' . $slug . '/src/Console/Commands/' . Str::studly($slug) . 'SetupCommand.php';
                return File::exists($classFile);
            })
            ->map(fn (string $slug) => "{$slug}:setup")
            ->values()
            ->all();

        $this->info('Running setup commands for Systha packages...');
        
        Artisan::call('auth:replace-config');
        
        $hasFailures = false;

        foreach ($commands as $command) {
            $application = $this->getApplication();
            if (!$application || !$application->has($command)) {
                $this->warn("Skipping {$command} (command not registered).");
                continue;
            }

            $this->info("Running {$command}...");
            $exitCode = $this->call($command);
            if ($exitCode !== self::SUCCESS) {
                $hasFailures = true;
                $this->warn("{$command} exited with code {$exitCode}.");
            }
        }

        if ($hasFailures) {
            $this->warn('One or more setup commands reported a failure.');
            return self::FAILURE;
        }

        $this->info('All setup commands completed.');
        return self::SUCCESS;
    }
}
