<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Services\DashboardService;
use App\Domain\BizBoost\Services\SaleService;
use App\Domain\BizBoost\Services\MarketingService;
use App\Domain\BizBoost\Services\AiUsageService;
use App\Domain\BizBoost\Repositories\IntegrationRepositoryInterface;
use App\Domain\BizBoost\Repositories\FollowUpReminderRepositoryInterface;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Domain\BizBoost\Repositories\PostRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private BusinessService $businessService,
        private DashboardService $dashboardService,
        private SaleService $saleService,
        private MarketingService $marketingService,
        private AiUsageService $aiUsageService,
        private IntegrationRepositoryInterface $integrationRepo,
        private FollowUpReminderRepositoryInterface $reminderRepo,
        private ProductRepositoryInterface $productRepo,
        private PostRepositoryInterface $postRepo,
    ) {}

    public function index(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $business = $this->businessService->getBusinessByUser($user->id);

        if (!$business) {
            return redirect()->route('bizboost.setup');
        }

        if (!$business->onboardingCompleted) {
            return redirect()->route('bizboost.setup');
        }

        $stats = $this->dashboardService->getStats($business->id);
        $sparklineData = $this->dashboardService->getSparklineData($business->id);
        $engagement = $this->dashboardService->getEngagement($business->id, now()->startOfMonth()->toDateString());
        $hasIntegrations = $this->dashboardService->hasActiveIntegrations($business->id);

        $recentPosts = $this->postRepo->findByBusiness($business->id);
        $recentSales = $this->saleService->getSales($business->id);

        $pendingTasks = count($this->reminderRepo->findByBusiness($business->id, ['status' => 'pending']));
        $scheduledPostsToday = $this->postRepo->countByBusiness($business->id, ['status' => 'scheduled']);
        $lowStockProducts = 0;

        $recommendations = $this->buildRecommendations($stats, $business->id, $hasIntegrations);

        $aiCreditsUsed = $this->aiUsageService->getMonthlyCredits($business->id, now()->startOfMonth()->toDateTimeString(), now()->endOfMonth()->toDateTimeString());
        $aiCreditsLimit = $this->subscriptionService->canIncrement($user, 'ai_credits_per_month', 'bizboost')['limit'] ?? 10;

        return Inertia::render('BizBoost/Dashboard', [
            'business' => ['id' => $business->id, 'name' => $business->name, 'slug' => $business->slug],
            'stats' => array_merge($stats, [
                'engagement' => ['total' => $engagement],
                'ai_credits_limit' => $aiCreditsLimit,
            ]),
            'recentPosts' => array_slice($recentPosts, 0, 5),
            'recentSales' => array_slice($recentSales, 0, 5),
            'recommendations' => $recommendations,
            'sparklineData' => $sparklineData,
            'pendingTasks' => $pendingTasks,
            'scheduledPostsToday' => $scheduledPostsToday,
            'lowStockProducts' => $lowStockProducts,
            'subscriptionTier' => $request->get('subscription_tier', 'free'),
            'subscriptionLimits' => $request->get('subscription_limits', []),
        ]);
    }

    private function buildRecommendations(array $stats, int $businessId, bool $hasIntegrations): array
    {
        $recommendations = [];

        if (($stats['posts_this_month'] ?? 0) < 4) {
            $recommendations[] = [
                'type' => 'content',
                'title' => 'Increase posting frequency',
                'description' => 'You\'ve only posted ' . ($stats['posts_this_month'] ?? 0) . ' times this month. Aim for at least 4 posts per week.',
                'priority' => 'high',
                'action' => 'Create a post',
                'actionUrl' => '/bizboost/posts/create',
            ];
        }

        if (($stats['customers'] ?? 0) === 0 && ($stats['customers'] ?? 0) > 0) {
            $recommendations[] = [
                'type' => 'customers',
                'title' => 'Grow your customer base',
                'description' => 'Consider running a promotion or referral campaign.',
                'priority' => 'high',
                'action' => 'Create campaign',
                'actionUrl' => '/bizboost/campaigns/create',
            ];
        }

        if (($stats['sales_detail']['change'] ?? 0) < 0) {
            $recommendations[] = [
                'type' => 'sales',
                'title' => 'Boost declining sales',
                'description' => 'Sales are down ' . abs($stats['sales_detail']['change']) . '% from last month.',
                'priority' => 'high',
                'action' => 'View analytics',
                'actionUrl' => '/bizboost/analytics',
            ];
        }

        if (!$hasIntegrations) {
            $recommendations[] = [
                'type' => 'marketing',
                'title' => 'Connect social accounts',
                'description' => 'Link your Facebook or Instagram to publish directly.',
                'priority' => 'medium',
                'action' => 'Connect accounts',
                'actionUrl' => '/bizboost/integrations',
            ];
        }

        $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
        usort($recommendations, fn($a, $b) => $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']]);

        return array_slice($recommendations, 0, 6);
    }
}