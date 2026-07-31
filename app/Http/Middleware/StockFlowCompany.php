<?php

namespace App\Http\Middleware;

use App\Domain\StockFlow\Services\CompanyUserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StockFlowCompany
{
    public function __construct(
        private CompanyUserService $companyUserService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->attributes->has('stockflow_company_id')) {
            abort(404);
        }

        $companyId = $request->attributes->get('stockflow_company_id');

        // Auth routes must always pass so users can log in/out regardless of membership.
        $routeName = $request->route()?->getName();
        $isAuthRoute = $routeName && in_array($routeName, [
            'stockflow.sub.login',
            'stockflow.sub.login.store',
            'stockflow.sub.logout',
        ]);

        if (!$isAuthRoute) {
            // Tenant gate: an authenticated user may only enter a company subdomain
            // if they are an active member of that company or a platform admin.
            $user = Auth::guard('web')->user();
            if ($user) {
                $isAdmin = $user->hasRole(['admin', 'Administrator', 'superadmin', 'super-admin', 'stockflow-admin']);
                if (!$isAdmin && !$this->companyUserService->isActiveMember($companyId, (int) $user->getAuthIdentifier())) {
                    abort(403, 'You do not have access to this company.');
                }
            }
        }

        $request->session()->put('stockflow_company_id', $companyId);

        // Extract account from route parameter for Ziggy default binding
        $account = $request->route('account');
        if ($account) {
            // Bind default route parameter for Ziggy
            app('url')->defaults(['account' => $account]);
        }

        return $next($request);
    }
}
