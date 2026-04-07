<?php

namespace Systha\Core\Commands;


use Illuminate\Console\Command;
use Systha\Core\Models\Vendor;
use Illuminate\Support\Facades\Storage;
use Systha\Core\Models\VendorDefault;
use Systha\Core\Models\VendorTemplate;

class ImportVendorDefault extends Command
{
    protected $signature = 'import:vendor-default {--templateId=}';
    protected $description = 'Import vendor_defaults data from JSON for a selected template';

    public function handle()
    {
        // $vendorId = $this->option('vendor');

        // if (!$vendorId) {
        //     $this->info('Fetching vendors...');
        //     $vendors = Vendor::select('id', 'name')->get();

        //     if ($vendors->isEmpty()) {
        //         $this->error('No vendors found.');
        //         return;
        //     }

        //     // Map of index => vendor
        //     $vendorList = $vendors->values(); // reset keys to 0,1,2,...

        //     $options = $vendorList->pluck('name')->toArray();


        //     // Show list by name, get selected index
        //     $selectedIndex = $this->choice('Select a vendor:', $options);

        //     // Find selected vendor using index
        //     $selectedVendor = $vendorList->firstWhere('name', $selectedIndex);

        //     if (!$selectedVendor) {
        //         $this->error('Vendor not found.');
        //         return;
        //     }

        //     $vendorId = $selectedVendor->id;
        // } else {
        //     $selectedVendor = Vendor::find($vendorId);
        //     if (!$selectedVendor) {
        //         $this->error("Vendor with ID {$vendorId} not found.");
        //         return;
        //     }
        // }

        // $fileName = "vendor_defaults.json";

        // if (!Storage::exists($fileName)) {
        //     $this->error("File not found: storage/app/{$fileName}");
        //     return;
        // }

        // $data = json_decode(Storage::get($fileName), true);

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

        // dd($template);

        $this->info("Importing categories for vendor: {$template->template_name}");

        $vendorId = $template->vendor_id;
        // dd($template->template_location);
        $filePath = base_path("vendor/systha/{$template->template_location}/resources/data/vendor_defaults.json");




        // dd($filePath);
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $json = file_get_contents($filePath);
        $data = json_decode($json, true);


        if (!$data || !is_array($data)) {
            $this->error("Invalid JSON format in vendor_defaults.json");
            return;
        }

        // Insert each row
        // Insert or update each row
        foreach ($data as $item) {
            unset($item['id']); // Avoid duplicate ID issues
            $item['vendor_id'] = $vendorId;

            // Assuming the unique identifier in your table is "property"
            VendorDefault::updateOrCreate(
                [
                    'vendor_id' => $vendorId,
                    'property' => $item['property'], // unique key for upsert
                ],
                $item // values to update/insert
            );
        }


        $this->info("Imported " . count($data) . " vendor_defaults for vendor ID: {$vendorId}");
    }
}
