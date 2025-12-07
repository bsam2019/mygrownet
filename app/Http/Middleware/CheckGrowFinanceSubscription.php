<?php

namespace App\Http\Middleware;

use App\Domain\Module\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckGrowFinanceSubscription
{
    private const MODULE_ID = 'growfinance';

    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $feature = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // If a specific feature is required, check access
        if ($feature) {
            if (!$this->subscriptionService->canPerformAction($user, $feature, self::MODULE_ID)) {
                $requiredTier = $this->subscriptionService->getRequiredTierForFeature($feature, self::MODULE_ID);
                
                if ($request->wantsJson()) {
                    return response()->json([
                        'error' => 'upgrade_required',
                        'message' => "This feature requires the {$requiredTier} plan or higher.",
                        'feature' => $feature,
                        'required_tier' => $requiredTier,
                        'current_tier' => $this->subscriptionService->getUserTier($user, self::MODULE_ID),
                    ], 403);
                }

                return redirect()->route('growfinance.upgrade')
                    ->with('warning', "This feature requires the {$requiredTier} plan or higher.");
            }
        }

        // Add subscription info to the request for use in controllers
        $request->merge([
            'subscription_tier' => $this->subscriptionService->getUserTier($user, self::MODULE_ID),
            'subscription_limits' => $this->subscriptionService->getUserLimits($user, self::MODULE_ID),
        ]);

        return $next($request);
    }
}
