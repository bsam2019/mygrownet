<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCmsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            // Not authenticated - redirect to main login
            return redirect()->route('login');
        }

        // Resolve active CMS user via the accessor (session-aware)
        $cmsUser = $user->cmsUser;

        if (!$cmsUser) {
            // User is authenticated but has no company yet — send to hub
            return redirect()->route('cms.companies.hub');
        }

        if (!$cmsUser->isActive()) {
            abort(403, 'Your CMS access has been suspended. Please contact your administrator.');
        }

        if (!$cmsUser->company->hasValidAccess()) {
            $company = $cmsUser->company;
            if ($company->subscription_type === 'complimentary' && $company->complimentary_until) {
                if (now()->gt($company->complimentary_until)) {
                    abort(403, 'Your complimentary access has expired. Please contact support.');
                }
            }
            abort(403, 'Your company account access is suspended. Please contact support.');
        }

        // Share with Inertia
        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::share([
                'cmsUser'  => fn () => $cmsUser->load('role'),
                'company'  => fn () => $cmsUser->company,

                // All companies this user belongs to — for the switcher
                'userCompanies' => fn () => $user->cmsUsers()
                    ->where('status', 'active')
                    ->with('company:id,name,industry_type,logo_path')
                    ->get()
                    ->map(fn ($cu) => [
                        'company_id'   => $cu->company_id,
                        'company_name' => $cu->company->name,
                        'industry'     => $cu->company->industry_type,
                        'logo'         => $cu->company->logo_path,
                        'role'         => $cu->role?->name,
                        'is_active'    => $cu->company_id === $cmsUser->company_id,
                    ]),

                'expenseCategories' => fn () => \App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel::where('company_id', $cmsUser->company_id)
                    ->where('is_active', true)->orderBy('name')->get(['id', 'name']),

                'jobs' => fn () => \App\Infrastructure\Persistence\Eloquent\CMS\JobModel::where('company_id', $cmsUser->company_id)
                    ->whereIn('status', ['in_progress', 'pending'])
                    ->orderBy('job_number', 'desc')->get(['id', 'job_number', 'description']),
            ]);
        }

        return $next($request);
    }
}
