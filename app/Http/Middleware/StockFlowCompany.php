<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StockFlowCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->attributes->has('stockflow_company_id')) {
            abort(404);
        }

        $companyId = $request->attributes->get('stockflow_company_id');
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
