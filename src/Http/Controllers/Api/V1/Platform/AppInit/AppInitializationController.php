<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\AppInit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Systha\Core\Models\Company;
use Systha\Core\Models\ServiceGroupModel;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\VendorMembership;
use Systha\Core\Support\Geo;

/**
 * @group Platform
 * @subgroup App Init
 */
class AppInitializationController extends Controller
{
    private const REF_LAT = 28.5420;
    private const REF_LNG = -81.3790;

    public function appInit(Request $request)
    {

        $company = $request->attributes->get('company');


        if (! $company instanceof Company) {
            $headerCode = $this->extractHeader($request);
            if (! $headerCode) {
                return response()->json(['message' => 'AppCode header is required.'], 403);
            }

            $company = Company::where('code', $headerCode)->first();

            if (! $company) {
                return response()->json(['message' => 'Invalid AppCode.'], 403);
            }
        }

        $logo = $company->primaryLogo->attachment->url ?? null;

        $companyPayload = [
            'name' => $company->c_name ?? null,
            'logoUrl' => $logo,
        ];

        $vendorIds = VendorMembership::query()
            ->where('company_id', $company->id)
            ->when(
                Schema::hasColumn('vendor_memberships', 'is_deleted'),
                fn ($q) => $q->where('is_deleted', 0)
            )
            ->pluck('vendor_id');

        $serviceGroupSlugs = Vendor::query()
            ->whereIn('id', $vendorIds)
            ->when(Schema::hasColumn('vendors', 'is_deleted'), fn ($q) => $q->where('is_deleted', 0))
            ->when(Schema::hasColumn('vendors', 'is_active'), fn ($q) => $q->where('is_active', 1))
            ->whereNotNull('type')
            ->pluck('type')
            ->unique()
            ->values();

        $serviceGroups = ServiceGroupModel::query()
            ->whereIn('slug', $serviceGroupSlugs)
            ->when(Schema::hasColumn('service_groups', 'is_active'), fn ($q) => $q->where('is_active', 1))
            ->get()
            ->map(function (ServiceGroupModel $group) {
                $meta = $group->meta;
                if (! is_array($meta)) {
                    $decoded = json_decode((string) $meta, true);
                    $meta = is_array($decoded) ? $decoded : [];
                }

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'label' => $group->name,
                    'subtitle' => $group->sub_title ?? null,
                    'description' => Schema::hasColumn($group->getTable(), 'description') ? $group->description : null,
                    'meta' => [
                        'icon' => $meta['icon_md'] ?? null,
                        'color' => $meta['color'] ?? null,
                    ],
                    'routeName' => $meta['mobile_route'] ?? null,
                ];
            })
            ->values();

        $vendorList = null;
        if ($serviceGroups->count() === 1) {
            $singleGroupSlug = $serviceGroupSlugs->first();

            if ($singleGroupSlug) {
                $vendorList = Vendor::query()
                    ->select(array_values(array_filter([
                        'id',
                        'name',
                        'vendor_code',
                        'profile_pic',
                        'type',
                        Schema::hasColumn('vendors', 'is_verified') ? 'is_verified' : null,
                        Schema::hasColumn('vendors', 'rating_star') ? 'rating_star' : null,
                        Schema::hasColumn('vendors', 'ratings') ? 'ratings' : null,
                        Schema::hasColumn('vendors', 'total_reviews_count') ? 'total_reviews_count' : null,
                    ])))
                    ->whereIn('id', $vendorIds)
                    ->where('type', $singleGroupSlug)
                    ->when(Schema::hasColumn('vendors', 'is_deleted'), fn ($q) => $q->where('is_deleted', 0))
                    ->when(Schema::hasColumn('vendors', 'is_active'), fn ($q) => $q->where('is_active', 1))
                    ->with(['address' => function ($query): void {
                        $query->select('id', 'table_id', 'table_name', 'add1', 'city', 'state', 'lat', 'lon as lng');
                    }])
                    ->get()
                    ->map(function (Vendor $vendor) {
                        $address = $vendor->address;
                        $addressParts = array_filter([
                            $address?->add1,
                            $address?->city,
                            $address?->state,
                        ], static function ($part): bool {
                            return $part !== null && trim((string) $part) !== '';
                        });

                        $distance = Geo::distanceMilesFromTo(
                            ['lat' => self::REF_LAT, 'lng' => self::REF_LNG],
                            ['lat' => $address?->lat, 'lng' => $address?->lng]
                        );

                        return [
                            'name' => $vendor->name,
                            'is_verified' => Schema::hasColumn('vendors', 'is_verified') ? (bool) $vendor->is_verified : false,
                            'review' => [
                                'rating' => rand(1, 5) / 2, // Placeholder for actual rating logic
                                'total_reviews_count' => rand(10, 500), // Placeholder for actual review count logic
                            ],
                            'logo' => $vendor->logo,
                            'distance' => $distance,
                            'code' => $vendor->vendor_code,
                            'address' => count($addressParts) ? implode(', ', $addressParts) : null,
                        ];
                    })
                    ->values();
            }
        }

        $response = [
            'company' => $companyPayload,
            'serviceGroups' => $serviceGroups,
        ];

        if (! is_null($vendorList)) {
            $response['vendorList'] = $vendorList;
        }

        return response()->json($response);
    }

    private function extractHeader(Request $request): ?string
    {
        return $request->headers->get('AppCode')
            ?? $request->headers->get('App-Code')
            ?? $request->headers->get('appcode')
            ?? $request->headers->get('app-code');
    }

    public function companyAbout(Request $request)
    {
        $company = $request->attributes->get('company');

        if (! $company instanceof Company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        return response()->json([
            'logoUrl' => $company->primaryLogo->attachment->url ?? null,
            'name' => $company->c_name ?? null,
            'description' => $company->company_info ?? null,
        ]);
    }
}
