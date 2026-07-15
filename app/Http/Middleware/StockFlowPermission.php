<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StockFlowPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::guard('stockflow')->user();
        $companyId = $request->session()->get('stockflow_company_id');

        if (!$user || !$companyId) {
            abort(403, 'No active company selected');
        }

        $userService = app(\App\Domain\StockFlow\Services\CompanyUserService::class);
        
        if (!$userService->userHasPermission($companyId, $user->id, $permission)) {
            abort(403, "You don't have permission to perform this action: {$permission}");
        }

        return $next($request);
    }
}