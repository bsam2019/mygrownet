<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StockFlowCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->attributes->has('stock_audit_company_id')) {
            abort(404);
        }

        return $next($request);
    }
}
