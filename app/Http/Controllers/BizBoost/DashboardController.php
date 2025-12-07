<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        // Redirect to setup if no business exists
        if (!$business) {
            return redirect()->route('bizboost.setup');
        }

        // Redirect to setup if onboarding not completed
        if (!$business->onboarding_completed) {
            return redirect()->route('bizboost.setup');
        }

        // Get dashboard stats
        $stats = $this->getDashboardStats($business);
        $recentPosts = $this->getRecentPosts($business);
        $upcomingPosts = $this->getUpcomingPosts($business);
        $recentSales = $this->getRecentSales($business);
        $topProducts = $this->getTopProducts($business);
        $recommendations = $this->getAiRecommendations($business, $stats);
        $recentActivity = $this->getRecentActivity($business);
        $sparklineData = $this->getSparklineData($business);

        // Get contextual counts for quick actions
        $pendingTasks = DB::table('bizboost_follow_up_reminders')
            ->where('business_id', $business->id)
            ->where('status', 'pending')
            ->where('due_date', '<=', now()->addDay()->toDateString())
            ->count();

        $scheduledPostsToday = $business->posts()
            ->where('status', 'scheduled')
            ->whereDate('scheduled_at', today())
            ->count();

        $lowStockProducts = $business->products()
            ->where('is_active', true)
            ->whereNotNull('stock_quantity')
            ->where('stock_quantity', '<=', 5)
            ->count();

        return Inertia::render('BizBoost/Dashboard', [
            'business' => $business->load('profile'),
            'stats' => $stats,
            'recentPosts' => $recentPosts,
            'upcomingPosts' => $upcomingPosts,
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
            'recommendations' => $recommendations,
            'recentActivity' => $recentActivity,
            'sparklineData' => $sparklineData,
            'pendingTasks' => $pendingTasks,
            'scheduledPostsToday' => $scheduledPostsToday,
            'lowStockProducts' => $lowStockProducts,
            'subscriptionTier' => $request->get('subscription_tier', 'free'),
            'subscriptionLimits' => $request->get('subscription_limits', []),
        ]);
    }

    private function getDashboardStats(BizBoostBusinessModel $business): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->endOfMonth();

        // Posts this month
        $postsThisMonth = $business->posts()
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $postsLastMonth = $business->posts()
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();

        // Sales this month
        $salesThisMonth = $business->sales()
            ->whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        $salesLastMonth = $business->sales()
            ->whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_amount');

        // Customers
        $totalCustomers = $business->customers()->count();
        $newCustomersThisMonth = $business->customers()
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Products
        $totalProducts = $business->products()->where('is_active', true)->count();

        // Engagement (from analytics)
        $engagementThisMonth = DB::table('bizboost_analytics_daily')
            ->where('business_id', $business->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('post_engagements');

        // Get previous period values for trend comparison
        $customersLastMonth = $business->customers()
            ->where('created_at', '<', $startOfMonth)
            ->count();

        $productsLastMonth = $business->products()
            ->where('is_active', true)
            ->where('created_at', '<', $startOfMonth)
            ->count();

        $totalPosts = $business->posts()->count();
        $postsBeforeThisMonth = $business->posts()
            ->where('created_at', '<', $startOfMonth)
            ->count();

        $totalSales = $business->sales()->sum('total_amount');

        // AI credits (from usage logs)
        $aiCreditsUsed = DB::table('bizboost_ai_usage_logs')
            ->where('business_id', $business->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('credits_used');

        // Get limit from subscription (default to free tier)
        $aiCreditsLimit = 10; // Free tier default

        return [
            // Flat structure for stat cards with previous values for trends
            'products' => $totalProducts,
            'products_previous' => $productsLastMonth,
            'customers' => $totalCustomers,
            'customers_previous' => $customersLastMonth,
            'posts' => $totalPosts,
            'posts_previous' => $postsBeforeThisMonth,
            'sales_total' => (int) $totalSales,
            'sales_previous' => (int) $salesLastMonth,
            'posts_this_month' => $postsThisMonth,
            'ai_credits_used' => (int) $aiCreditsUsed,
            'ai_credits_limit' => $aiCreditsLimit,
            
            // Detailed stats for charts/analytics
            'posts_detail' => [
                'current' => $postsThisMonth,
                'previous' => $postsLastMonth,
                'change' => $postsLastMonth > 0 
                    ? round((($postsThisMonth - $postsLastMonth) / $postsLastMonth) * 100, 1) 
                    : 0,
            ],
            'sales_detail' => [
                'current' => $salesThisMonth,
                'previous' => $salesLastMonth,
                'change' => $salesLastMonth > 0 
                    ? round((($salesThisMonth - $salesLastMonth) / $salesLastMonth) * 100, 1) 
                    : 0,
            ],
            'customers_detail' => [
                'total' => $totalCustomers,
                'new' => $newCustomersThisMonth,
            ],
            'engagement' => [
                'total' => $engagementThisMonth,
            ],
        ];
    }

    private function getRecentPosts(BizBoostBusinessModel $business): array
    {
        return $business->posts()
            ->with('media')
            ->latest()
            ->take(5)
            ->get()
            ->toArray();
    }

    private function getUpcomingPosts(BizBoostBusinessModel $business): array
    {
        return $business->posts()
            ->with('media')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->take(5)
            ->get()
            ->toArray();
    }

    private function getRecentSales(BizBoostBusinessModel $business): array
    {
        return $business->sales()
            ->with(['customer', 'product'])
            ->latest('sale_date')
            ->take(5)
            ->get()
            ->toArray();
    }

    private function getTopProducts(BizBoostBusinessModel $business): array
    {
        return $business->products()
            ->where('is_active', true)
            ->withCount('sales')
            ->orderByDesc('sales_count')
            ->take(5)
            ->get()
            ->toArray();
    }

    private function getAiRecommendations(BizBoostBusinessModel $business, array $stats): array
    {
        $recommendations = [];

        // Check posting frequency
        if ($stats['posts_this_month'] < 4) {
            $recommendations[] = [
                'type' => 'content',
                'title' => 'Increase posting frequency',
                'description' => 'You\'ve only posted ' . $stats['posts_this_month'] . ' times this month. Aim for at least 4 posts per week to maintain engagement.',
                'priority' => 'high',
                'action' => 'Create a post',
                'actionUrl' => '/bizboost/posts/create',
            ];
        }

        // Check if no scheduled posts
        $scheduledCount = $business->posts()->where('status', 'scheduled')->count();
        if ($scheduledCount === 0) {
            $recommendations[] = [
                'type' => 'marketing',
                'title' => 'Schedule content ahead',
                'description' => 'You have no scheduled posts. Plan your content in advance to maintain consistent presence.',
                'priority' => 'medium',
                'action' => 'Schedule posts',
                'actionUrl' => '/bizboost/calendar',
            ];
        }

        // Check customer growth
        if ($stats['customers_detail']['new'] === 0 && $stats['customers'] > 0) {
            $recommendations[] = [
                'type' => 'customers',
                'title' => 'Grow your customer base',
                'description' => 'No new customers this month. Consider running a promotion or referral campaign.',
                'priority' => 'high',
                'action' => 'Create campaign',
                'actionUrl' => '/bizboost/campaigns/create',
            ];
        }

        // Check sales performance
        if ($stats['sales_detail']['change'] < 0) {
            $recommendations[] = [
                'type' => 'sales',
                'title' => 'Boost declining sales',
                'description' => 'Sales are down ' . abs($stats['sales_detail']['change']) . '% from last month. Try promoting your best-selling products.',
                'priority' => 'high',
                'action' => 'View analytics',
                'actionUrl' => '/bizboost/analytics',
            ];
        }

        // Check product catalog
        if ($stats['products'] < 5) {
            $recommendations[] = [
                'type' => 'sales',
                'title' => 'Expand your product catalog',
                'description' => 'You only have ' . $stats['products'] . ' products. Adding more variety can increase sales.',
                'priority' => 'low',
                'action' => 'Add products',
                'actionUrl' => '/bizboost/products/create',
            ];
        }

        // Check engagement
        if ($stats['engagement']['total'] === 0 && $stats['posts_this_month'] > 0) {
            $recommendations[] = [
                'type' => 'engagement',
                'title' => 'Improve post engagement',
                'description' => 'Your posts aren\'t getting engagement. Try using more visuals and asking questions.',
                'priority' => 'medium',
                'action' => 'Get AI suggestions',
                'actionUrl' => '/bizboost/ai',
            ];
        }

        // Check social integrations
        $hasIntegrations = DB::table('bizboost_integrations')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->exists();

        if (!$hasIntegrations) {
            $recommendations[] = [
                'type' => 'marketing',
                'title' => 'Connect social accounts',
                'description' => 'Link your Facebook or Instagram to publish directly and track performance.',
                'priority' => 'medium',
                'action' => 'Connect accounts',
                'actionUrl' => '/bizboost/integrations',
            ];
        }

        // Sort by priority
        $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
        usort($recommendations, fn($a, $b) => $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']]);

        return array_slice($recommendations, 0, 6);
    }

    /**
     * Get recent activity feed for the dashboard
     */
    private function getRecentActivity(BizBoostBusinessModel $business): array
    {
        $activities = [];

        // Recent sales
        $recentSales = $business->sales()
            ->with('customer')
            ->latest('sale_date')
            ->take(5)
            ->get();

        foreach ($recentSales as $sale) {
            $activities[] = [
                'id' => 'sale-' . $sale->id,
                'type' => 'sale',
                'title' => 'New Sale',
                'description' => $sale->customer?->name ?? 'Walk-in customer',
                'amount' => $sale->total_amount,
                'timestamp' => $sale->sale_date ?? $sale->created_at,
                'href' => '/bizboost/sales/' . $sale->id,
                'actions' => [
                    ['label' => 'View', 'href' => '/bizboost/sales/' . $sale->id],
                ],
            ];
        }

        // Recent customers
        $recentCustomers = $business->customers()
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentCustomers as $customer) {
            $activities[] = [
                'id' => 'customer-' . $customer->id,
                'type' => 'customer',
                'title' => 'New Customer',
                'description' => $customer->name,
                'timestamp' => $customer->created_at,
                'href' => '/bizboost/customers/' . $customer->id,
            ];
        }

        // Recent posts
        $recentPosts = $business->posts()
            ->whereIn('status', ['published', 'scheduled'])
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentPosts as $post) {
            $activities[] = [
                'id' => 'post-' . $post->id,
                'type' => 'post',
                'title' => $post->status === 'published' ? 'Post Published' : 'Post Scheduled',
                'description' => \Illuminate\Support\Str::limit($post->caption, 50),
                'timestamp' => $post->published_at ?? $post->scheduled_at ?? $post->created_at,
                'href' => '/bizboost/posts/' . $post->id,
            ];
        }

        // Sort by timestamp descending
        usort($activities, fn($a, $b) => strtotime($b['timestamp']) <=> strtotime($a['timestamp']));

        return array_slice($activities, 0, 8);
    }

    /**
     * Get sparkline data for stat cards (last 7 days)
     */
    private function getSparklineData(BizBoostBusinessModel $business): array
    {
        $days = 7;
        $dates = collect(range(0, $days - 1))->map(fn($i) => now()->subDays($days - 1 - $i)->format('Y-m-d'));

        // Sales per day
        $salesByDay = $business->sales()
            ->where('sale_date', '>=', now()->subDays($days))
            ->selectRaw('DATE(sale_date) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Customers per day
        $customersByDay = $business->customers()
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Posts per day
        $postsByDay = $business->posts()
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Products per day
        $productsByDay = $business->products()
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        return [
            'sales' => $dates->map(fn($date) => (int) ($salesByDay[$date] ?? 0))->values()->toArray(),
            'customers' => $dates->map(fn($date) => (int) ($customersByDay[$date] ?? 0))->values()->toArray(),
            'posts' => $dates->map(fn($date) => (int) ($postsByDay[$date] ?? 0))->values()->toArray(),
            'products' => $dates->map(fn($date) => (int) ($productsByDay[$date] ?? 0))->values()->toArray(),
        ];
    }
}
