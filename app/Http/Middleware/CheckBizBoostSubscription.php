<?php

namespace App\Http\Middleware;

use App\Domain\Module\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBizBoostSubscription
{
    private const MODULE_ID = 'bizboost';

    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $feature = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // If a specific feature is required, check access
        if ($feature) {
            if (!$this->subscriptionService->hasFeature($user, $feature, self::MODULE_ID)) {
                $requiredTier = $this->subscriptionService->getRequiredTierForFeature($feature, self::MODULE_ID);
                $upgradeUrl = $this->getUpgradeUrl();

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'upgrade_required',
                        'message' => "This feature requires the {$requiredTier} plan or higher.",
                        'feature' => $feature,
                        'required_tier' => $requiredTier,
                        'current_tier' => $this->subscriptionService->getUserTier($user, self::MODULE_ID),
                        'upgrade_url' => $upgradeUrl,
                    ], 403);
                }

                return redirect($upgradeUrl)
                    ->with('error', "This feature requires the {$requiredTier} plan or higher.");
            }
        }

        // Add subscription info to the request for use in controllers
        $request->merge([
            'subscription_tier' => $this->subscriptionService->getUserTier($user, self::MODULE_ID),
            'subscription_limits' => $this->subscriptionService->getUserLimits($user, self::MODULE_ID),
        ]);

        return $next($request);
    }

    /**
     * Get the upgrade URL, with fallback if route doesn't exist
     */
    private function getUpgradeUrl(): string
    {
        try {
            return route('bizboost.upgrade');
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            return '/bizboost/upgrade';
        }
    }
}
