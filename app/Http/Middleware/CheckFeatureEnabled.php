<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $companyId = $request->session()->get('stockflow_company_id');

        if (!$companyId) {
            return $next($request);
        }

        try {
            $company = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::find($companyId);
            $settings = $company?->settings ?? [];
            $features = $settings['features_enabled'] ?? [];

            // If explicitly disabled in company settings, block
            if (($features[$feature] ?? null) === false) {
                abort(403, "This feature ({$feature}) is disabled for your company.");
            }

            // If feature is not in settings at all, check extension assignments
            if (!isset($features[$feature])) {
                $hasExtension = \App\Models\StockAudit\CompanyExtension::where('sa_company_id', $companyId)
                    ->whereHas('extension', function ($q) use ($feature) {
                        $q->where('is_active', true);
                    })
                    ->get()
                    ->contains(function ($ce) use ($feature) {
                        $providerClass = $ce->extension->provider_class;
                        if (!class_exists($providerClass)) return false;
                        $provider = app($providerClass);
                        return in_array($feature, $provider->getFeatures());
                    });

                if (!$hasExtension) {
                    abort(403, "This feature ({$feature}) requires an extension that is not enabled for your company.");
                }
            }
        } catch (\Exception $e) {
            // Fail open if settings can't be read
        }

        return $next($request);
    }
}
