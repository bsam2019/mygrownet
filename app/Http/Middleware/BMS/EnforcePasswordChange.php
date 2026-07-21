<?php

namespace App\Http\Middleware\BMS;

use App\Infrastructure\Persistence\Eloquent\BMS\CmsUserModel;
use App\Services\BMS\SecurityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordChange
{
    public function __construct(
        private SecurityService $securityService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Skip check for password change routes
        if ($request->routeIs('bms.password.*') || $request->routeIs('bms.logout')) {
            return $next($request);
        }

        $cmsUser = CmsUserModel::where('user_id', $user->id)->first();

        if (!$cmsUser) {
            return $next($request);
        }

        // Check if password change is forced
        if ($user->force_password_change) {
            return redirect()->route('bms.password.change')
                ->with('warning', 'You must change your password before continuing.');
        }

        // Check if password has expired
        if ($this->securityService->isPasswordExpired($user, $cmsUser->company_id)) {
            $user->update(['force_password_change' => true]);
            
            return redirect()->route('bms.password.change')
                ->with('warning', 'Your password has expired. Please change it to continue.');
        }

        return $next($request);
    }
}
