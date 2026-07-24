<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Services\BizBoost\AIContentSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdvisorController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private AIContentSuggestionService $aiService,
        private BusinessService $businessService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $business = $this->businessService->getBusinessOrFail($user->id);

        if (!$this->subscriptionService->hasFeature($user, 'ai_advisor', 'bizboost')) {
            return Inertia::render('BizBoost/FeatureUpgradeRequired', [
                'feature' => 'AI Business Advisor',
                'description' => 'Get personalized business recommendations and growth strategies.',
                'requiredTier' => 'professional',
            ]);
        }

        $chatHistory = DB::table('bizboost_ai_usage_logs')
            ->where('business_id', $business->id)
            ->where('type', 'advisor_chat')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        $insights = $this->getBusinessInsights($business->id);

        return Inertia::render('BizBoost/Advisor/Index', [
            'chatHistory' => $chatHistory,
            'insights' => $insights,
            'recommendations' => $this->generateRecommendations($business->id, $insights),
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate(['message' => 'required|string|max:2000']);

        $user = $request->user();
        $business = $this->businessService->getBusinessOrFail($user->id);

        $result = $this->subscriptionService->canIncrement($user, 'ai_credits_per_month', 'bizboost');
        if (!$result['allowed']) {
            return response()->json(['error' => 'AI credits exhausted. Please upgrade your plan.', 'upgrade_required' => true], 403);
        }

        $context = [
            'business_name' => $business->name,
            'industry' => $business->industry,
            'insights' => $this->getBusinessInsights($business->id),
        ];

        $response = $this->aiService->generateAdvisorResponse($validated['message'], $context);

        DB::table('bizboost_ai_usage_logs')->insert([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'type' => 'advisor_chat',
            'content_type' => 'advisor_chat',
            'prompt' => $validated['message'],
            'response' => $response,
            'credits_used' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => $response, 'timestamp' => now()->toIso8601String()]);
    }

    public function recommendations(Request $request): JsonResponse
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $insights = $this->getBusinessInsights($business->id);

        return response()->json([
            'recommendations' => $this->generateRecommendations($business->id, $insights),
            'insights' => $insights,
        ]);
    }

    private function getBusinessInsights(int $businessId): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        return [
            'total_products' => DB::table('bizboost_products')->where('business_id', $businessId)->count(),
            'total_customers' => DB::table('bizboost_customers')->where('business_id', $businessId)->count(),
            'total_sales_30d' => (float) DB::table('bizboost_sales')->where('business_id', $businessId)->where('created_at', '>=', $thirtyDaysAgo)->sum('total_amount'),
            'sales_count_30d' => DB::table('bizboost_sales')->where('business_id', $businessId)->where('created_at', '>=', $thirtyDaysAgo)->count(),
            'posts_30d' => DB::table('bizboost_posts')->where('business_id', $businessId)->where('created_at', '>=', $thirtyDaysAgo)->count(),
            'published_posts_30d' => DB::table('bizboost_posts')->where('business_id', $businessId)->where('status', 'published')->where('published_at', '>=', $thirtyDaysAgo)->count(),
            'active_campaigns' => DB::table('bizboost_campaigns')->where('business_id', $businessId)->where('status', 'active')->count(),
            'new_customers_30d' => DB::table('bizboost_customers')->where('business_id', $businessId)->where('created_at', '>=', $thirtyDaysAgo)->count(),
            'has_integrations' => DB::table('bizboost_integrations')->where('business_id', $businessId)->where('status', 'active')->exists(),
        ];
    }

    private function generateRecommendations(int $businessId, array $insights): array
    {
        $recommendations = [];

        if ($insights['posts_30d'] < 12) {
            $recommendations[] = [
                'type' => 'marketing', 'priority' => 'high',
                'title' => 'Increase posting frequency',
                'description' => 'You\'ve only posted ' . $insights['posts_30d'] . ' times in 30 days. Aim for at least 3 posts per week.',
                'action' => 'Create Post', 'action_route' => 'bizboost.posts.create',
            ];
        }

        if (!$insights['has_integrations']) {
            $recommendations[] = [
                'type' => 'setup', 'priority' => 'high',
                'title' => 'Connect social media',
                'description' => 'Connect your Facebook or Instagram to auto-publish posts and save time.',
                'action' => 'Connect Now', 'action_route' => 'bizboost.integrations.index',
            ];
        }

        if ($insights['active_campaigns'] === 0) {
            $recommendations[] = [
                'type' => 'marketing', 'priority' => 'medium',
                'title' => 'Start a campaign',
                'description' => 'Automated campaigns can help you maintain consistent marketing without daily effort.',
                'action' => 'Create Campaign', 'action_route' => 'bizboost.campaigns.create',
            ];
        }

        if ($insights['new_customers_30d'] < 5) {
            $recommendations[] = [
                'type' => 'growth', 'priority' => 'medium',
                'title' => 'Grow your customer base',
                'description' => 'Only ' . $insights['new_customers_30d'] . ' new customers this month. Try running a referral promotion.',
                'action' => 'View Customers', 'action_route' => 'bizboost.customers.index',
            ];
        }

        if ($insights['total_products'] < 5) {
            $recommendations[] = [
                'type' => 'setup', 'priority' => 'low',
                'title' => 'Add more products',
                'description' => 'Showcase more of your offerings to attract different customer segments.',
                'action' => 'Add Product', 'action_route' => 'bizboost.products.create',
            ];
        }

        return $recommendations;
    }
}
