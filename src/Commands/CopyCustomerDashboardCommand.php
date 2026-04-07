<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CopyCustomerDashboardCommand extends Command
{
    protected $signature = 'copy:core';
    protected $description = 'Copy core assets into public/build';

    public function handle(): int
    {
        $fs           = new Filesystem;
        $packagePath  = base_path('vendor/systha/core');
        $targets = [
            [
                'name'        => 'assets',
                'source'      => "{$packagePath}/Publishable/public/build",
                'destination' => public_path('build'),
            ],
            [
                'name'        => 'images',
                'source'      => "{$packagePath}/Publishable/public/images",
                'destination' => public_path('images'),
            ],
        ];

        foreach ($targets as $target) {
            if (! $fs->isDirectory($target['source'])) {
                $this->warn("{$target['name']} directory not found at {$target['source']}. Skipping.");
                continue;
            }

            if ($fs->exists($target['destination'])) {
                $fs->deleteDirectory($target['destination']);
            }

            $fs->copyDirectory($target['source'], $target['destination']);
            $this->info("Golo {$target['name']} copied to {$target['destination']}.");
        }

        return self::SUCCESS;
    }
}
