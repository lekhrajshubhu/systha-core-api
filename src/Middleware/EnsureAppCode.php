<?php

namespace Systha\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Systha\Core\Models\Company;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppCode
{
    public function handle(Request $request, Closure $next): Response
    {
        $appCode = $this->extractHeader($request);

        if (! $appCode) {
            return response()->json(['message' => 'Invalid Request'], 403);
        }

        $company = $this->findCompany($appCode);

        if (! $company) {
            return response()->json(['message' => 'Invalid Request'], 403);
        }

        // Expose for downstream consumers
        $request->merge([
            'company_id' => $company->id,
            'company_code' => $company->code,
        ]);
        $request->attributes->set('company', $company);
        $request->attributes->set('company_id', $company->id);
        $request->attributes->set('company_code', $company->code);

        return $next($request);
    }

    private function extractHeader(Request $request): ?string
    {
        // Headers are case-insensitive; try common variants explicitly.
        return $request->headers->get('App-Code');

    }

    private function findCompany(string $code): ?Company
    {
        $query = Company::query()->where('code', $code);

        if (Schema::hasColumn((new Company())->getTable(), 'is_active')) {
            $query->where('is_active', 1);
        }

        if (Schema::hasColumn((new Company())->getTable(), 'is_deleted')) {
            $query->where('is_deleted', 0);
        }

        return $query->first();
    }
}
