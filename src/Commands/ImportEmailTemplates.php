<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Systha\Core\Models\EmailTemplate;
use Systha\Core\Models\VendorTemplate;

class ImportEmailTemplates extends Command
{
    protected $signature = 'import:email-templates {--templateId=}';
    protected $description = 'Import email templates from JSON for a selected vendor template';

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
                'Select a vendor template to import email templates for:',
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
        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/vendor_email_templates.json");

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
            $this->warn("Deleting all existing email templates for vendor ID {$vendorId}...");
            EmailTemplate::where('vendor_id', $vendorId)->delete();
            $this->info("All email templates deleted.");
        }

        $json = file_get_contents($filePath);
        $templatesData = json_decode($json, true);

        if (empty($templatesData)) {
            $this->error("No email template data found in the file.");
            return;
        }

        foreach ($templatesData as $data) {
            EmailTemplate::updateOrCreate(
                [
                    'vendor_id' => $vendorId,
                    'code'      => $data['code'] ?? null,
                ],
                [
                    'table_name'        => $data['table_name'] ?? null,
                    'table_id'          => $data['table_id'] ?? null,
                    'section'           => $data['section'] ?? null,
                    'subject'           => $data['subject'] ?? null,
                    'temp_type'         => $data['temp_type'] ?? null,
                    'temp_name'         => $data['temp_name'] ?? null,
                    'temp_html'         => $data['temp_html'] ?? null,
                    'temp_vendor'       => $data['temp_vendor'] ?? null,
                    'subject_vendor'    => $data['subject_vendor'] ?? null,
                    'temp_msg'          => $data['temp_msg'] ?? null,
                    'temp_json'         => $data['temp_json'] ?? null,
                ]
            );
        }

        $this->info("Import complete for vendor template: {$template->template_name}");
    }
}
