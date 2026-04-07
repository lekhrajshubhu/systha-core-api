<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Systha\Core\Models\EmailTemplate;
use Systha\Core\Models\VendorTemplate;

class ExportEmailTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:email-templates {--templateId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export email templates for a selected vendor to a JSON file';

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
                'Select a vendor template to export email templates for:',
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

        $this->info("Exporting email templates for vendor ID: {$vendorId}");

        $emailTemplates = EmailTemplate::where([
            'vendor_id' => $vendorId,
            'is_deleted' => 0,
        ])->get();

        if ($emailTemplates->isEmpty()) {
            $this->warn('No email templates found for this vendor.');
            return 0;
        }

        $filtered = $emailTemplates->map(function ($template) {
            return [
              
                'section'           => $template->section,
                'subject'           => $template->subject,
                'code'              => $template->code,
                'temp_type'         => $template->temp_type,
                'temp_name'         => $template->temp_name,
                'temp_html'         => $template->temp_html,
                'temp_vendor'       => $template->temp_vendor,
                'subject_vendor'    => $template->subject_vendor,
                'temp_msg'          => $template->temp_msg,
                'temp_json'         => $template->temp_json,
            ];
        })->toArray();

        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/vendor_email_templates.json");

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


        // Write new JSON file
        file_put_contents($filePath, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Export complete: {$filePath}");
    }
}
