<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Systha\Core\Http\Resources\NearByVendorResource;
use Systha\Core\Models\Company;
use Systha\Core\Models\ServiceCategory;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\VendorAvailability;
use Systha\Core\Models\VendorMembership;
use Systha\Core\Models\VendorMenuComponent;
use Systha\Core\Models\VendorTemplate;

/**
 * @group Platform
 * @subgroup Vendors
 */
class VendorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $company = $request->attributes->get('company');

            if (! $company instanceof Company) {
                $companyCode = $this->extractCompanyHeader($request);

                if (! $companyCode) {
                    return response(['error' => 'Company code header is required.'], 403);
                }

                $company = Company::where('code', $companyCode)->first();

                if (! $company) {
                    return response(['error' => 'Invalid company code.'], 403);
                }
            }

            $vendorIds = VendorMembership::query()
                ->where('company_id', $company->id)
                ->when(
                    Schema::hasColumn('vendor_memberships', 'is_deleted'),
                    fn ($q) => $q->where('is_deleted', 0)
                )
                ->pluck('vendor_id');

            $currentLat = $request->query('lat');
            $currentLng = $request->query('lng', $request->query('lon'));
            $search = trim((string) $request->query('query', ''));
            $hasTagsColumn = Schema::hasColumn((new Vendor())->getTable(), 'tags');

            $vendors = Vendor::query()
                ->whereIn('id', $vendorIds)
                ->when(Schema::hasColumn('vendors', 'is_deleted'), fn ($q) => $q->where('is_deleted', 0))
                ->when($search !== '', function ($query) use ($search, $hasTagsColumn) {
                    $query->where(function ($q) use ($search, $hasTagsColumn) {
                        $q->where('name', 'like', '%' . $search . '%');

                        if ($hasTagsColumn) {
                            $q->orWhere('tags', 'like', '%' . $search . '%');
                        }

                        $q->orWhereHas('address', function ($addrQuery) use ($search) {
                            $addrQuery->where('add1', 'like', '%' . $search . '%')
                                ->orWhere('add2', 'like', '%' . $search . '%')
                                ->orWhere('city', 'like', '%' . $search . '%')
                                ->orWhere('state', 'like', '%' . $search . '%')
                                ->orWhere('zip', 'like', '%' . $search . '%');
                        });
                    });
                })
                ->with('address')
                ->get()
                ->map(function (Vendor $vendor) use ($currentLat, $currentLng) {
                    $address = $vendor->address;

                    $distance = null;
                    if (! is_null($currentLat) && ! is_null($currentLng) && $address) {
                        $distance = $this->calculateDistanceKm(
                            (float) $currentLat,
                            (float) $currentLng,
                            (float) ($address->lat ?? 0),
                            (float) ($address->lon ?? 0)
                        );
                    }

                    $stateZip = collect([$address->state ?? null, $address->zip ?? null])
                        ->filter()
                        ->implode(' ');

                    $addressLine = collect([
                        $address->add1 ?? null,
                        $address->city ?? null,
                        $stateZip !== '' ? $stateZip : null,
                    ])->filter()->implode(', ');

                    return [
                        'name' => $vendor->name,
                        'vendor_code' => $vendor->vendor_code,
                        'logo' => $vendor->logo,
                        'address' => $addressLine ?: null,
                        'distance_km' => $distance !== null ? round($distance, 2) : null,
                    ];
                });

            return response([
                'data' => $vendors,
            ]);
        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage()], 422);
        }
    }

    private function extractCompanyHeader(Request $request): ?string
    {
        return $request->headers->get('Company')
            ?? $request->headers->get('company')
            ?? $request->headers->get('Company-Code')
            ?? $request->headers->get('company-code')
            ?? $request->headers->get('AppCode')
            ?? $request->headers->get('App-Code')
            ?? $request->headers->get('appcode')
            ?? $request->headers->get('app-code');
    }

    public function nearby(Request $request)
    {
        try {
            $vendorType = trim((string) $request->query('type', ''));
            $search = trim((string) $request->query('query', ''));
            $currentLat = (float) $request->query('lat', 40.7128);
            $currentLng = (float) $request->query('lng', $request->query('lon', -74.0060));
            $perPage = (int) $request->query('per_page', 15);
            $hasTagsColumn = Schema::hasColumn((new Vendor())->getTable(), 'tags');

            if ($perPage < 1) {
                $perPage = 5;
            }
            if ($perPage > 100) {
                $perPage = 100;
            }

            $vendors = Vendor::query()
                ->where('is_deleted', 0)
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->with('address')
                ->when($vendorType !== '', function ($query) use ($vendorType) {
                    $query->where('type', $vendorType);
                })
                ->when($search !== '', function ($query) use ($search, $hasTagsColumn) {
                    $query->where(function ($q) use ($search, $hasTagsColumn) {
                        $q->where('name', 'like', '%' . $search . '%');
                        if ($hasTagsColumn) {
                            $q->orWhere('tags', 'like', '%' . $search . '%');
                        }
                    });
                })
                ->get();

            $vendorIds = $vendors->pluck('id');
            $availabilityByVendor = VendorAvailability::query()
                ->whereIn('vendor_id', $vendorIds)
                ->where('is_deleted', 0)
                ->orderByDesc('id')
                ->get()
                ->groupBy('vendor_id')
                ->map(function ($items) {
                    return $items->first();
                });

            $minPriceByVendor = DB::table('packages')
                ->join('package_types', 'package_types.package_id', '=', 'packages.id')
                ->where('packages.is_deleted', 0)
                ->where('package_types.is_deleted', 0)
                ->whereIn('packages.vendor_id', $vendorIds)
                ->groupBy('packages.vendor_id')
                ->select('packages.vendor_id', DB::raw('MIN(package_types.amount) as min_price'))
                ->pluck('min_price', 'packages.vendor_id');

            $vendors = $vendors->map(function ($vendor) use ($currentLat, $currentLng) {
                $vendorLat = $vendor->address->lat ?? 0;
                $vendorLng = $vendor->address->lon ?? 0;
                $vendor->distance_km = $this->calculateDistanceKm(
                    $currentLat, $currentLng, $vendorLat, $vendorLng
                );

                return $vendor;
            })->map(function ($vendor) use ($availabilityByVendor, $minPriceByVendor) {
                $availability = $availabilityByVendor->get($vendor->id);
                $vendor->nearest_available_day = $this->resolveNearestAvailableDay($availability?->working_days);
                $vendor->min_price = $minPriceByVendor->get($vendor->id);

                return $vendor;
            })->sortBy('distance_km')->values();

            // For simple pagination (optional)
            $page = (int) $request->query('page', 1);
            $paginated = $vendors->forPage($page, $perPage)->values();

            return response()->json([
                'data' => NearByVendorResource::collection($paginated),
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $vendors->count(),
                    'last_page' => (int) ceil($vendors->count() / $perPage),
                ],
            ]);
        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage()], 422);
        }
    }

    private function calculateDistanceKm($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371;

        $lat1 = deg2rad((float) $lat1);
        $lng1 = deg2rad((float) $lng1);
        $lat2 = deg2rad((float) $lat2);
        $lng2 = deg2rad((float) $lng2);

        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;

        $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function resolveNearestAvailableDay(?string $workingDays): ?string
    {
        if (!$workingDays) {
            return null;
        }

        $dayMap = [
            'sun' => 0, 'sunday' => 0,
            'mon' => 1, 'monday' => 1,
            'tue' => 2, 'tues' => 2, 'tuesday' => 2,
            'wed' => 3, 'wednesday' => 3,
            'thu' => 4, 'thur' => 4, 'thurs' => 4, 'thursday' => 4,
            'fri' => 5, 'friday' => 5,
            'sat' => 6, 'saturday' => 6,
        ];

        $availableDays = collect(explode(',', $workingDays))
            ->map(function ($day) use ($dayMap) {
                $key = strtolower(trim($day));
                return $dayMap[$key] ?? null;
            })
            ->filter(function ($value) {
                return $value !== null;
            })
            ->unique()
            ->values();

        if ($availableDays->isEmpty()) {
            return null;
        }

        $today = Carbon::today()->dayOfWeek;
        $minDiff = null;

        foreach ($availableDays as $day) {
            $diff = ($day - $today + 7) % 7;
            if ($minDiff === null || $diff < $minDiff) {
                $minDiff = $diff;
            }
        }

        if ($minDiff === 0) {
            return 'today';
        }
        if ($minDiff === 1) {
            return 'tomorrow';
        }

        return 'after ' . $minDiff . ' days';
    }

    public function details($id)
    {
        try {
            $vendor = Vendor::query()
                ->where('is_deleted', 0)
                ->where('id', $id)
                ->firstOrFail();

            return response([
                'data' => $vendor,
            ]);
        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage()], 422);
        }
    }
    public function detailByCode($code)
    {
        try {
            $vendor = Vendor::query()
                ->where('is_deleted', 0)
                ->where('vendor_code', $code)
                ->with(['offerList' => function ($query) {
                    $query->with(['coupon', 'vendor:id,vendor_code,name,profile_pic']);
                }, 'services'])
                ->firstOrFail();

                $defaultTemplate = $vendor->templates()->where('is_default', 1)->first();


            $offers = $vendor->offerList->map(static function ($package) {
                return [
                    'id' => $package->id,
                    'vendor' => [
                        'logo' => $package->vendor->logo ?? null,
                        'code' => $package->vendor->vendor_code ?? null,
                        'name' => $package->vendor->name ?? null,
                    ],
                    'badge' => $package->coupon->name ?? null,
                    'title' => $package->coupon->description ?? null,
                    'subtitle' => $package->package_name ?? $package->name,
                    'image' => $package->thumbnail_url ?? null,
                ];
            })->values();

            $wordPool = [
                'Trusted', 'Premium', 'Local', 'Certified', 'Friendly',
                'Quality', 'Reliable', 'Fast', 'Expert', 'Affordable',
                'Secure', 'Professional', 'Express', 'Complete', 'Smart',
            ];

            $serviceList = $vendor->services->map(static function ($service) use ($wordPool) {
                $meta = $service->meta ?? [];
                if (!is_array($meta)) {
                    $decoded = json_decode((string) $meta, true);
                    $meta = is_array($decoded) ? $decoded : [];
                }

                $subtitle = implode(' ', Arr::random($wordPool, min(3, count($wordPool))));

                return [
                    'id' => $service->id,
                    'name' => $service->service_name ?? $service->name ?? null,
                    'icon' => $meta['icon_md'] ?? $meta['icon'] ?? null,
                    'sub_title' => $subtitle,
                ];
            })->values();

            $vendor->makeHidden(['offerList', 'services']);

            $vendor["faq_list"] = collect();
            $vendor["blog_list"] = collect();
            if($defaultTemplate){
                $faqcomponent = VendorMenuComponent::where([
                    "vendor_template_id" => $defaultTemplate->id,
                    "is_faq" => 1
                ])->get();
    
                if($faqcomponent->isEmpty()){
                    $faqPosts = collect();
                }else{
                    $faqcomponent->load('posts:id,component_id,title,sub_title');
                    $faqPosts = $faqcomponent->flatMap->posts->map(function ($post) {
                        return [
                            'id' => $post->id,
                            'title' => $post->title,
                            'sub_title' => $post->sub_title,
                        ];
                    })->values();
                }
                $vendor["faq_list"] = $faqPosts;
    
                $blogComponent = VendorMenuComponent::where([
                    "vendor_template_id" => $defaultTemplate->id,
                    "is_blog" => 1
                ])->get();
    
                if($blogComponent->isEmpty()){
                    $blogPosts = collect();
                }else{
                    $blogComponent->load('posts:id,component_id,title,sub_title,post_image_link');
                    $blogPosts = $blogComponent->flatMap->posts->map(function ($post) {
                        return [
                            'id' => $post->id,
                            'title' => $post->title,
                            'sub_title' => $post->sub_title,
                            'image' => $post->thumbnail_url,
                        ];
                    })->values();
                }
                $vendor["blog_list"] = $blogPosts;
            }


            $vendor["offers"] = $offers;
            $vendor["service_list"] = $serviceList;
            // $vendor["default_template"] = $defaultTemplate;
            return response([
                'data' => $vendor,
            ]);
        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage(),"line" => $th->getLine()], 422);
        }
    }

    public function services($id)
    {
        try {
            $vendor = Vendor::query()
                ->where('is_deleted', 0)
                ->where('id', $id)
                ->firstOrFail();

            $services = ServiceCategory::query()
                ->where('vendor_id', $vendor->id)
                ->where('is_deleted', 0)
                ->with(['services' => function ($query) {
                    $query->where('is_deleted', 0);
                }])
                ->get();

            return response([
                'data' => $services,
            ]);
        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage()], 422);
        }
    }
    public function serviceHierarchy(Request $request, $vendorCode)
    {
        $vendor = Vendor::where([
            "vendor_code" => $vendorCode,
            "is_deleted" => 0,
        ])->first();

        $defaultTemplate = VendorTemplate::where(['is_default' => 1, 'vendor_id' => $vendor->id])->first();
        
        if (!$defaultTemplate) {
            return response([
                "error" => "No default template"
            ], 422);
        }
        $categories = ServiceCategory::query()
            ->select('id', 'service_category_name as name', 'description')
            ->where('template_id', $defaultTemplate->id)


            // ✅ only categories that have at least 1 matching service
            ->whereHas('services', function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)
                    ->whereNull('parent_id')
                    ->where('is_deleted', 0)
                    ->whereNotNull('service_name');
            })

            ->with([
                'services' => function ($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id)
                        ->whereNull('parent_id')
                        ->where('is_deleted', 0)
                        ->whereNotNull('service_name')
                        ->with('children');
                }
            ])
            ->distinct('service_category_name')
            ->get();

        $data = $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'short_info' => $cat->name,
                'form_question' => $cat->name." options",
                'services' => $cat->services->map(function ($srv) {
                    return $this->serviceNode($srv);
                })->values(),
            ];
        })->values();

        return response()->json(['data' => $data], 200);
    }
   

    private function serviceNode($service)
    {
        $serviceName = $service->service_name ?? $service->name;

        $node = [
            'id' => $service->id,
            'name' => $serviceName,
            'short_info' => $serviceName,
            'form_question' => $service->question_text ?? ($serviceName . " options"),
            'child_items' => [],
        ];

        if ($service->children && $service->children->count() > 0) {
            $node['child_items'] = $service->children->map(function ($child) {
                return $this->serviceNode($child);
            })->values();
        }

        return $node;
    }

    public function availableDates(Request $request, $vendorCode)
    {
        $vendor = Vendor::where('vendor_code', $vendorCode)->first();
        if (!$vendor) {
            return response()->json(["error" => "Vendor not found", 'data' => $vendor], 404);
        }


        $schedule = VendorAvailability::where(['vendor_id' => $vendor->id, 'is_deleted' => 0])->latest()->first();
        // return response(["data"=>$schedule],200);

        if (!$schedule) {
            return response()->json(["error" => "Schedule not found", 'data' => $schedule], 404);
        }

        $startDate = Carbon::parse($schedule->start_date)->startOfDay();
        $endDate = Carbon::parse($schedule->end_date)->endOfDay();
        $workingDays = explode(',', $schedule->working_days); // e.g. ['Mon','Tue']
        $startTime = $schedule->start_time;
        $endTime = $schedule->end_time;
        $lapseTime = (int) $schedule->lapse_time;
        $slotDuration = (int) $schedule->time_slot_duration;

        $availableSlots = [];
        $today = Carbon::today(); // current date without time

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip past dates
            if ($date->lte($today)) {
                continue;
            }

            if (in_array($date->format('D'), $workingDays)) {
                $slots = $this->generateTimeSlotsForDate($date, $startTime, $endTime, $slotDuration, $lapseTime);

                $formattedSlots = array_map(function ($slot) {
                    return $slot['start']->format('H:i');
                }, $slots);

                if (!empty($formattedSlots)) {
                    $availableSlots[] = [
                        'date' => $date->format('Y-m-d'),
                        'slots' => $formattedSlots,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $availableSlots,
        ]);
    }
    // public function availableDatesOld(Request $request, $vendorCode)
    // {
    //     $vendor = Vendor::where('vendor_code', $vendorCode)->first();
    //     if (!$vendor) {
    //         return response()->json(["error" => "Vendor not found", 'data' => $vendor], 404);
    //     }


    //     $schedule = VendorAvailability::where(['vendor_id' => $vendor->id, 'is_deleted' => 0])->latest()->first();
    //     // return response(["data"=>$schedule],200);

    //     if (!$schedule) {
    //         return response()->json(["error" => "Schedule not found", 'data' => $schedule], 404);
    //     }

    //     $startDate = Carbon::parse($schedule->start_date)->startOfDay();
    //     $endDate = Carbon::parse($schedule->end_date)->endOfDay();
    //     $workingDays = explode(',', $schedule->working_days); // e.g. ['Mon','Tue']
    //     $startTime = $schedule->start_time;
    //     $endTime = $schedule->end_time;
    //     $lapseTime = (int) $schedule->lapse_time;
    //     $slotDuration = (int) $schedule->time_slot_duration;

    //     $availableSlots = [];


    //     $today = Carbon::today(); // current date without time

    //     for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
    //         // Skip past dates
    //         if ($date->lt($today)) {
    //             continue;
    //         }

    //         if (in_array($date->format('D'), $workingDays)) {
    //             $slots = $this->generateTimeSlotsForDate($date, $startTime, $endTime, $slotDuration, $lapseTime);

    //             $formattedSlots = array_map(function ($slot) {
    //                 return [
    //                     'start_time' => $slot['start']->format('H:i:s'),
    //                     'start_time_format' => $slot['start']->format('h:i A'),
    //                     'end_time' => $slot['end']->format('H:i:s'),
    //                     'end_time_format' => $slot['end']->format('h:i A'),
    //                 ];
    //             }, $slots);

    //             $availableSlots[] = [
    //                 'key' => $date->format('Y-m-d'),
    //                 'args' => [$startTime, $endTime, $slotDuration, $lapseTime],
    //                 'dot' => true,
    //                 'bar' => false,
    //                 'highlight' => [
    //                     'color' => 'green',
    //                     'fillMode' => 'light',
    //                 ],
    //                 'dates' => $date->format('Y-m-d'),
    //                 'customData' => [
    //                     'timeSlots' => $formattedSlots,
    //                 ]
    //             ];
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $availableSlots,
    //     ]);
    // }

    private function generateTimeSlotsForDate($date, $startTime, $endTime, $slotDuration, $lapseTime)
    {
        $start = Carbon::parse($date->format('Y-m-d') . ' ' . $startTime);
        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $endTime);

        $slots = [];

        while ($start->copy()->addMinutes($slotDuration)->lte($end)) {
            $slotStart = $start->copy();
            $slotEnd = $slotStart->copy()->addMinutes($slotDuration);

            $slots[] = [
                'start' => $slotStart,
                'end' => $slotEnd,
            ];

            $start = $slotEnd->copy()->addMinutes($lapseTime);
        }

        return $slots;
    }

    public function offerList(Request $request, $vendorCode)
    {
        $vendor = Vendor::where('vendor_code', $vendorCode)->where('is_deleted', 0)->first();
        if (!$vendor) {
            return response()->json(["error" => "Vendor not found"], 404);
        }

        $packages = $vendor->offerList()->with('plans')->get();

        $data = $packages->map(function ($package) {
            return [
                'id' => $package->id,
                'name' => $package->package_name ?? $package->name,
                'description' => strip_tags($package->description),
                'plans' => $package->plans->map(function ($plan) {
                    $billingUnit = $this->formatBillingUnit($plan->duration, $plan->type_name);

                    $payload = [
                        'id' => $plan->id,
                        'name' => $plan->type_name,
                        'description' => strip_tags($plan->description),
                        'price' => $plan->amount,
                        'billingUnit' => $billingUnit,
                        'features' => [],
                    ];

                    if (!empty($plan->badge)) {
                        $payload['badge'] = $plan->badge;
                    }

                    return $payload;
                })->values(),
            ];
        })->values();

        return response()->json([
            'data' => $data,
        ], 200);
    }
    public function scheduleList(Request $request, $vendorCode)
    {
        $vendor = Vendor::where('vendor_code', $vendorCode)->where('is_deleted', 0)->first();
        if (!$vendor) {
            return response()->json(["error" => "Vendor not found"], 404);
        }

        $packages = $vendor->scheduleList()->with('plans')->get();

        $data = $packages->map(function ($package) {
            return [
                'id' => $package->id,
                'name' => $package->package_name ?? $package->name,
                'description' => strip_tags($package->description),
                'plans' => $package->plans->map(function ($plan) {
                    $billingUnit = $this->formatBillingUnit($plan->duration, $plan->type_name);

                    $payload = [
                        'id' => $plan->id,
                        'name' => $plan->type_name,
                        'description' => strip_tags($plan->description),
                        'price' => $plan->amount,
                        'billingUnit' => $billingUnit,
                        'features' => [],
                    ];

                    if (!empty($plan->badge)) {
                        $payload['badge'] = $plan->badge;
                    }

                    return $payload;
                })->values(),
            ];
        })->values();

        return response()->json([
            'data' => $data,
        ], 200);
    }

    private function formatBillingUnit($duration, $typeName)
    {
        $duration = (int) $duration;
        $typeName = trim((string) $typeName);

        if ($typeName === '') {
            return $duration > 0 ? (string) $duration : null;
        }

        $unit = strtolower($typeName);
        if ($duration > 1 && substr($unit, -1) !== 's') {
            $unit .= 's';
        }

        return trim(($duration > 1 ? $duration . ' ' : '') . $unit);
    }
}
