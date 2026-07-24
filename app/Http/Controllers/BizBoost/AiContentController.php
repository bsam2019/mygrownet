<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\AiUsageLogRepositoryInterface;
use App\Services\BizBoost\AIContentSuggestionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiContentController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private AIContentSuggestionService $aiService,
        private BusinessService $businessService,
        private AiUsageLogRepositoryInterface $aiUsageRepo,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $business = $this->businessService->getBusinessOrFail($user->id);
        $hasFeature = $this->subscriptionService->hasFeature($user, 'ai_content_generator', 'bizboost');
        $canUseAi = $this->subscriptionService->canIncrement($user, 'ai_credits_per_month', 'bizboost');

        $recentGenerations = $this->aiUsageRepo->findByBusiness($business->id, ['was_successful' => true]);

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
            'business' => $business->toArray(),
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
        $business = $this->businessService->getBusinessOrFail($user->id);

        $canUseAi = $this->subscriptionService->canIncrement($user, 'ai_credits_per_month', 'bizboost');
        if (!$canUseAi['allowed']) {
            return response()->json(['success' => false, 'error' => $canUseAi['reason'], 'upgrade_required' => true], 403);
        }

        try {
            $generatedContent = $this->aiService->generateContentForParams($validated, $business);

            $this->aiUsageRepo->save(new \App\Domain\BizBoost\Entities\AiUsageLog(
                id: null,
                businessId: $business->id,
                userId: $user->id,
                feature: $validated['content_type'],
                model: config('services.ai.nvidia_model', 'deepseek-ai/deepseek-v4-flash'),
                inputTokens: 0,
                outputTokens: 0,
                creditsUsed: 1,
                prompt: $validated['context'],
                response: $generatedContent,
                wasSuccessful: true,
                errorMessage: null,
                createdAt: null,
                updatedAt: null,
            ));

            return response()->json(['success' => true, 'content' => $generatedContent, 'credits_remaining' => $this->getRemainingCredits($user)]);

        } catch (\Exception $e) {
            $this->aiUsageRepo->save(new \App\Domain\BizBoost\Entities\AiUsageLog(
                id: null,
                businessId: $business->id,
                userId: $user->id,
                feature: $validated['content_type'],
                model: null,
                inputTokens: 0,
                outputTokens: 0,
                creditsUsed: 0,
                prompt: $validated['context'],
                response: null,
                wasSuccessful: false,
                errorMessage: $e->getMessage(),
                createdAt: null,
                updatedAt: null,
            ));

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function getRemainingCredits($user): int
    {
        $result = $this->subscriptionService->canIncrement($user, 'ai_credits_per_month', 'bizboost');
        return $result['remaining'] ?? 0;
    }
}
