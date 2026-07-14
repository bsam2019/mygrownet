<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        if (!$companyId) {
            return $next($request);
        }

        try {
            $company = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::find($companyId);
            $settings = $company?->settings ?? [];
            $features = $settings['features_enabled'] ?? [];

            if (($features[$feature] ?? true) === false) {
                abort(403, "This feature ({$feature}) is disabled for your company.");
            }
        } catch (\Exception $e) {
            // Fail open if settings can't be read
        }

        return $next($request);
    }
}
