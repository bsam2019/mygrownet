<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStockFlowCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->attributes->has('stock_audit_company_id') && $request->hasSession()) {
            $request->session()->put(
                'stock_audit_company_id',
                $request->attributes->get('stock_audit_company_id')
            );
        }

        return $next($request);
    }
}
