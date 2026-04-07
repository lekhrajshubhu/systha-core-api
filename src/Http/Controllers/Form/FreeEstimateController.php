<?php

namespace Systha\Core\Http\Controllers\Form;


use Illuminate\Support\Facades\DB;
use Systha\Core\Models\Vendor;
use Systha\Core\Services\ClientService;
use Systha\Core\Services\InquiryService;
use Systha\Core\Http\Requests\InquiryRequest;
use Systha\Core\Services\ServiceRequestBuilder;
use Systha\Core\Http\Controllers\BaseController;


class FreeEstimateController extends BaseController
{

    protected $inquiryService, $clientService, $serviceRequestBuilder;

    public function __construct(
        InquiryService $inquiryService,
        ClientService $clientService,
        ServiceRequestBuilder $serviceRequestBuilder
    ) {

        parent::__construct(); // ✅ Call BaseController constructor

        $this->inquiryService = $inquiryService;
        $this->clientService = $clientService;
        $this->serviceRequestBuilder = $serviceRequestBuilder;
    }

    public function freeEstimate(InquiryRequest $request)
    {
        
        $validated = $request->validated();

        try {
            $inquiry = DB::transaction(function () use ($validated, $request) {

                if(isset($validated['vendor_code'])) {
                    $this->vendor = Vendor::where('vendor_code', $validated['vendor_code'])->first();
                
                }else{
                    $this->vendor = $this->vendor;
                }


                $result = $this->serviceRequestBuilder->build($request, $validated, $this->vendor);

                return $this->inquiryService->createInquiry([
                    'client_id' => $result['client']->id,
                    'vendor_id' => $result['vendor']->id,
                    'address_id' => $result['address']->id,
                    'preferred_date' => $validated['preferred_date'],
                    'preferred_time' => $validated['preferred_time'],
                    'reviewable_history' => $validated['reviewable_history'] ?? null,
                    'is_recurring' => 0,
                    'recurring_frequency' => null,
                    'service_list' => $result['selectedServices'],
                ]);
            });

            $temp = view($this->viewPath.'::components._form_partials._inquiry_success')->render();
            return response()->json([
                "message" => "Inquiry Added",
                "inquiry" => $inquiry,
                "temp" => $temp,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => true,
                "message" => $th->getMessage(),
                "line" => $th->getLine(),
                "file" => $th->getFile(),
            ], 422);
        }
    }
    public function freeEstimateGlobal(InquiryRequest $request)
    {
        
        $validated = $request->validated();

        try {
            $inquiry = DB::transaction(function () use ($validated, $request) {

                if(isset($validated['vendor_code'])) {
                    $this->vendor = Vendor::where('vendor_code', $validated['vendor_code'])->first();
                
                }else{
                    $this->vendor = $this->vendor;
                }

                $validated['is_global'] = 1;

                $result = $this->serviceRequestBuilder->build($request, $validated, $this->vendor);

                return $this->inquiryService->createInquiry([
                    'client_id' => $result['client']->id,
                    'vendor_id' => $result['vendor']->id,
                    'address_id' => $result['address']->id,
                    'preferred_date' => $validated['preferred_date'],
                    'preferred_time' => $validated['preferred_time'],
                    'reviewable_history' => $validated['reviewable_history'] ?? null,
                    'is_recurring' => 0,
                    'recurring_frequency' => null,
                    'service_list' => $result['selectedServices'],
                ]);
            });

            $temp = view($this->viewPath.'::components._form_partials._inquiry_success')->render();
            return response()->json([
                "message" => "Inquiry Added",
                "inquiry" => $inquiry,
                "temp" => $temp,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => true,
                "message" => $th->getMessage(),
                "line" => $th->getLine(),
                "file" => $th->getFile(),
            ], 422);
        }
    }
}
