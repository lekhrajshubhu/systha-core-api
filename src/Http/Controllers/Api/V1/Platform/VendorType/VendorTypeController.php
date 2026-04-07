<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\VendorType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Systha\Core\Http\Resources\VendorTypeResource;
use Systha\Core\Models\Lookup;


/**
 * @group Platform
<<<<<<< HEAD
 * @subgroup Vendors
=======
 * @subgroup Inquiries
>>>>>>> 18539635f4a2a7c24ea1f527231dffef47b3d97a
 */
class VendorTypeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->query('query', ''));

            $vendorTypes = Lookup::where('code', 'vendor_type')
                ->where('is_deleted', 0)
                ->get()
                ->filter(function (Lookup $vendorType) {
                    $features = is_array($vendorType->features) ? $vendorType->features : [];

                    if (array_is_list($features) && isset($features[0]) && is_array($features[0])) {
                        $features = $features[0];
                    }

                    return is_array($features) && array_key_exists('mobile_route_name', $features);
                })
                ->map(function (Lookup $vendorType) {
                    $features = is_array($vendorType->features) ? $vendorType->features : [];

                    if (array_is_list($features) && isset($features[0]) && is_array($features[0])) {
                        $features = $features[0];
                    }

                    $vendorType->setAttribute('value', data_get($features, 'label', data_get($features, 'value', $vendorType->value)));
                    $vendorType->setAttribute('description', data_get($features, 'subtitle', data_get($features, 'description', $vendorType->description)));
                    $vendorType->setAttribute('icon_md', data_get($features, 'icon', data_get($features, 'icon_md', $vendorType->icon_md)));
                    $vendorType->setAttribute('mobile_route_name', data_get($features, 'routeName', data_get($features, 'mobile_route_name', $vendorType->mobile_route_name)));
                    $vendorType->setAttribute('color', data_get($features, 'color', data_get($features, 'mobile_route_name', $vendorType->mobile_route_name)));

                    return $vendorType;
                });

            if ($search !== '') {
                $needle = mb_strtolower($search);

                $vendorTypes = $vendorTypes->filter(function (Lookup $vendorType) use ($needle) {
                    $value = mb_strtolower((string) $vendorType->value);
                    $description = mb_strtolower((string) $vendorType->description);
                    $mobileRouteName = mb_strtolower((string) $vendorType->mobile_route_name);

                    return str_contains($value, $needle)
                        || str_contains($description, $needle)
                        || str_contains($mobileRouteName, $needle);
                })->values();
            }

            return response([
                'data' => VendorTypeResource::collection($vendorTypes)->resolve($request),
            ]);
        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage()], 422);
        }
    }
}
