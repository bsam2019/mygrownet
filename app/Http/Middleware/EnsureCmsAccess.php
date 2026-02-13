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

        // Check if company is active
        if (!$user->cmsUser->company->isActive()) {
            abort(403, 'Your company account is suspended. Please contact support.');
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
