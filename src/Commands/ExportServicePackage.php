<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Systha\Core\Models\Package;
use Systha\Core\Models\VendorTemplate;

class ExportServicePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:package {--templateId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export packages for a selected vendor with plans to a JSON file';

    /**
     * Execute the console command.
     */
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


        $this->info("Exporting packages for vendor ID: {$vendorId}");

        $packages = Package::with(['plans', 'services'])->where([
            'vendor_id' => $vendorId,
            'is_deleted' => 0
        ])->get();

        $filtered = $packages->map(function ($package) {
            return [
                'stripe_product_id' => $package->stripe_product_id,
                'package_name' => $package->package_name,
                'slug' => $package->slug,
                'type' => $package->type,
                'package_thumb' => $package->package_thumb,
                'sub_total' => $package->sub_total,
                'tax_amount' => $package->tax_amount,
                'price' => $package->price,
                'description' => $package->description,
                'state' => $package->state,
                'is_active' => $package->is_active,
                'plans' => $package->plans->map(function ($plan) {
                    return [
                        'vendor_id' => $plan->vendor_id,
                        'package_id' => $plan->package_id,
                        'type_name' => $plan->type_name,
                        'duration' => $plan->duration,
                        'discount_type' => $plan->discount_type,
                        'amount' => $plan->amount,
                        'description' => $plan->description,
                        'stripe_price_id' => $plan->stripe_price_id,
                        'advance_schedule_days' => $plan->advance_schedule_days,
                        'remainder_frequency' => $plan->remainder_frequency,
                        'state' => $plan->state,
                        'is_active' => $plan->is_active,
                    ];
                })->toArray(),
                'services'          => $package->services->map(function ($service) {
                    return [
                        'service_name' => $service->service_name,
                        'price'        => $service->price,
                    ];
                })->toArray(),
            ];
        });


        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/vendor_packages.json");

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
        file_put_contents($filePath, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Export complete: {$filePath}");
    }
}
