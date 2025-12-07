<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostAiUsageLogModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiContentController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
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
            // Build the prompt
            $prompt = $this->buildPrompt($validated, $business);
            
            // In production, this would call OpenAI API
            // For now, return a placeholder response
            $generatedContent = $this->generateMockContent($validated);

            // Log the usage
            BizBoostAiUsageLogModel::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'content_type' => $validated['content_type'],
                'model' => 'gpt-4o-mini',
                'input_tokens' => strlen($prompt),
                'output_tokens' => strlen($generatedContent),
                'credits_used' => 1,
                'prompt' => $prompt,
                'response' => $generatedContent,
                'was_successful' => true,
            ]);

            return response()->json([
                'success' => true,
                'content' => $generatedContent,
                'credits_remaining' => $this->getRemainingCredits($user),
            ]);

        } catch (\Exception $e) {
            // Log failed attempt
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
                'error' => 'Failed to generate content. Please try again.',
            ], 500);
        }
    }

    private function buildPrompt(array $params, BizBoostBusinessModel $business): string
    {
        $type = $params['content_type'];
        $context = $params['context'];
        $tone = $params['tone'] ?? 'friendly';
        $language = $params['language'] ?? 'en';
        
        $languageNames = [
            'en' => 'English',
            'bem' => 'Bemba',
            'nya' => 'Nyanja',
            'ton' => 'Tonga',
            'loz' => 'Lozi',
        ];

        $prompt = "You are a marketing assistant for a {$business->industry} business in Zambia called '{$business->name}'.\n\n";
        $prompt .= "Generate a {$type} with a {$tone} tone in {$languageNames[$language]}.\n\n";
        $prompt .= "Context: {$context}\n\n";
        
        if (!empty($params['product_name'])) {
            $prompt .= "Product/Service: {$params['product_name']}\n";
        }
        
        $prompt .= "Requirements:\n";
        if ($params['include_emoji'] ?? false) {
            $prompt .= "- Include relevant emojis\n";
        }
        if ($params['include_hashtags'] ?? false) {
            $prompt .= "- Include 3-5 relevant hashtags\n";
        }
        if ($params['include_cta'] ?? false) {
            $prompt .= "- Include a clear call-to-action\n";
        }

        return $prompt;
    }

    private function generateMockContent(array $params): string
    {
        // Mock responses for development
        $mockResponses = [
            'caption' => "✨ New arrivals just dropped! Come check out our latest collection - you won't want to miss this! 🛍️\n\nVisit us today or DM for more info.\n\n#NewArrivals #ShopLocal #ZambianBusiness #QualityProducts",
            'ad' => "🎉 SPECIAL OFFER! 🎉\n\nGet 20% OFF on all items this weekend only!\n\n✅ Quality products\n✅ Great prices\n✅ Excellent service\n\nDon't miss out - offer ends Sunday!\n\n📍 Visit us today\n📱 WhatsApp: [Your Number]",
            'description' => "Discover our premium quality products, carefully selected to meet your needs. Made with the finest materials and designed for lasting satisfaction. Perfect for everyday use or special occasions.",
            'idea' => "Here are 5 marketing ideas for your business:\n\n1. Run a 'Customer of the Week' feature on social media\n2. Create a loyalty program with rewards\n3. Partner with complementary local businesses\n4. Host a live product demonstration\n5. Share behind-the-scenes content",
            'whatsapp' => "Hi! 👋\n\nThank you for your interest in our products!\n\nWe have some amazing deals available right now. Would you like me to share our latest catalog with you?\n\nFeel free to ask any questions - we're here to help! 😊",
            'promo' => "🔥 FLASH SALE ALERT! 🔥\n\nFor the next 48 hours only:\n\n💰 Up to 30% OFF selected items\n🚚 FREE delivery on orders over K500\n🎁 FREE gift with every purchase\n\nHurry - limited stock available!\n\nShop now: [Link]",
        ];

        return $mockResponses[$params['content_type']] ?? $mockResponses['caption'];
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
