<?php

namespace App\Http\Controllers\Admin;

use App\Domain\QuickInvoice\Services\AdminDashboardService;
use App\Domain\QuickInvoice\Services\SubscriptionService;
use App\Domain\QuickInvoice\Repositories\AdminSettingRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionTierRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\UsageTrackingRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuickInvoiceAdminController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $adminDashboardService,
        private readonly SubscriptionService $subscriptionService,
        private readonly SubscriptionTierRepositoryInterface $tierRepository,
        private readonly AdminSettingRepositoryInterface $adminSetting,
        private readonly UsageTrackingRepositoryInterface $usageTracking,
    ) {}

    public function dashboard(): Response
    {
        $data = $this->adminDashboardService->getDashboardData();
        $tiers = $this->tierRepository->findAllActive();

        return Inertia::render('Admin/QuickInvoice/Dashboard', [
            'stats' => $data['stats'],
            'subscriptionStats' => $data['subscriptionStats'],
            'recentActivity' => $data['recentActivity'],
            'monetizationSettings' => $data['monetizationSettings'],
            'trialSettings' => $data['trialSettings'],
            'billingStats' => $data['billingStats'],
            'tiers' => $tiers,
        ]);
    }

    public function updateMonetizationSettings(Request $request)
    {
        $request->validate([
            'usage_limits_enabled' => 'required|boolean',
            'free_tier_limit' => 'required|integer|min:0|max:1000',
            'require_subscription' => 'required|boolean',
            'grace_period_days' => 'required|integer|min:0|max:30',
        ]);

        $this->adminSetting->updateMonetizationSettings($request->all(), auth()->id());

        if ($request->usage_limits_enabled) {
            $freeTier = $this->tierRepository->findByName('Free');
            if ($freeTier) {
                $this->tierRepository->update($freeTier['id'], [
                    'documents_per_month' => $request->free_tier_limit,
                ]);
            }
        }

        return back()->with('success', 'Monetization settings updated successfully');
    }

    public function toggleUsageLimits(Request $request)
    {
        $enabled = $request->boolean('enabled');
        $this->adminSetting->setUsageLimitsEnabled($enabled, auth()->id());

        $message = $enabled
            ? 'Usage limits have been enabled. Users will now be restricted based on their subscription tier.'
            : 'Usage limits have been disabled. All users can create unlimited documents.';

        return back()->with('success', $message);
    }

    public function usageAnalytics(Request $request): Response
    {
        $period = $request->get('period', '30d');
        $analytics = $this->adminDashboardService->getUsageAnalytics($period);

        return Inertia::render('Admin/QuickInvoice/Analytics', [
            'period' => $analytics['period'],
            'dailyUsage' => $analytics['dailyUsage'],
            'overallStats' => $analytics['overallStats'],
            'topUsers' => $analytics['topUsers'],
        ]);
    }

    public function updateTrialSettings(Request $request)
    {
        $request->validate([
            'trial_days' => 'required|integer|min:0|max:365',
            'tier_on_trial' => 'required|string|exists:quick_invoice_subscription_tiers,name',
            'require_payment_after_trial' => 'required|boolean',
        ]);

        $this->adminSetting->set('trial_settings', [
            'trial_days' => (int) $request->trial_days,
            'tier_on_trial' => $request->tier_on_trial,
            'require_payment_after_trial' => (bool) $request->require_payment_after_trial,
        ], auth()->id());

        return back()->with('success', 'Trial settings updated successfully');
    }

    public function userManagement(Request $request): Response
    {
        $search = $request->get('search');
        $tierFilter = $request->get('tier');

        $subscriptions = $this->subscriptionService->getUserManagementList($search, $tierFilter);

        $tiers = $this->tierRepository->findAllActive();

        return Inertia::render('Admin/QuickInvoice/UserManagement', [
            'subscriptions' => $subscriptions,
            'tiers' => $tiers,
            'filters' => [
                'search' => $search,
                'tier' => $tierFilter,
            ],
        ]);
    }

    public function tiers(): Response
    {
        $tiers = $this->adminDashboardService->getTiers();

        return Inertia::render('Admin/QuickInvoice/Tiers/Index', [
            'tiers' => $tiers,
        ]);
    }

    public function createTier(): Response
    {
        return Inertia::render('Admin/QuickInvoice/Tiers/Form', [
            'tier' => null,
        ]);
    }

    public function storeTier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:quick_invoice_subscription_tiers,name',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'documents_per_month' => 'required|integer|min:-1',
            'features' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $this->tierRepository->create([
            'name' => $request->name,
            'price' => $request->price,
            'currency' => $request->currency,
            'documents_per_month' => $request->documents_per_month,
            'features' => $request->features,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.quick-invoice.tiers')
            ->with('success', 'Plan created successfully');
    }

    public function editTier(int $id): Response
    {
        $tier = $this->tierRepository->findById($id);

        if (!$tier) {
            abort(404);
        }

        return Inertia::render('Admin/QuickInvoice/Tiers/Form', [
            'tier' => [
                'id' => $tier['id'],
                'name' => $tier['name'],
                'price' => (float) $tier['price'],
                'currency' => $tier['currency'],
                'documents_per_month' => $tier['documents_per_month'],
                'features' => $tier['features'],
                'is_active' => $tier['is_active'],
            ],
        ]);
    }

    public function updateTier(int $id, Request $request)
    {
        $tier = $this->tierRepository->findById($id);

        if (!$tier) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:100|unique:quick_invoice_subscription_tiers,name,' . $id,
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'documents_per_month' => 'required|integer|min:-1',
            'features' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $this->tierRepository->update($id, [
            'name' => $request->name,
            'price' => $request->price,
            'currency' => $request->currency,
            'documents_per_month' => $request->documents_per_month,
            'features' => $request->features,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.quick-invoice.tiers')
            ->with('success', 'Plan updated successfully');
    }

    public function destroyTier(int $id)
    {
        if ($this->tierRepository->hasSubscribers($id)) {
            return back()->with('error', 'Cannot delete a plan that has active subscribers. Deactivate it instead.');
        }

        $this->tierRepository->delete($id);

        return redirect()->route('admin.quick-invoice.tiers')
            ->with('success', 'Plan deleted successfully');
    }
}