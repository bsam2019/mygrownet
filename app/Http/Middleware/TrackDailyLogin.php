<?php

namespace App\Http\Middleware;

use App\Services\PointService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackDailyLogin
{
    public function __construct(
        protected PointService $pointService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Award daily login points
            $transaction = $this->pointService->awardDailyLogin($user);
            
            // Check for streak bonuses if points were awarded
            if ($transaction) {
                $this->pointService->checkStreakBonuses($user);
            }
        }

        return $next($request);
    }
}
