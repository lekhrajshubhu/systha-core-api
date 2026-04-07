<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Systha\Core\Models\VendorTemplate;

class ExportVendorData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'export:template';

    /**
     * The console command description.
     */
    protected $description = 'Export all vendor-related data (vendor_defaults, service_categories, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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
        // dd($template);

        $this->info("Running export commands for Template: {$template->template_name}");

        // Run vendor default export
        $this->call('export:vendor-default', [
            '--templateId' => $templateId
        ]);

        // Run category export
        $this->call('export:category', [
            '--templateId' => $templateId
        ]);

        // Run category package
        $this->call('export:package', [
            '--templateId' => $templateId
        ]);

        // Run vendor template
        $this->call('export:vendor-template', [
            '--templateId' => $templateId
        ]);

         // Run vendor template
        $this->call('export:payment-credential', [
            '--templateId' => $templateId
        ]);

        // Run category package
        $this->call('zip:files', [
            '--templateId' => $templateId
        ]);

        $this->info('All exports complete.');
    }
}
