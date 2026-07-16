<?php

namespace App\Http\Middleware;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaApiKeyModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StockFlowApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->bearerToken();

        if (!$apiKey) {
            return response()->json(['error' => 'Missing API key. Use Authorization: Bearer <key>'], 401);
        }

        $key = SaApiKeyModel::where('key', $apiKey)->where('active', true)->first();

        if (!$key) {
            return response()->json(['error' => 'Invalid or revoked API key.'], 401);
        }

        if ($key->expires_at && $key->expires_at->isPast()) {
            return response()->json(['error' => 'API key has expired.'], 401);
        }

        $key->update(['last_used_at' => now()]);

        $request->merge(['stockflow_company_id' => $key->sa_company_id]);
        $request->attributes->set('stockflow_company_id', $key->sa_company_id);

        return $next($request);
    }
}
