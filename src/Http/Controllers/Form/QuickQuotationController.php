<?php

namespace Systha\Core\Http\Controllers\Form;


use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Systha\Core\Models\Package;
use Systha\Core\Services\ClientService;
use Systha\Core\Services\InquiryService;
use Systha\Core\Services\ServiceRequestBuilder;
use Systha\Core\Http\Controllers\BaseController;

class QuickQuotationController extends BaseController
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


    public function quickQuotation(Request $request)
    {

        $validated = $request->validate([
            'address'       => ['required'],
            'address.full'  => ['nullable', 'string', 'max:255'],
            'address.add1'  => ['nullable', 'string', 'max:255'],
            'address.city'  => ['nullable', 'string', 'max:120'],
            'address.state' => ['nullable', 'string', 'max:120'],
            'address.zip'   => ['nullable', 'string', 'max:30'],
            'address.country' => ['nullable', 'string', 'max:120'],
            'address.lat'   => ['nullable', 'numeric', 'between:-90,90'],
            'address.lng'   => ['nullable', 'numeric', 'between:-180,180'],
            'place_id'      => ['nullable', 'string', 'max:200'],

            'lng'           => ['required', 'numeric', 'between:-180,180'],
            'lat'           => ['required', 'numeric', 'between:-90,90'],

            // 'roof_mode'     => ['required', 'in:custom,auto'], // adjust allowed values
            'roof_polygon'  => ['required_if:roof_mode,custom', 'string', 'json'],

            'roof_area_m2'  => ['nullable', 'numeric', 'min:0'],
            'roof_slope' => ['required', 'integer', 'exists:vendor_lookups,id'],

            // 'pricing_plan'  => ['nullable', 'integer'], // or exists:pricing_plans,id (if table)
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],

            'phone'         => ['required', 'string', 'max:30'], // or regex if needed
            'email'         => ['required', 'email', 'max:150'],
        ]);

        $roofArea = (float) ($validated['roof_area_m2'] ?? 0);

        $packages = Package::with(['services.service'])->where([
            "vendor_id" => $this->vendor->id,
            "category_id" => $validated["roof_slope"],
            "is_deleted" => 0,
        ])->get();

        $packages = $packages->map(function ($package) use ($roofArea) {
            $services = $package->services->map(function ($packageService) use ($roofArea) {
                $type = strtolower((string) (
                    $packageService->type ??
                    optional($packageService->service)->type ??
                    optional($packageService->service)->unit_type ??
                    'fixed'
                ));
                $price = (float) (
                    $packageService->price ??
                    optional($packageService->service)->price ??
                    0
                );

                $totalPrice = $type === 'variable' ? $price * $roofArea : $price;

                return [
                    'id' => $packageService->id,
                    'service_id' => $packageService->service_id,
                    'name' => $packageService->service_name ??
                        optional($packageService->service)->name ??
                        optional($packageService->service)->service_name,
                    'type' => $type,
                    'price' => $price,
                    'total_price' => $totalPrice,
                ];
            });


            return [
                'id' => $package->id,
                'name' => $package->package_name ?? $package->name ?? null,
                'services' => $services,
                'thumb' => $package->package_thumb,
                'description' => $package->description ?? null,
                'roof_area' => $roofArea,
                'total_price' => $services->sum('total_price'),
            ];
        });


        $template = view($this->viewPath . "::components._form_partials._quick_quotation_list", compact('packages'))->render();

        return response()->json([
            'packages' => $packages,
            'template' => $template,
        ], 200);
    }

    public function sendPackageDetails(Request $request)
    {
        $validated = $request->validate([
            'address'       => ['required'],
            'address.full'  => ['nullable', 'string', 'max:255'],
            'address.add1'  => ['nullable', 'string', 'max:255'],
            'address.city'  => ['nullable', 'string', 'max:120'],
            'address.state' => ['nullable', 'string', 'max:120'],
            'address.zip'   => ['nullable', 'string', 'max:30'],
            'address.country' => ['nullable', 'string', 'max:120'],
            'place_id'      => ['nullable', 'string', 'max:200'],

            'lng'           => ['required', 'numeric', 'between:-180,180'],
            'lat'           => ['required', 'numeric', 'between:-90,90'],

            'roof_polygon'  => ['required_if:roof_mode,custom', 'string', 'json'],
            'roof_area_m2'  => ['nullable', 'numeric', 'min:0'],
            'roof_slope'    => ['required', 'integer', 'exists:vendor_lookups,id'],

            'first_name'    => ['nullable', 'string', 'max:100', 'required_without:contact.fname'],
            'last_name'     => ['nullable', 'string', 'max:100', 'required_without:contact.lname'],
            'phone'         => ['nullable', 'string', 'max:30', 'required_without:contact.phone_no'],
            'email'         => ['nullable', 'email', 'max:150', 'required_without:contact.email'],
            'contact.fname' => ['nullable', 'string', 'max:255', 'required_without:first_name'],
            'contact.lname' => ['nullable', 'string', 'max:255', 'required_without:last_name'],
            'contact.email' => ['nullable', 'email', 'max:255', 'required_without:email'],
            'contact.phone_no' => ['nullable', 'string', 'max:20', 'required_without:phone'],

            'package_id'    => ['required', 'integer', 'exists:packages,id'],
            'package_name'  => ['nullable', 'string', 'max:255'],
            'total_price'   => ['required', 'numeric', 'min:0'],
        ]);

        // dd($validated);
        
        $roofArea = (float) ($validated['roof_area_m2'] ?? 0);

        $package = Package::with(['services.service'])->where([
            'id' => $validated['package_id'],
            'vendor_id' => $this->vendor->id,
            'is_deleted' => 0,
        ])->first();

        if (!$package) {
            return response()->json([
                'error' => true,
                'message' => 'Package not found for this vendor.',
            ], 404);
        }

        $services = $package->services->map(function ($packageService) use ($roofArea) {
            // dd($packageService);
            $type = strtolower((string) (
                $packageService->type ??
                optional($packageService->service)->type ??
                optional($packageService->service)->unit_type ??
                'fixed'
            ));
            $price = (float) (
                $packageService->price ??
                optional($packageService->service)->price ??
                0
            );

            $totalPrice = $type === 'variable' ? $price * $roofArea : $price;

            return [
                'id' => $packageService->service_id,
                'service_id' => $packageService->service_id,
                'name' => $packageService->service_name ??
                    optional($packageService->service)->name ??
                    optional($packageService->service)->service_name,
                'type' => $type,
                'price' => $price,
                'total_price' => $totalPrice,
            ];
        });

        // dd($services);

        // dd($services);
        
        $calculatedTotal = $services->sum('total_price');
        // $packageName = $package->package_name ?? $package->name ?? $validated['package_name'];

        // $addressInput = $request->input('address');
        // $addressObj = is_array($addressInput) ? $addressInput : [
        //     'full' => (string) $addressInput,
        //     'add1' => null,
        //     'city' => null,
        //     'state' => null,
        //     'zip' => null,
        //     'country' => null,
        // ];
        // $addressObj['lat'] = $addressObj['lat'] ?? $request->input('lat');
        // $addressObj['lng'] = $addressObj['lng'] ?? $request->input('lng');

        // $contactInput = $request->input('contact');
        // $contactObj = is_array($contactInput) ? $contactInput : [
        //     'fname' => $validated['first_name'] ?? null,
        //     'lname' => $validated['last_name'] ?? null,
        //     'email' => $validated['email'] ?? null,
        //     'phone_no' => $validated['phone'] ?? null,
        // ];

        // return response()->json([
        //     'message' => 'Details request received.',
        //     'contact' => [
        //         'first_name' => $validated['first_name'] ?? $contactObj['fname'] ?? null,
        //         'last_name' => $validated['last_name'] ?? $contactObj['lname'] ?? null,
        //         'phone' => $validated['phone'] ?? $contactObj['phone_no'] ?? null,
        //         'email' => $validated['email'] ?? $contactObj['email'] ?? null,
        //         'contact' => $contactObj,
        //         'address' => $addressObj,
        //     ],
        //     'package' => [
        //         'id' => $package->id,
        //         'name' => $packageName,
        //         'total_price' => $calculatedTotal,
        //         'client_total_price' => (float) $validated['total_price'],
        //     ],
        //     'services' => $services,
        //     'roof_slope' => $validated['roof_slope'],
        //     'roof_area' => $roofArea,
        //     'roof_polygon' => $validated['roof_polygon'],
        //     'lat' => $validated['lat'],
        //     'lng' => $validated['lng'],
        //     'place_id' => $validated['place_id'],

        // ], 200);

        $validated["services"] = $services;
        $request["service_selected"] = $services->toArray();

        try {
            $inquiry = DB::transaction(function () use ($validated, $request) {
                $result = $this->serviceRequestBuilder->build($request, $validated, $this->vendor);

                // dd($result["selectedServices"]);

                return $this->inquiryService->createInquiry([
                    'client_id' => $result['client']->id,
                    'vendor_id' => $result['vendor']->id,
                    'address_id' => $result['address']->id,
                    'preferred_date' => $validated['preferred_date'] ?? Carbon::tomorrow()->toDateString(),
                    'preferred_time' => $validated['preferred_time'] ??now()->format('H:i'),
                    'reviewable_history' => $validated['reviewable_history'] ?? null,
                    'inquiry_info' => [
                        "roof_area_m2" => $validated['roof_area_m2'],
                        "roof_slope" => $validated['roof_slope'],
                        "roof_polygon" => $validated['roof_polygon'],
                        "first_name" => $validated['first_name'],
                        "last_name" => $validated['last_name'],
                        "phone" => $validated['phone'],
                        "email" => $validated['email'],
                        "package_id" => $validated['package_id'],
                        "package_name" => $validated['package_name'],
                        "total_price" => $validated['total_price'],
                    ],
                    'is_recurring' => 0,
                    'recurring_frequency' => null,
                    'service_list' => $result['selectedServices'],
                ]);
            });

            $temp = view($this->viewPath . '::website.forms._inquiry_success')->render();
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
