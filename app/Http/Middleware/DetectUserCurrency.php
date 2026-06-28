<?php

namespace App\Http\Middleware;

use App\Domain\Payment\Services\CurrencyDetectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DetectUserCurrency
{
    public function __construct(
        private CurrencyDetectionService $currencyDetection
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if ($user = $request->user()) {
            $currency = $user->user_currency ?? $user->preferred_currency;
            if (!$currency) {
                $currency = $this->currencyDetection->detectCurrency(
                    ipAddress: $request->ip(),
                    userId: $user->id
                );
                if ($currency && $currency !== ($user->user_currency ?? null)) {
                    $user->user_currency = $currency;
                    $user->preferred_currency = $currency;
                    $user->saveQuietly();
                }
            }
            session(['user_currency' => $currency, 'currency' => $currency]);
        } else {
            if (!session()->has('user_currency')) {
                $currency = $this->currencyDetection->detectCurrency(
                    ipAddress: $request->ip()
                );
                session(['user_currency' => $currency, 'currency' => $currency]);
            }
        }

        return $next($request);
    }
}
