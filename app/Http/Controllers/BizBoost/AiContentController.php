<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostAiUsageLogModel;
use App\Services\BizBoost\AIContentSuggestionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiContentController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private AIContentSuggestionService $aiService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $business = $this->getBusiness($request);

        // Check if AI content generator is available
        $hasFeature = $this->subscriptionService->hasFeature($user, 'ai_content_generator', 'bizboost');
        
        // Get AI credits usage
        $canUseAi = $this->subscriptionService->canIncrement($user, 'ai_credits_per_month', 'bizboost');

        // Recent generations
        $recentGenerations = BizBoostAiUsageLogModel::where('business_id', $business->id)
            ->where('was_successful', true)
            ->latest()
            ->take(10)
            ->get(['id', 'content_type', 'prompt', 'response', 'created_at']);

        return Inertia::render('BizBoost/AiContent/Index', [
            'hasFeature' => $hasFeature,
            'canUseAi' => $canUseAi,
            'recentGenerations' => $recentGenerations,
            'contentTypes' => [
                'caption' => 'Social Media Caption',
                'ad' => 'Advertisement Copy',
                'description' => 'Product Description',
                'idea' => 'Marketing Ideas',
                'whatsapp' => 'WhatsApp Message',
                'promo' => 'Promotion Announcement',
            ],
            'industries' => config('modules.bizboost.industry_kits', []),
            'business' => $business,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'content_type' => 'required|string|in:caption,ad,description,idea,whatsapp,promo',
            'context' => 'required|string|max:500',
            'tone' => 'nullable|string|in:professional,casual,friendly,urgent,playful',
            'language' => 'nullable|string|in:en,bem,nya,ton,loz',
            'product_name' => 'nullable|string|max:255',
            'include_emoji' => 'boolean',
            'include_hashtags' => 'boolean',
            'include_cta' => 'boolean',
        ]);

        $user = $request->user();
        $business = $this->getBusiness($request);

        // Check AI credits
        $canUseAi = $this->subscriptionService->canIncrement($user, 'ai_credits_per_month', 'bizboost');
        if (!$canUseAi['allowed']) {
            return response()->json([
                'success' => false,
                'error' => $canUseAi['reason'],
                'upgrade_required' => true,
            ], 403);
        }

        try {
            $generatedContent = $this->aiService->generateContentForParams($validated, $business);

            BizBoostAiUsageLogModel::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'content_type' => $validated['content_type'],
                'model' => config('services.ai.nvidia_model', 'deepseek-ai/deepseek-v4-flash'),
                'input_tokens' => 0,
                'output_tokens' => 0,
                'credits_used' => 1,
                'prompt' => $validated['context'],
                'response' => $generatedContent,
                'was_successful' => true,
            ]);

            return response()->json([
                'success' => true,
                'content' => $generatedContent,
                'credits_remaining' => $this->getRemainingCredits($user),
            ]);

        } catch (\Exception $e) {
            BizBoostAiUsageLogModel::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'content_type' => $validated['content_type'],
                'credits_used' => 0,
                'was_successful' => false,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getRemainingCredits($user): int
    {
        $result = $this->subscriptionService->canIncrement($user, 'ai_credits_per_month', 'bizboost');
        return $result['remaining'] ?? 0;
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
