<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\AIFeedback;
use App\Services\GrowBuilder\AIContentService;
use App\Services\GrowBuilder\AIUsageService;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private AIContentService $aiService,
        private AIUsageService $aiUsageService,
    ) {}
    
    /**
     * Check if AI is available and get usage stats
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $usageStats = $this->aiUsageService->getUsageStats($user);
        
        return response()->json([
            'available' => $this->aiService->isConfigured(),
            'provider' => $this->aiService->getProvider(),
            'usage' => $usageStats,
            'can_use' => $this->aiUsageService->canUseAI($user),
        ]);
    }

    /**
     * Check AI access before processing - returns error response if not allowed
     */
    private function checkAIAccess(Request $request, string $feature = 'content'): ?array
    {
        $user = $request->user();
        
        // Check if user can use AI at all
        if (!$this->aiUsageService->canUseAI($user)) {
            return [
                'error' => 'AI limit reached',
                'message' => $this->aiUsageService->getUpgradeMessage($user),
                'usage' => $this->aiUsageService->getUsageStats($user),
                'upgrade_required' => true,
            ];
        }
        
        // Check if user has access to this specific feature
        if (!$this->aiUsageService->hasFeatureAccess($user, $feature)) {
            $featureNames = [
                'seo' => 'SEO Assistant',
                'section' => 'Section Generator',
                'priority' => 'Priority Processing',
            ];
            
            return [
                'error' => 'Feature not available',
                'message' => ($featureNames[$feature] ?? ucfirst($feature)) . ' is available on Business plan and above.',
                'feature' => $feature,
                'upgrade_required' => true,
            ];
        }
        
        return null; // Access granted
    }

    /**
     * Record AI usage after successful generation
     */
    private function recordUsage(Request $request, string $promptType, ?string $prompt = null, int $tokens = 0, ?int $siteId = null): void
    {
        $this->aiUsageService->recordUsage(
            $request->user(),
            $promptType,
            $prompt,
            $tokens,
            $siteId,
            $this->aiService->getProvider()
        );
    }
    
    /**
     * Record AI feedback (when user applies or rejects a suggestion)
     */
    public function recordFeedback(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'action_type' => 'required|string|max:50',
            'section_type' => 'nullable|string|max:50',
            'business_type' => 'nullable|string|max:50',
            'applied' => 'required|boolean',
            'user_message' => 'nullable|string|max:1000',
            'ai_response' => 'nullable|string|max:2000',
            'context' => 'nullable|array',
        ]);
        
        AIFeedback::create([
            'site_id' => $siteId,
            'user_id' => $request->user()->id,
            'action_type' => $validated['action_type'],
            'section_type' => $validated['section_type'] ?? null,
            'business_type' => $validated['business_type'] ?? null,
            'applied' => $validated['applied'],
            'user_message' => $validated['user_message'] ?? null,
            'ai_response' => isset($validated['ai_response']) 
                ? substr($validated['ai_response'], 0, 2000) 
                : null,
            'context' => $validated['context'] ?? null,
        ]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Get AI feedback statistics - combines site, industry, and global learning
     */
    public function getFeedbackStats(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $businessType = $request->query('business_type');
        
        // Get combined stats (site + industry + global)
        $combinedStats = AIFeedback::getCombinedStats($siteId, $businessType);
        $recent = AIFeedback::getRecentForSite($siteId, 20);
        $topContent = AIFeedback::getTopPerformingContent(5);
        $aiInsights = AIFeedback::getInsightsForAI($siteId, $businessType);
        
        return response()->json([
            'success' => true,
            'stats' => $combinedStats['primary'],
            'combined' => $combinedStats,
            'recent' => $recent,
            'topContent' => $topContent,
            'aiInsights' => $aiInsights,
        ]);
    }
    
    /**
     * Classify user intent using AI
     * Makes the assistant smarter by understanding natural language
     */
    public function classifyIntent(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'context' => 'nullable|array',
        ]);
        
        $result = $this->aiService->classifyIntent(
            $validated['message'],
            $validated['context'] ?? []
        );
        
        return response()->json([
            'success' => true,
            'intent' => $result,
        ]);
    }
    
    /**
     * Smart Chat - AI-first approach to handle any user request
     * The AI understands intent AND generates content in one call
     */
    public function smartChat(Request $request, int $siteId)
        
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }

        // Check AI access
        $accessError = $this->checkAIAccess($request, 'content');
        if ($accessError) {
            return response()->json($accessError, 403);
        }
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'nullable|array',
        ]);
        
        // Add site info to context
        $context = $validated['context'] ?? [];
        $context['siteName'] = $site->getName();
        
        $result = $this->aiService->smartChat(
            $validated['message'],
            $context
        );
        
        // Record usage to database
        $this->recordUsage($request, 'smart_chat', $validated['message'], 0, $siteId);
        
        // Get updated usage stats
        $usageStats = $this->aiUsageService->getUsageStats($request->user());
        
        return response()->json([
            'success' => true,
            'result' => $result,
            'usage' => $usageStats,
        ]);
    }
    
    /**
     * Generate content for a section
     */
    public function generateContent(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'section_type' => 'required|string',
            'business_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:100',
            'business_description' => 'nullable|string|max:1000',
            'tone' => 'nullable|string|in:professional,friendly,casual,formal,playful',
        ]);
        
        $businessName = $validated['business_name'] ?? $site->getName();
        $businessType = $validated['business_type'] ?? 'business';
        
        $content = $this->aiService->generateSectionContent(
            $validated['section_type'],
            $businessName,
            $businessType,
            $validated['business_description'] ?? null,
            $validated['tone'] ?? 'professional'
        );
        
        return response()->json([
            'success' => true,
            'content' => $content,
        ]);
    }
    
    /**
     * Generate SEO meta description
     */
    public function generateMeta(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'page_title' => 'required|string|max:255',
            'page_content' => 'required|string|max:5000',
        ]);
        
        $description = $this->aiService->generateMetaDescription(
            $validated['page_title'],
            $validated['page_content'],
            $site->getName()
        );
        
        $keywords = $this->aiService->generateKeywords(
            $site->getName(),
            'business',
            $validated['page_content']
        );
        
        return response()->json([
            'success' => true,
            'meta_description' => $description,
            'keywords' => $keywords,
        ]);
    }
    
    /**
     * Suggest color palette
     */
    public function suggestColors(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'business_type' => 'nullable|string|max:100',
            'mood' => 'nullable|string|in:professional,modern,playful,elegant,bold,minimal',
        ]);
        
        $palette = $this->aiService->suggestColorPalette(
            $validated['business_type'] ?? 'business',
            $validated['mood'] ?? 'professional'
        );
        
        return response()->json([
            'success' => true,
            'palette' => $palette,
        ]);
    }
    
    /**
     * Improve text content
     */
    public function improveText(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'text' => 'required|string|max:5000',
            'style' => 'nullable|string|in:professional,friendly,casual,formal,persuasive,concise',
            'instruction' => 'nullable|string|max:500',
        ]);
        
        $improved = $this->aiService->improveText(
            $validated['text'],
            $validated['style'] ?? 'professional',
            $validated['instruction'] ?? null
        );
        
        return response()->json([
            'success' => true,
            'improved_text' => $improved,
        ]);
    }
    
    /**
     * Translate content
     */
    public function translate(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'text' => 'required|string|max:5000',
            'target_language' => 'required|string|in:bem,nya,ton,loz,sw,fr',
        ]);
        
        $translated = $this->aiService->translateContent(
            $validated['text'],
            $validated['target_language']
        );
        
        return response()->json([
            'success' => true,
            'translated_text' => $translated,
            'target_language' => $validated['target_language'],
        ]);
    }
    
    /**
     * Suggest image keywords
     */
    public function suggestImages(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'section_type' => 'required|string',
            'content' => 'required|string|max:2000',
            'business_type' => 'nullable|string|max:100',
        ]);
        
        $keywords = $this->aiService->suggestImageKeywords(
            $validated['section_type'],
            $validated['content'],
            $validated['business_type'] ?? 'business'
        );
        
        return response()->json([
            'success' => true,
            'keywords' => $keywords,
        ]);
    }
    
    /**
     * Generate testimonials
     */
    public function generateTestimonials(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'business_type' => 'nullable|string|max:100',
            'count' => 'nullable|integer|min:1|max:10',
        ]);
        
        $testimonials = $this->aiService->generateTestimonials(
            $site->getName(),
            $validated['business_type'] ?? 'business',
            $validated['count'] ?? 3
        );
        
        return response()->json([
            'success' => true,
            'testimonials' => $testimonials,
        ]);
    }
    
    /**
     * Generate FAQs
     */
    public function generateFAQs(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'business_type' => 'nullable|string|max:100',
            'count' => 'nullable|integer|min:1|max:20',
        ]);
        
        $faqs = $this->aiService->generateFAQs(
            $site->getName(),
            $validated['business_type'] ?? 'business',
            $validated['count'] ?? 5
        );
        
        return response()->json([
            'success' => true,
            'faqs' => $faqs,
        ]);
    }
    
    /**
     * Generate a complete page with AI-powered content
     */
    public function generatePage(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'page_type' => 'required|string|in:about,services,contact,pricing,faq,testimonials,gallery,blog',
            'business_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:100',
            'business_description' => 'nullable|string|max:2000',
            'existing_colors' => 'nullable|array',
        ]);
        
        $businessName = $validated['business_name'] ?? $site->getName();
        $businessType = $validated['business_type'] ?? 'business';
        
        $pageData = $this->aiService->generatePage(
            $validated['page_type'],
            $businessName,
            $businessType,
            $validated['business_description'] ?? null,
            $validated['existing_colors'] ?? null
        );
        
        return response()->json([
            'success' => true,
            'page' => $pageData,
        ]);
    }
    
    /**
     * Generate a page with detailed/comprehensive requirements
     * Handles complex prompts with specific sections, tone, style, and content guidelines
     */
    public function generatePageDetailed(Request $request, int $siteId)
    {
        $site = $this->validateSiteAccess($request, $siteId);
        if (!$site) {
            return response()->json(['error' => 'Site not found'], 404);
        }
        
        $validated = $request->validate([
            'page_type' => 'required|string|in:about,services,contact,pricing,faq,testimonials,gallery,blog,home,landing',
            'business_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:100',
            'existing_colors' => 'nullable|array',
            'detailed_requirements' => 'required|array',
            'detailed_requirements.sections' => 'nullable|array',
            'detailed_requirements.tone' => 'nullable|string|max:100',
            'detailed_requirements.businessContext' => 'nullable|string|max:2000',
            'detailed_requirements.targetAudience' => 'nullable|string|max:500',
            'detailed_requirements.contentGuidelines' => 'nullable|array',
            'detailed_requirements.stylePreferences' => 'nullable|string|max:500',
        ]);
        
        $businessName = $validated['business_name'] ?? $site->getName();
        $businessType = $validated['business_type'] ?? 'business';
        
        $pageData = $this->aiService->generatePageDetailed(
            $validated['page_type'],
            $businessName,
            $businessType,
            $validated['detailed_requirements'],
            $validated['existing_colors'] ?? null
        );
        
        return response()->json([
            'success' => true,
            'page' => $pageData,
        ]);
    }
    
    /**
     * Validate site access
     */
    private function validateSiteAccess(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));
        
        if (!$site || $site->getUserId() !== $request->user()->id) {
            return null;
        }
        
        return $site;
    }
}
