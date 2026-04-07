<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Package;
use Systha\Core\Models\Service;
use Illuminate\Support\Facades\Storage;
use Systha\Core\Models\PackageType;
use Systha\Core\Models\PackageService;
use Systha\Core\Models\PackageSubscription;
use Systha\Core\Models\VendorTemplate;

class ImportServicePackage extends Command
{
    protected $signature = 'import:package {--templateId=}';
    protected $description = 'Import packages and plans from JSON for a selected template';

    public function handle()
    {
        $templateId = $this->option('templateId');

        if ($templateId) {
            $template = VendorTemplate::with('vendor')->find($templateId);
        } else {
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
        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/vendor_packages.json");

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        // Ask if fresh or update
        $installType = $this->choice(
            'Do you want a fresh install (delete all data) or update existing data?',
            ['fresh', 'update'],
            1 // default is update
        );

        if ($installType === 'fresh') {
            $this->warn("Deleting all existing packages, plans, and package services for vendor ID {$vendorId}...");

            // Delete package services
            PackageService::whereIn('package_id', function ($q) use ($vendorId) {
                $q->select('id')->from('packages')->where('vendor_id', $vendorId);
            })->delete();

            $plansIds = PackageType::where('vendor_id', $vendorId)->pluck('id');
            
            PackageSubscription::whereIn('package_type_id',$plansIds)->delete();

            // Delete package types (plans)
            PackageType::whereIn('package_id', function ($q) use ($vendorId) {
                $q->select('id')->from('packages')->where('vendor_id', $vendorId);
            })->delete();

            // Delete packages
            Package::where('vendor_id', $vendorId)->delete();

            $this->info("All package-related data deleted.");
        }

        $json = file_get_contents($filePath);
        $packages = json_decode($json, true);

        if (empty($packages)) {
            $this->error("No package data found in the file.");
            return;
        }

        foreach ($packages as $data) {
            $package = Package::updateOrCreate(
                [
                    'vendor_id' => $vendorId,
                    'package_name' => $data['package_name'],
                ],
                [
                    'stripe_product_id' => $data['stripe_product_id'],
                    'slug' => $data['slug'],
                    'type' => $data['type'],
                    'package_thumb' => $data['package_thumb'],
                    'sub_total' => $data['sub_total'],
                    'tax_amount' => $data['tax_amount'],
                    'price' => $data['price'],
                    'description' => $data['description'],
                    'state' => $data['state'],
                    'is_active' => $data['is_active'],
                ]
            );

            // Import plans
            if (!empty($data['plans'])) {
                foreach ($data['plans'] as $planData) {
                    PackageType::updateOrCreate(
                        [
                            'vendor_id' => $vendorId,
                            'package_id' => $package->id,
                            'type_name' => $planData['type_name'],
                            'duration' => $planData['duration'],
                        ],
                        [
                            'discount_type' => $planData['discount_type'],
                            'amount' => $planData['amount'],
                            'description' => $planData['description'],
                            'stripe_price_id' => $planData['stripe_price_id'],
                            'advance_schedule_days' => $planData['advance_schedule_days'],
                            'remainder_frequency' => $planData['remainder_frequency'],
                            'state' => $planData['state'],
                            'is_active' => $planData['is_active'],
                        ]
                    );
                }
            }

            // Import services
            if (!empty($data['services'])) {
                foreach ($data['services'] as $serviceData) {
                    $service = Service::firstOrCreate(
                        [
                            'service_name' => $serviceData['service_name'],
                            'vendor_id' => $vendorId,
                        ],
                        [
                            'price' => $serviceData['price'] ?? 0,
                        ]
                    );

                    PackageService::updateOrCreate(
                        [
                            'package_id' => $package->id,
                            'service_name' => $serviceData['service_name'],
                        ],
                        [
                            'price' => $serviceData['price'],
                            'service_id' => $service->id,
                        ]
                    );
                }
            }
        }

        $this->info("Import complete for vendor: {$template->template_name}");
    }
}
