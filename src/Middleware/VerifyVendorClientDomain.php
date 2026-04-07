<?php

namespace Systha\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\VendorClient;
use Systha\Core\Models\VendorTemplate;

class VerifyVendorClientDomain
{
    public function handle(Request $request, Closure $next)
    {
        $requestHost = $this->resolveRequestHost($request);
 $requestHost = "roofing.test";
        if (!$requestHost) {
            return response()->json(['message' => 'Unable to resolve request domain.'], 403);
        }

        $mappedVendorCode = $this->resolveVendorCodeFromHost($requestHost);
        if (!$mappedVendorCode) {
            return response()->json(['message' => 'Domain is not associated with this vendor.'], 403);
        }

        $vendorCode = $this->resolveVendorCode($request);
        if (!$vendorCode) {
            return response()->json(['message' => 'Unable to resolve vendor for this request.'], 403);
        }

        if (strcasecmp($vendorCode, $mappedVendorCode) !== 0) {
            return response()->json(['message' => 'Domain and vendor do not match.'], 403);
        }

        $request->merge([
            'vendor_code' => $mappedVendorCode,
        ]);

        $request->attributes->set('vendor_code', $mappedVendorCode);

        return $next($request);
    }

    private function resolveVendorCode(Request $request): ?string
    {
        $authUser = Auth::guard('vendor_client')->user();
        if ($authUser && !empty($authUser->vendor_code)) {
            return (string) $authUser->vendor_code;
        }

        if ($authUser && $authUser->vendor_id) {
            $vendorCode = Vendor::query()->whereKey($authUser->vendor_id)->value('vendor_code');
            if ($vendorCode) {
                return (string) $vendorCode;
            }
        }

        $vendorCode = $request->input('vendor_code')
            ?: $request->input('code')
            ?: $request->query('vendor_code')
            ?: $request->query('code');

        if ($vendorCode) {
            return (string) $vendorCode;
        }

        $email = $request->input('email');
        if ($email) {
            $vendorClient = VendorClient::query()
                ->where('email', $email)
                ->select('vendor_code', 'vendor_id')
                ->first();

            if ($vendorClient && !empty($vendorClient->vendor_code)) {
                return (string) $vendorClient->vendor_code;
            }

            if ($vendorClient && $vendorClient->vendor_id) {
                $vendorCode = Vendor::query()->whereKey($vendorClient->vendor_id)->value('vendor_code');
                if ($vendorCode) {
                    return (string) $vendorCode;
                }
            }
        }

        return null;
    }

    private function resolveRequestHost(Request $request): ?string
    {
        $origin = $request->headers->get('origin');
        if ($origin) {
            $host = parse_url($origin, PHP_URL_HOST);
            if ($host) {
                return $this->normalizeHost($host);
            }
        }


        $referer = $request->headers->get('referer');
        if ($referer) {
            $host = parse_url($referer, PHP_URL_HOST);
            if ($host) {
                return $this->normalizeHost($host);
            }
        }

        $host = $request->getHost();
        return $host ? $this->normalizeHost($host) : null;
    }


    private function resolveVendorCodeFromHost(string $requestHost): ?string
    {
        $hostColumn = Schema::hasColumn((new VendorTemplate())->getTable(), 'template_host')
            ? 'template_host'
            : 'host';

        $templates = VendorTemplate::query()
            ->select('vendor_id', $hostColumn)
            ->when(
                Schema::hasColumn((new VendorTemplate())->getTable(), 'is_deleted'),
                fn ($q) => $q->where('is_deleted', 0)
            )
            ->when(
                Schema::hasColumn((new VendorTemplate())->getTable(), 'is_active'),
                fn ($q) => $q->where('is_active', 1)
            )
            ->get();

        foreach ($templates as $template) {
            $host = $this->normalizeHost($template->{$hostColumn});
            if ($host && $host === $requestHost) {
                $vendorCode = Vendor::query()->whereKey($template->vendor_id)->value('vendor_code');
                return $vendorCode ? (string) $vendorCode : null;
            }
        }

        return null;
    }

    private function normalizeHost(?string $host): ?string
    {
        if (!$host) {
            return null;
        }

        $host = trim(strtolower($host));
        if (preg_match('#^https?://#', $host)) {
            $parsedHost = parse_url($host, PHP_URL_HOST);
            if ($parsedHost) {
                $host = strtolower($parsedHost);
            }
        }

        if (strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }

        return $host ?: null;
    }
}
