<?php

namespace Systha\Core\Commands;


use Illuminate\Console\Command;
use Systha\Core\Models\VendorPaymentCredential;
use Systha\Core\Models\VendorTemplate;

class ImportVendorPaymentCredential extends Command
{
    protected $signature = 'import:payment-credential {--templateId=}';
    protected $description = 'Import vendor payment credentials for a selected vendor from JSON';

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
                'Select a vendor template to:',
                $templates->map(fn($t) => "{$t->id} - {$t->template_name}")->toArray()
            );

            $templateId = (int) explode(' - ', $choice)[0];
            $template = VendorTemplate::with('vendor')->find($templateId);
        }

        if (!$template || !$template->vendor) {
            $this->error("Vendor or Template not found.");
            return 1;
        }

        $this->info("Importing vendor payment credentials for vendor: {$template->template_name}");

        $vendorId = $template->vendor_id;


        $mode = $this->choice(
            'Import mode?',
            ['fresh (delete + insert)', 'update (no delete)'],
            1
        );

        if ($mode === 'fresh (delete + insert)') {
            $this->warn("Deleting existing vendor payment credentials for vendor: {$vendorId}...");
            VendorPaymentCredential::where([
                'vendor_id' => $vendorId,
            ])->delete();
        }

        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/vendor_payment_credentials.json");

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $json = file_get_contents($filePath);
        $credentials = json_decode($json, true);

        if (!is_array($credentials)) {
            $this->error("Invalid JSON in: {$filePath}");
            return 1;
        }

        foreach ($credentials as $entry) {
            $payload = [
                'name' => $entry['name'],
                'vendor_id' => $vendorId,
                'val1' => $entry['val1'] ?? null,
                'val2' => $entry['val2'] ?? null,
                'banner_image' => $entry['banner_image'] ?? null,
                'service_charge' => $entry['service_charge'] ?? null,
                'is_active' => $entry['is_active'] ?? 1,
                'is_default' => $entry['is_default'] ?? 0,
                'status' => 'publish',
            ];

            VendorPaymentCredential::updateOrCreate(
                [
                    'vendor_id' => $vendorId,
                ],
                $payload
            );
        }


        $this->info("Import complete for vendor: {$template->template_name}");
    }
}
