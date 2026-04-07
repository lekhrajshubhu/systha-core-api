<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Systha\Core\Models\VendorPaymentCredential;
use Systha\Core\Models\VendorTemplate;

class ExportVendorPaymentCredential extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:payment-credential {--templateId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export vendor payment credentials for a selected vendor to a JSON file';

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

    
        $vendorId = $template->vendor_id;


        $this->info("Exporting vendor payment credentials for vendor ID: {$vendorId}");

        $credentials = VendorPaymentCredential::where([
            'vendor_id' => $vendorId,
            'is_deleted' => 0,
            'status' => 'publish',
        ])->get();

        $filtered = $credentials->map(function ($credential) {
            return [
                'name' => $credential->name,
                'val1' => $credential->val1,
                'val2' => $credential->val2,
                'is_active' => $credential->is_active,
                'is_default' => $credential->is_default,
                'banner_image' => $credential->banner_image,
                'service_charge' => $credential->service_charge,
            ];
        });

        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/vendor_payment_credentials.json");

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
