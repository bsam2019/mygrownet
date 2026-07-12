<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Services\BizBoost\AIContentSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdvisorController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private AIContentSuggestionService $aiService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $business = $this->getBusiness($request);

        // Check feature access
        $hasAdvisor = $this->subscriptionService->hasFeature($user, 'ai_advisor', 'bizboost');

        if (!$hasAdvisor) {
            return Inertia::render('BizBoost/FeatureUpgradeRequired', [
                'feature' => 'AI Business Advisor',
                'description' => 'Get personalized business recommendations and growth strategies.',
                'requiredTier' => 'professional',
            ]);
        }

        // Get recent chat history
        $chatHistory = DB::table('bizboost_ai_usage_logs')
            ->where('business_id', $business->id)
            ->where('type', 'advisor_chat')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        // Get business insights for recommendations
        $insights = $this->getBusinessInsights($business);

        return Inertia::render('BizBoost/Advisor/Index', [
            'chatHistory' => $chatHistory,
            'insights' => $insights,
            'recommendations' => $this->generateRecommendations($business, $insights),
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = $request->user();
        $business = $this->getBusiness($request);

        // Check AI credits
        $result = $this->subscriptionService->canIncrement($user, 'ai_credits_per_month', 'bizboost');
        if (!$result['allowed']) {
            return response()->json([
                'error' => 'AI credits exhausted. Please upgrade your plan.',
                'upgrade_required' => true,
            ], 403);
        }

        // Get business context for AI
        $context = $this->getBusinessContext($business);

        // Generate AI response
        $response = $this->aiService->generateAdvisorResponse($validated['message'], $context);

        // Log the interaction
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

        return response()->json([
            'message' => $response,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function recommendations(Request $request): JsonResponse
    {
        $business = $this->getBusiness($request);
        $insights = $this->getBusinessInsights($business);
        $recommendations = $this->generateRecommendations($business, $insights);

        return response()->json([
            'recommendations' => $recommendations,
            'insights' => $insights,
        ]);
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }

    private function getBusinessInsights(BizBoostBusinessModel $business): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        return [
            'total_products' => $business->products()->count(),
            'total_customers' => $business->customers()->count(),
            'total_sales_30d' => $business->sales()->where('created_at', '>=', $thirtyDaysAgo)->sum('total_amount'),
            'sales_count_30d' => $business->sales()->where('created_at', '>=', $thirtyDaysAgo)->count(),
            'posts_30d' => $business->posts()->where('created_at', '>=', $thirtyDaysAgo)->count(),
            'published_posts_30d' => $business->posts()->where('status', 'published')->where('published_at', '>=', $thirtyDaysAgo)->count(),
            'active_campaigns' => $business->campaigns()->where('status', 'active')->count(),
            'top_product' => $business->products()->withCount('sales')->orderByDesc('sales_count')->first()?->name,
            'new_customers_30d' => $business->customers()->where('created_at', '>=', $thirtyDaysAgo)->count(),
            'has_integrations' => $business->integrations()->where('status', 'active')->exists(),
        ];
    }

    private function getBusinessContext(BizBoostBusinessModel $business): array
    {
        return [
            'business_name' => $business->name,
            'industry' => $business->industry,
            'insights' => $this->getBusinessInsights($business),
        ];
    }

    private function generateRecommendations(BizBoostBusinessModel $business, array $insights): array
    {
        $recommendations = [];

        // Check posting frequency
        if ($insights['posts_30d'] < 12) {
            $recommendations[] = [
                'type' => 'marketing',
                'priority' => 'high',
                'title' => 'Increase posting frequency',
                'description' => 'You\'ve only posted ' . $insights['posts_30d'] . ' times in 30 days. Aim for at least 3 posts per week.',
                'action' => 'Create Post',
                'action_route' => 'bizboost.posts.create',
            ];
        }

        // Check social media integration
        if (!$insights['has_integrations']) {
            $recommendations[] = [
                'type' => 'setup',
                'priority' => 'high',
                'title' => 'Connect social media',
                'description' => 'Connect your Facebook or Instagram to auto-publish posts and save time.',
                'action' => 'Connect Now',
                'action_route' => 'bizboost.integrations.index',
            ];
        }

        // Check campaigns
        if ($insights['active_campaigns'] === 0) {
            $recommendations[] = [
                'type' => 'marketing',
                'priority' => 'medium',
                'title' => 'Start a campaign',
                'description' => 'Automated campaigns can help you maintain consistent marketing without daily effort.',
                'action' => 'Create Campaign',
                'action_route' => 'bizboost.campaigns.create',
            ];
        }

        // Check customer engagement
        if ($insights['new_customers_30d'] < 5) {
            $recommendations[] = [
                'type' => 'growth',
                'priority' => 'medium',
                'title' => 'Grow your customer base',
                'description' => 'Only ' . $insights['new_customers_30d'] . ' new customers this month. Try running a referral promotion.',
                'action' => 'View Customers',
                'action_route' => 'bizboost.customers.index',
            ];
        }

        // Check products
        if ($insights['total_products'] < 5) {
            $recommendations[] = [
                'type' => 'setup',
                'priority' => 'low',
                'title' => 'Add more products',
                'description' => 'Showcase more of your offerings to attract different customer segments.',
                'action' => 'Add Product',
                'action_route' => 'bizboost.products.create',
            ];
        }

        return $recommendations;
    }
}
