<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Systha\Core\Models\Vendor;
use Illuminate\Support\Facades\Schema;
use Systha\Core\Models\VendorTemplate;

class ImportVendorData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cms:install';

    /**
     * The console command description.
     */
    protected $description = 'import all vendor-related data (vendor_defaults, service_categories, etc.)';

    protected $pkgname = "";
    protected $template;
    protected $vendor;
    protected $templateLocation = "";
    protected $storageLocation = "";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vendors = Vendor::where(['state' => 'publish', 'is_deleted' => 0])->get();

        if ($vendors->isEmpty()) {
            exit('No vendor found');
        } else {
            $this->info('List of Vendors');
            foreach ($vendors as $vendor) {
                $this->info("ID: {$vendor->id}, Name: {$vendor->name}");
            }
        }

        $this->vendor = $this->ask("Enter vendor Id of template you want to install");
        $vendor = Vendor::find($this->vendor);
        if (!$vendor) {
            echo "Selected Vendor Not Found \n";
        }
        echo "Selected vendor is " . $vendor->name . "\n";
        $confirmation = $this->confirm('Setup New Vendor Template?');

        $hostColumn = Schema::hasColumn((new VendorTemplate)->getTable(), 'template_host') ? 'template_host' : 'host';

        if ($confirmation) {
            $template_name = $this->ask("Enter Template Name");
            $template_host = $this->ask("Enter Template Host");
            $this->templateLocation = $this->ask("Enter Template Location");
            $this->storageLocation = $this->ask("Enter Storage Location");
            $this->template = VendorTemplate::updateOrCreate([
                'vendor_id' => $vendor->id,
                $hostColumn => $template_host
            ], [
                'template_name' => $template_name,
                'template_location' => $this->templateLocation,
                'storage_path' => $this->storageLocation,
                'is_active' => 1
            ]);
        } else {
            $templates = VendorTemplate::where(['vendor_id' => $vendor->id, "is_deleted" => 0])->get();

            if ($templates->isEmpty()) {
                $this->error("No existing templates found for this vendor.");
                return;
            }

            // Map templates: display name => ID
            $choices = $templates->mapWithKeys(function ($template) use ($hostColumn) {
                $label = "{$template->template_name} ({$template->$hostColumn})";
                return [$label => $template->id]; // flip: label => id
            })->toArray();

            // Show choices, return selected ID
            $selectedLabel = $this->choice("Select a template to use", array_keys($choices));
            $selectedId = $choices[$selectedLabel];

            $this->template = VendorTemplate::find($selectedId);
            $this->storageLocation = $this->template->storage_path;
            $this->templateLocation = $this->template->template_location;
        }

        $templateId = $this->template->id;
        $vendorId = $vendor->id;

        $this->info("Running import commands for Vendor ID: {$vendorId}");

        $this->info("✅ Importing vendor defaults.");
        $this->call('import:vendor-default', [
            '--templateId' => $templateId
        ]);

        $this->info("✅ Importing service categories.");
        $this->call('import:category', [
            '--templateId' => $templateId
        ]);

        $this->info("✅ Importing service packages.");
        $this->call('import:package', [
            '--templateId' => $templateId
        ]);

        $this->info("✅ Importing vendor template.");
        $this->call('import:vendor-template', [
            '--templateId' => $templateId
        ]);

        $this->info("✅ Importing vendor payment credential.");
        $this->call('import:payment-credential', [
            '--templateId' => $templateId
        ]);

        $this->info("✅ Unzipping files from template location...");
        $this->call('unzip:files', [
            '--templateId' => $templateId
        ]);

        // // copy auth.php and vite.config.js
        // $this->info("🔄 Copying auth.php and vite.config.js files...");
        // $this->call('setup:frontend');

        // $this->info("✅ Copied assets from sourceDir to destinationDir");
        // $this->call('form:install',[
        //     '--templateLocation' => $this->templateLocation
        // ]);


        $this->info('All imports complete.');
    }
}
