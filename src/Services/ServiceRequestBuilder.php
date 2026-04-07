<?php

namespace Systha\Core\Services;

use Illuminate\Http\Request;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Service;

class ServiceRequestBuilder
{
      protected $clientService;

      public function __construct(ClientService $clientService)
      {
            $this->clientService = $clientService;
      }

      public function build(Request $request, array $validated, $vendor)
      {


       
            $result = $this->clientService->createClient($validated, $vendor);

            $result["selectedServices"] = $this->prepareSelectedServices($request->service_selected, $vendor);
            $result["vendor"] = $vendor;

            return $result;
      }

      public function getVendor(Request $request): Vendor
      {
            if (auth('webContact')->check()) {
                  $user = auth('webContact')->user();
                  $vendor = $user->vendor;
            } else {
                  $vendorCode = request()->header('vendor-code');
                  if (!$vendorCode) {
                        throw new \Exception('Invalid request');
                  }
                  $vendor = Vendor::where('vendor_code', $vendorCode)->first();
                  if (!$vendor) {
                        throw new \Exception('Invalid request');
                  }
            }
            return $vendor;
      }

      protected function prepareSelectedServices(array $serviceSelected, $vendor)
      {
            $selectedServices = [];

            foreach ($serviceSelected as $item) {
 
                  if(in_array($item["type"],['service','fixed','variable'])){
                        $service = Service::find($item['id'] ?? null);
                        if (!$service) {
                              throw new \Exception("Service not found for ID: {$item['id']}");
                        }

                        $selectedServices[] = [
                              'unit_type' => $service['unit_type'],
                              'service_id' => $service['id'],
                              'service_name' => $service['service_name'],
                              'quantity' => $serviceItem['quantity'] ?? 1,
                              'cancel_charge' => $service['cancel_charge'],
                              'amend_charge' => $service['amend_charge'],
                              'type' => $service['type'],
                              'price' => $service['price'],
                              'vendor_id' => $vendor['id'],
                        ];
                  }
            }

            return $selectedServices;
      }
}
