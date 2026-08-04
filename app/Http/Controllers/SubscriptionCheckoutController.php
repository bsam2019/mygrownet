<?php

namespace App\Http\Controllers;

use App\Domain\Module\Services\ModuleAccessService;
use App\Domain\Module\Services\ModuleSubscriptionService;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Domain\Module\ValueObjects\Money;
use App\Domain\Module\ValueObjects\SubscriptionTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/**
 * Unified subscription checkout flow shared by every module.
 *
 * Creates a pending module subscription linked to a payment provider
 * reference, then renders the shared checkout page where the user pays via
 * PawaPay. On webhook completion, the subscription is activated by
 * ActivateSubscriptionOnPaymentCompleted.
 */
class SubscriptionCheckoutController extends Controller
{
    private const SUPPORTED_BILLING_CYCLES = ['monthly', 'annual'];

    public function __construct(
        private readonly ModuleSubscriptionService $subscriptions,
        private readonly TierConfigurationService $tierConfig,
    ) {}

    /**
     * Shared pricing/plans page for a module. Every module that has tier
     * configuration in config/modules/*.php can render this as its
     * subscription entry point; each plan links to the unified checkout.
     */
    public function pricing(Request $request, string $moduleId)
    {
        $tiers = $this->tierConfig->getAllTiersForDisplay($moduleId);

        if (empty($tiers)) {
            abort(404, 'No plans configured for this module');
        }

        $user = $request->user();
        $currentTier = 'free';
        $isAdmin = false;

        if ($user) {
            $subscriptionService = app(SubscriptionService::class);
            $isAdmin = app(ModuleAccessService::class)->userIsAdmin($user);
            $subscription = $subscriptionService->getUserTier($user, $moduleId);
            $currentTier = $subscription ?: 'free';
        }

        $moduleConfig = $this->tierConfig->getModuleConfig($moduleId);

        // Subdomain modules (growstream) have no /workspace; link back to their
        // home page instead. Other modules return to the platform workspace.
        $back = ['label' => 'Back to workspace', 'url' => route('workspace')];
        if ($moduleId === 'growstream' && Route::has('growstream.home')) {
            $back = ['label' => 'Back to GrowStream', 'url' => route('growstream.home')];
        }

        return Inertia::render('Payments/ModulePlans', [
            'module' => [
                'id' => $moduleId,
                'name' => $moduleConfig['name'] ?? ucfirst($moduleId),
                'color' => $moduleConfig['color'] ?? 'emerald',
            ],
            'tiers' => $tiers,
            'currentTier' => $currentTier,
            'isAdmin' => $isAdmin,
            'back' => $back,
        ]);
    }

    /**
     * Render the shared checkout page for a module subscription.
     */
    public function show(Request $request, string $moduleId)
    {
        $validated = $request->validate([
            'tier' => 'required|string',
            'billing_cycle' => 'required|in:' . implode(',', self::SUPPORTED_BILLING_CYCLES),
            'return_url' => 'nullable|string',
        ]);

        $tierConfig = $this->tierConfig->getTierConfig($moduleId, $validated['tier']);

        if (!$tierConfig) {
            abort(404, 'Tier not found');
        }

        $amount = (float) ($validated['billing_cycle'] === 'annual'
            ? ($tierConfig['price_annual'] ?? 0)
            : ($tierConfig['price_monthly'] ?? 0));

        // Free tiers activate immediately — no payment required.
        if ($amount <= 0) {
            $this->subscriptions->subscribe(
                userId: $request->user()->id,
                moduleId: ModuleId::fromString($moduleId),
                tier: SubscriptionTier::fromString($validated['tier']),
                amount: Money::fromFloat(0),
                billingCycle: $validated['billing_cycle'],
            );

            return redirect()->to($validated['return_url'] ?? '/workspace')
                ->with('success', 'Your free plan is now active.');
        }

        $subscription = $this->subscriptions->startCheckout(
            userId: $request->user()->id,
            moduleId: ModuleId::fromString($moduleId),
            tier: SubscriptionTier::fromString($validated['tier']),
            amount: Money::fromFloat($amount),
            billingCycle: $validated['billing_cycle'],
        );

        $description = ucfirst($moduleId) . ' ' . ucwords($validated['tier']) . ' subscription';

        return Inertia::render('Payments/SharedCheckout', [
            'amount' => $amount,
            'currency' => 'ZMW',
            'description' => $description,
            'gateway' => 'pawapay',
            'reference' => $subscription->getProviderReference(),
            'returnUrl' => $validated['return_url'] ?? null,
            'organizationId' => null,
            'subscription' => [
                'module_id' => $moduleId,
                'tier' => $validated['tier'],
                'billing_cycle' => $validated['billing_cycle'],
                'provider_reference' => $subscription->getProviderReference(),
            ],
        ]);
    }
}
