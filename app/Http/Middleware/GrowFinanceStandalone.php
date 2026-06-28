<?php

namespace App\Http\Middleware;

use App\Domain\Module\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class GrowFinanceStandalone
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Handle an incoming request.
     * 
     * Sets the root view to the GrowFinance standalone blade template
     * for PWA standalone mode support.
     * Also shares subscription data with all GrowFinance pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use the GrowFinance standalone blade template
        Inertia::setRootView('growfinance');

        // Share subscription data with all GrowFinance pages
        if ($user = $request->user()) {
            $tier = $this->subscriptionService->getUserTier($user, 'growfinance');
            $limits = $this->subscriptionService->getUserLimits($user, 'growfinance');

            Inertia::share([
                'subscription_tier' => $tier,
                'subscription_limits' => $limits,
            ]);
        }

        return $next($request);
    }
}
