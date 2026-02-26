<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCmsAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('cms.login');
        }

        // Check if user has CMS access
        if (!$user->cmsUser) {
            abort(403, 'You do not have access to the CMS. Please contact your administrator.');
        }

        // Check if CMS user is active
        if (!$user->cmsUser->isActive()) {
            abort(403, 'Your CMS access has been suspended. Please contact your administrator.');
        }

        // Check if company has valid access (includes subscription check)
        if (!$user->cmsUser->company->hasValidAccess()) {
            $company = $user->cmsUser->company;
            
            // Provide specific message based on subscription type
            if ($company->subscription_type === 'complimentary' && $company->complimentary_until) {
                if (now()->gt($company->complimentary_until)) {
                    abort(403, 'Your complimentary access has expired. Please contact support to upgrade to a paid subscription.');
                }
            }
            
            abort(403, 'Your company account access is suspended. Please contact support.');
        }

        // Share CMS data with Inertia
        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::share([
                'cmsUser' => fn () => $user->cmsUser->load('role'),
                'company' => fn () => $user->cmsUser->company,
            ]);
        }

        return $next($request);
    }
}
