<?php

namespace App\Http\Middleware;

use App\Domain\Module\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class BizBoostStandalone
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Only activate standalone mode on bizboost subdomain
        if (str_contains($host, 'bizboost.mygrownet.com')) {
            Inertia::setRootView('bizboost');
        }

        // Share subscription data with all BizBoost pages
        if ($user = $request->user()) {
            $tier = $this->subscriptionService->getUserTier($user, 'bizboost');
            $limits = $this->subscriptionService->getUserLimits($user, 'bizboost');

            Inertia::share([
                'subscription_tier' => $tier,
                'subscription_limits' => $limits,
            ]);
        }

        return $next($request);
    }
}
