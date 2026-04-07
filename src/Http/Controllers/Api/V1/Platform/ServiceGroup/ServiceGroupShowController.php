<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\ServiceGroup;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Systha\Core\Models\Package;
use Systha\Core\Models\Service;
use Systha\Core\Models\ServiceCategory;
use Systha\Core\Models\ServiceGroupModel;
use Systha\Core\Models\SurveyModel;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\VendorModel;
use Systha\Core\Support\Geo;

/**
 * @group Platform
 * @subgroup Static Content
 */
class ServiceGroupShowController extends Controller
{
    private const REF_LAT = 28.5420;
    private const REF_LNG = -81.3790;

    public function groupList()
    {
        try {
            $data = ServiceGroupModel::where('is_active', 1)->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'label' => $item->name,
                    'subtitle' => $item->sub_title,
                    'slug' => $item->slug,
                    'meta' => [
                        'color' => $item->meta['color'] ?? null,
                        'icon' => $item->meta['icon_md'] ?? null,
                    ],
                    'routeName' => $item->meta['mobile_route'] ?? null,

                ];
            });
            return response(['data' => $data], 200);
        } catch (ValidationException $th) {
            throw $th;
        } catch (\Throwable $th) {
            return response(['error' => 'Unable to fetch static content.'], 422);
        }
    }

    public function detail(Request $request, $slug)
    {
        try {
            $tenantPage = (int) $request->query('tenant_page', 1);
            $tenantPerPage = (int) $request->query('tenant_per_page', 6);
            $itemPage = (int) $request->query('item_page', 1);
            $itemPerPage = (int) $request->query('item_per_page', 6);
            $offerPage = (int) $request->query('offer_page', 1);
            $offerPerPage = (int) $request->query('offer_per_page', 6);

            $offerPage = $offerPage < 1 ? 1 : $offerPage;
            $offerPerPage = $offerPerPage < 1 ? 6 : $offerPerPage;
            if ($offerPerPage > 100) {
                $offerPerPage = 100;
            }

            $serviceGroup = ServiceGroupModel::where('slug', $slug)->first();


            if (!$serviceGroup) {
                return response(['error' => 'Service group not found.'], 404);
            }
            $serviceGroup->load('bannerUsage.attachment');


            $vendorIds = Vendor::where('type', $slug)->pluck('id')->toArray();

            $serviceCategoriesQuery = ServiceCategory::query()
                ->whereIn('vendor_id', $vendorIds)
                ->where('is_deleted', 0)
                ->orderBy('service_category_name');

            $totalCategories = (clone $serviceCategoriesQuery)->count();
            $limit = $totalCategories < 6 ? 3 : 6;

            $serviceCategories = $serviceCategoriesQuery
                ->take($limit)
                ->get()
                ->map(function ($category) {
                    $servicesQuery = Service::query()
                        ->where('service_category_id', $category->id)
                        ->where('is_deleted', 0)
                        ->orderBy('service_name');

                    $totalServices = (clone $servicesQuery)->count();

                    $serviceItems = $servicesQuery
                        ->take(6)
                        ->get()
                        ->map(function ($service) {
                            $meta = $service->meta;
                            if (!is_array($meta)) {
                                $decoded = json_decode((string) $meta, true);
                                $meta = is_array($decoded) ? $decoded : [];
                            }

                            return [
                                'id' => $service->id,
                                'name' => $service->service_name,
                                'slug' => $service->slug ?? null,
                                'meta' => [
                                    'icon' => $meta['icon_md'] ?? null,
                                    'color' => $meta['color'] ?? null,
                                ],
                            ];
                        })
                        ->values()
                        ->all();

                    return [
                        'id' => $category->id,
                        'name' => $category->service_category_name,
                        'slug' => $category->slug,
                        'meta' => [
                            'icon' => $category->meta['icon_md'] ?? null,
                            'color' => $category->meta['color'] ?? null,
                        ],
                        'service_items' => [
                            'total' => $totalServices,
                            'per_page' => 6,
                            'items' => $serviceItems,
                        ],
                    ];
                })
                ->values()
                ->all();

            $data = [
                'id' => $serviceGroup->id,
                'name' => $serviceGroup->name,
                'slug' => $serviceGroup->slug,
                'meta' => $serviceGroup->meta,
                'banner' => $this->getBannerPayload($serviceGroup),
                'tenants' => $this->getTenantsPayload($serviceGroup->slug, $tenantPage, $tenantPerPage),
                'offers' => $this->getOffersPayload($serviceGroup->slug, $offerPage, $offerPerPage),
            ];
            return response(['data' => $data], 200);
        } catch (ValidationException $th) {
            throw $th;
        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage()], 422);
        }
    }

    private function getBannerPayload(ServiceGroupModel $serviceGroup): array
    {
        $banner = $serviceGroup->bannerUsage;

        if ($banner && $banner->attachment) {
            $meta = $banner->meta ?? [];

            return [
                'title' => $meta['title'] ?? '',
                'sub_title' => $meta['sub_title'] ?? '',
                'highlight' => $meta['highlight'] ?? '',
                'image_url' => $banner->attachment->url ?? '', // Use the url accessor from AttachmentModel
            ];
        }

        // Default fallback
        return [
            'title' => 'no',
            'description' => 'no',
            'image_url' => 'https://systha.com/assets/images/hero/hero-1.png',
        ];
    }

    private function getTenantsPayload(string $slug, int $tenantPage, int $tenantPerPage): array
    {
        $tenantPaginator = Vendor::select(
            array_values(array_filter([
                'id',
                'name',
                'vendor_code',
                'profile_pic',
                Schema::hasColumn('vendors', 'is_verified') ? 'is_verified' : null,
                Schema::hasColumn('vendors', 'ratings') ? 'ratings' : null,
                Schema::hasColumn('vendors', 'rating_star') ? 'rating_star' : null,
                Schema::hasColumn('vendors', 'total_reviews_count') ? 'total_reviews_count' : null,
            ]))
        )
            ->where('is_active', 1)
            ->with([
                'address' => function ($query): void {
                    $query->select('id', 'table_id', 'table_name', 'add1', 'city', 'state', 'lat', 'lon as lng');
                },
            ])
            ->where('type', $slug)
            ->distinct()
            ->paginate($tenantPerPage, ['*'], 'tenant_page', $tenantPage);

        $items = $tenantPaginator->getCollection()->map(static function (Vendor $tenant): array {
            $address = $tenant->address;
            $lat = $address?->lat;
            $lng = $address?->lng;
            $addressParts = array_filter([
                $address?->add1,
                $address?->city,
                $address?->state,
            ], static function ($part): bool {
                return $part !== null && trim((string) $part) !== '';
            });
            $addressText = count($addressParts) ? implode(', ', $addressParts) : null;

            $rawDistance = Geo::distanceMilesRawFromTo(
                ['lat' => self::REF_LAT, 'lng' => self::REF_LNG],
                ['lat' => $lat, 'lng' => $lng]
            );

            $isVerified = Schema::hasColumn('vendors', 'is_verified') ? (bool) $tenant->is_verified : rand(0, 1) === 1;
            $rating = Schema::hasColumn('vendors', 'rating_star') ? $tenant->rating_star : (Schema::hasColumn('vendors', 'ratings') ? $tenant->ratings : rand(1, 5) / 2);
            $totalReviews = Schema::hasColumn('vendors', 'total_reviews_count') ? $tenant->total_reviews_count : rand(10, 200);

            return [
                'name' => $tenant->name,
                'is_verified' => $isVerified,
                'review' => [
                    'rating' => $rating,
                    'total_reviews_count' => $totalReviews,
                ],
                'logo' => $tenant->logo ?? null,
                'distance' => $rawDistance === null ? null : Geo::formatMiles($rawDistance),
                'code' => $tenant->vendor_code,
                'address' => $addressText,
                '_distance_raw' => $rawDistance,
            ];
        })
            ->sortBy(static function (array $tenant): float {
                $distance = $tenant['_distance_raw'] ?? null;
                return $distance === null ? PHP_FLOAT_MAX : (float) $distance;
            })
            ->values()
            ->map(static function (array $tenant): array {
                unset($tenant['_distance_raw']);
                return $tenant;
            });

        return [
            'total' => $tenantPaginator->total(),
            'per_page' => $tenantPaginator->perPage(),
            'items' => $items,
        ];
    }

    private function getOffersPayload(string $slug, int $offerPage, int $offerPerPage): array
    {
        $vendorIds = Vendor::where('type', $slug)
            ->where('is_active', 1)
            ->pluck('id')
            ->all();

        if (empty($vendorIds)) {
            return [
                'total' => 0,
                'per_page' => $offerPerPage,
                'items' => [],
            ];
        }

        $packagesQuery = Package::query()
            ->whereIn('vendor_id', $vendorIds)
            ->with(['vendor:id,vendor_code,name', 'coupon'])
            ->whereNotNull('coupon_id')
            ->orderByDesc('id');

        if (Schema::hasColumn('packages', 'is_deleted')) {
            $packagesQuery->where('is_deleted', 0);
        }

        $packagesPaginator = $packagesQuery->paginate($offerPerPage, ['*'], 'offer_page', $offerPage);

        $items = $packagesPaginator->getCollection()->map(static function (Package $package): array {
            return [
                'id' => $package->id,

                'vendor' => [
                    'logo' => $package->vendor->logo ?? null,
                    'code' => $package->vendor->vendor_code ?? null,
                    'name' => $package->vendor->name ?? null,
                ],

                'badge' => $package->coupon->name,
                'title' =>  $package->coupon->description ?? null,
                'subtitle' => $package->package_name ?? $package->name,
                'image' => $package->thumbnail_url ?? null,
            ];
        })->values();

        return [
            'total' => $packagesPaginator->total(),
            'per_page' => $packagesPaginator->perPage(),
            'items' => $items,
        ];
    }





    public function questions(Request $request, $slug)
    {
        try {
            $request_mode = $request->query('request_mode');
            $vendor_code = $request->query('vendor_code');

            if (!$vendor_code) {
                return response(['error' => 'vendor_code is required.'], 422);
            }
            if (!$request_mode) {
                return response(['error' => 'request_mode is required.'], 422);
            }

            $vendor = VendorModel::where('vendor_code', $vendor_code)->first();
            if (!$vendor) {
                return response(['error' => 'Vendor not found for the specified vendor code.'], 404);
            }

            $survey = SurveyModel::where([
                'service_group' => $slug,
                'survey_type' => $request_mode,
                'vendor_id' => $vendor->id
            ])->with('questions.options')->first();

            if (!$survey) {
                return response(['error' => 'Survey not found for the specified criteria.'], 404);
            }

            $serviceOptions = Service::query()
                ->where('vendor_id', $vendor->id)
                ->where('is_deleted', 0)
                ->orderBy('service_name')
                ->get(['service_name', 'slug'])
                ->map(function ($service) {
                    return [
                        'label' => $service->service_name,
                        'value' => $service->slug,
                    ];
                })
                ->values();

            $questions = $survey->questions->map(function ($q) use ($serviceOptions) {
                $options = $q->options->map(function ($opt) {
                    return [
                        'label' => $opt->label,
                        'value' => $opt->value,
                    ];
                })->values();

                if ($q->question_code === 'service_items' || $q->question_code === 'schedule_items') {
                    $options = $serviceOptions;
                }

                return [
                    'question' => $q->question,
                    'code' => $q->question_code,
                    'field_type' => $q->question_type,
                    'seq_no' => $q->seq_no,
                    'options' => $options,
                ];
            })->values();

            return response(['data' => $questions], 200);
        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage()], 422);
        }
    }
}
