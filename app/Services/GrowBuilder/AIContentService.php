<?php

namespace App\Services\GrowBuilder;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * AI Content Service for GrowBuilder
 * Provides AI-powered content generation, SEO optimization, and suggestions
 * 
 * Supports multiple AI providers:
 * - OpenAI (gpt-3.5-turbo, gpt-4)
 * - Groq (llama-3.3-70b-versatile) - FREE
 * - Google Gemini (gemini-pro) - FREE tier
 * - Ollama (local, free)
 */
class AIContentService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private string $provider;
    
    public function __construct()
    {
        // Determine provider from config
        $this->provider = config('services.ai.provider', 'openai');
        
        // Set API key and base URL based on provider
        switch ($this->provider) {
            case 'groq':
                $this->apiKey = config('services.ai.groq_key', '');
                $this->model = config('services.ai.groq_model', 'llama-3.3-70b-versatile');
                $this->baseUrl = 'https://api.groq.com/openai/v1';
                break;
            case 'grok':
            case 'xai':
                $this->apiKey = config('services.ai.grok_key', config('services.ai.xai_key', ''));
                $this->model = config('services.ai.grok_model', 'grok-beta');
                $this->baseUrl = 'https://api.x.ai/v1';
                break;
            case 'gemini':
                $this->apiKey = config('services.ai.gemini_key', '');
                $this->model = config('services.ai.gemini_model', 'gemini-pro');
                $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
                break;
            case 'ollama':
                $this->apiKey = ''; // Ollama doesn't need API key
                $this->model = config('services.ai.ollama_model', 'llama3');
                $this->baseUrl = config('services.ai.ollama_url', 'http://localhost:11434/api');
                break;
            default: // openai
                $this->apiKey = config('services.ai.openai_key', config('services.openai.key', ''));
                $this->model = config('services.ai.openai_model', config('services.openai.model', 'gpt-3.5-turbo'));
                $this->baseUrl = config('services.ai.openai_url', 'https://api.openai.com/v1');
        }
    }
    
    /**
     * Check if AI service is configured
     */
    public function isConfigured(): bool
    {
        // Ollama doesn't need API key
        if ($this->provider === 'ollama') {
            return true;
        }
        return !empty($this->apiKey);
    }
    
    /**
     * Get current provider name
     */
    public function getProvider(): string
    {
        return $this->provider;
    }
    
    /**
     * Classify user intent using AI with conversation memory and rich context
     * This makes the assistant smarter by understanding natural language
     * 
     * Enhanced features:
     * 1. Conversation Memory - Uses recent messages for context
     * 2. Richer Context - Site sections, colors, content
     * 3. Multi-step Actions - Detects compound requests
     * 4. Clarification - Returns clarification prompts for low confidence
     * 5. Detailed Page Generation - Handles comprehensive page requirements
     */
    public function classifyIntent(string $userMessage, array $context = []): array
    {
        $systemPrompt = <<<PROMPT
You are an intelligent intent classifier for a website builder assistant. Analyze the user's message considering the conversation history and site context.

Available intents:
- create_page: User wants to create a new page (about, services, contact, pricing, faq, blog, gallery, testimonials)
- create_page_detailed: User provides detailed requirements for a page (specific sections, tone, style, content requirements) - use this when the request includes specific instructions about structure, tone, or content
- add_section: User wants to add a section to the current page (hero, about, services, features, testimonials, pricing, faq, contact, cta, team, gallery, stats, video, map, blog)
- edit_content: User wants to modify text/content in a section
- change_style: User wants to change colors, spacing, alignment, fonts, or other visual styles
- generate_content: User wants AI to generate content (testimonials, FAQs, pricing plans, etc.)
- navigation: User wants to modify the navigation/menu
- footer: User wants to modify the footer
- seo: User wants SEO help (meta descriptions, keywords)
- colors: User wants color palette suggestions
- help: User needs help or doesn't know what to do
- multi_step: User wants multiple actions (e.g., "create about page and add testimonials")
- clarify: Message is ambiguous and needs clarification
- unknown: Cannot determine intent

IMPORTANT RULES:
1. Use conversation history to understand references like "it", "that", "the page", "this section"
2. If user says "add more" or "another one", refer to previous context
3. For multi-step requests, list ALL actions in the params.actions array
4. If confidence is between 0.4-0.7, set intent to "clarify" and provide a clarification question
5. Consider the site's existing sections and pages when interpreting requests
6. Use "create_page_detailed" when the user provides specific requirements like:
   - Specific sections to include (hero, team, values, etc.)
   - Tone/style requirements (professional, friendly, corporate)
   - Content guidelines (no lorem ipsum, specific messaging)
   - Structure requirements (specific order of sections)
   - Business context (company type, target audience)

Return ONLY valid JSON in this exact format:
{
    "intent": "intent_name",
    "confidence": 0.0-1.0,
    "params": {
        "pageType": "blog|about|services|etc" (for create_page),
        "sectionType": "hero|about|etc" (for add_section),
        "styleChanges": {} (for change_style),
        "details": "any extracted details",
        "actions": [] (for multi_step - array of {intent, params} objects),
        "clarificationQuestion": "question to ask user" (for clarify intent),
        "referenceResolved": "what 'it/that/this' refers to based on context",
        "detailedRequirements": {
            "sections": ["hero", "about", "team", "values", "cta"],
            "tone": "professional|friendly|corporate|casual",
            "businessContext": "description of the business",
            "targetAudience": "who the page is for",
            "contentGuidelines": ["no lorem ipsum", "original content", etc],
            "stylePreferences": "any style requirements"
        }
    }
}
PROMPT;

        // Build rich context information
        $contextInfo = $this->buildRichContext($context);
        
        // Build conversation history
        $conversationHistory = $this->buildConversationHistory($context['conversationHistory'] ?? []);
        
        $userPrompt = "Site Context:\n{$contextInfo}\n\n{$conversationHistory}Current user message: \"{$userMessage}\"\n\nClassify the intent:";
        
        try {
            $response = $this->callAI($systemPrompt, $userPrompt);
            
            // Extract JSON from response
            if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
                $result = json_decode($matches[0], true);
                if (is_array($result) && isset($result['intent'])) {
                    // Handle medium confidence - suggest clarification
                    if ($result['confidence'] >= 0.4 && $result['confidence'] < 0.7 && $result['intent'] !== 'clarify') {
                        $result['needsClarification'] = true;
                        $result['originalIntent'] = $result['intent'];
                    }
                    return $result;
                }
            }
            
            return ['intent' => 'unknown', 'confidence' => 0, 'params' => []];
        } catch (\Exception $e) {
            Log::error('Intent classification failed', ['error' => $e->getMessage()]);
            return ['intent' => 'unknown', 'confidence' => 0, 'params' => []];
        }
    }
    
    /**
     * Build rich context information for AI classification
     */
    private function buildRichContext(array $context): string
    {
        $info = [];
        
        // Site information
        if (!empty($context['siteName'])) {
            $info[] = "Site name: {$context['siteName']}";
        }
        if (!empty($context['businessType'])) {
            $info[] = "Business type: {$context['businessType']}";
        }
        
        // Current page context
        if (!empty($context['currentPage'])) {
            $info[] = "Current page: {$context['currentPage']}";
        }
        
        // Selected section context
        if (!empty($context['selectedSection'])) {
            $info[] = "Selected section: {$context['selectedSection']}";
        }
        if (!empty($context['selectedSectionContent'])) {
            $preview = substr($context['selectedSectionContent'], 0, 200);
            $info[] = "Section content preview: {$preview}...";
        }
        
        // Existing page sections
        if (!empty($context['pageSections']) && is_array($context['pageSections'])) {
            $sections = implode(', ', $context['pageSections']);
            $info[] = "Page sections: {$sections}";
        }
        
        // Site pages
        if (!empty($context['sitePages']) && is_array($context['sitePages'])) {
            $pages = implode(', ', $context['sitePages']);
            $info[] = "Existing pages: {$pages}";
        }
        
        // Current colors
        if (!empty($context['siteColors'])) {
            $colors = is_array($context['siteColors']) 
                ? json_encode($context['siteColors']) 
                : $context['siteColors'];
            $info[] = "Site colors: {$colors}";
        }
        
        // Last action performed
        if (!empty($context['lastAction'])) {
            $info[] = "Last action: {$context['lastAction']}";
        }
        
        return empty($info) ? 'No additional context available.' : implode("\n", $info);
    }
    
    /**
     * Build conversation history for context
     */
    private function buildConversationHistory(array $history): string
    {
        if (empty($history)) {
            return '';
        }
        
        // Take last 5 messages for context (to avoid token limits)
        $recentHistory = array_slice($history, -5);
        
        $formatted = "Recent conversation:\n";
        foreach ($recentHistory as $msg) {
            $role = $msg['role'] ?? 'user';
            $content = $msg['content'] ?? '';
            // Truncate long messages
            if (strlen($content) > 150) {
                $content = substr($content, 0, 150) . '...';
            }
            $formatted .= "- {$role}: {$content}\n";
        }
        $formatted .= "\n";
        
        return $formatted;
    }

    /**
     * Smart Chat - AI-first approach to handle any user request
     * 
     * This method uses AI to:
     * 1. Understand the user's intent (no matter how they phrase it)
     * 2. Generate appropriate content/response
     * 3. Return structured data that the frontend can apply
     * 
     * The AI decides what action to take and generates the content in one call.
     */
    public function smartChat(string $userMessage, array $context = []): array
    {
        $systemPrompt = $this->buildSmartChatSystemPrompt($context);
        $userPrompt = $this->buildSmartChatUserPrompt($userMessage, $context);
        
        try {
            $response = $this->callAI($systemPrompt, $userPrompt);
            
            // Log the raw response for debugging
            Log::debug('AI raw response', ['response' => substr($response, 0, 500)]);
            
            // Remove markdown code blocks if present (```json ... ```, ``` ... ```, or `json ... `)
            $cleanResponse = $response;
            // Triple backticks with optional language
            $cleanResponse = preg_replace('/```(?:json)?\s*([\s\S]*?)\s*```/', '$1', $cleanResponse);
            // Single backticks (sometimes AI uses these)
            $cleanResponse = preg_replace('/`json\s*([\s\S]*?)\s*`/', '$1', $cleanResponse);
            $cleanResponse = preg_replace('/`([\s\S]*?)`/', '$1', $cleanResponse);
            
            // Extract JSON from response
            if (preg_match('/\{[\s\S]*\}/', $cleanResponse, $matches)) {
                $result = json_decode($matches[0], true);
                if (is_array($result) && isset($result['action'])) {
                    // Post-process to enforce intelligent rules
                    $result = $this->enforceIntelligentRules($result, $userMessage, $context);
                    
                    Log::debug('AI processed result', [
                        'action' => $result['action'],
                        'sectionType' => $result['data']['sectionType'] ?? 'none',
                        'position' => $result['data']['position'] ?? 'none'
                    ]);
                    
                    return $result;
                }
            }
            
            // If parsing failed, return as conversational response
            return [
                'action' => 'chat',
                'message' => $response,
                'data' => null
            ];
        } catch (\Exception $e) {
            Log::error('Smart chat failed', ['error' => $e->getMessage()]);
            return [
                'action' => 'error',
                'message' => 'I encountered an issue processing your request. Could you try rephrasing it?',
                'data' => null
            ];
        }
    }
    
    /**
     * Post-process AI response to enforce intelligent rules
     * PHASE 1 UPDATE: Removed forcing logic - AI now has creative freedom
     * Only enforces: JSON validation, required fields, security
     */
    private function enforceIntelligentRules(array $result, string $userMessage, array $context): array
    {
        $lowerMessage = strtolower($userMessage);
        $currentPage = strtolower($context['currentPage'] ?? '');
        $isHomePage = in_array($currentPage, ['home', 'homepage', 'index', '']);
        $pageSections = $context['pageSections'] ?? [];
        
        // Check for creativity mode
        $creativityLevel = $context['creativityLevel'] ?? 'balanced';
        $isCreativeMode = $creativityLevel === 'creative' 
            || str_contains($lowerMessage, 'surprise me')
            || str_contains($lowerMessage, 'be creative')
            || str_contains($lowerMessage, 'something different')
            || str_contains($lowerMessage, 'unconventional')
            || str_contains($lowerMessage, 'experiment');
        
        Log::debug('enforceIntelligentRules input', [
            'action' => $result['action'] ?? 'none',
            'sectionType' => $result['data']['sectionType'] ?? 'none',
            'currentPage' => $currentPage,
            'isHomePage' => $isHomePage,
            'creativityLevel' => $creativityLevel,
            'isCreativeMode' => $isCreativeMode,
            'userMessage' => $lowerMessage
        ]);
        
        // In creative mode, skip most rules - trust the AI
        if ($isCreativeMode) {
            Log::debug('Creative mode enabled - minimal enforcement');
            
            // Only ensure required fields exist
            if ($result['action'] === 'generate_content') {
                if (empty($result['data']['style'])) {
                    $result['data']['style'] = $this->getDefaultStyleForSection(
                        $result['data']['sectionType'] ?? 'about', 
                        $context
                    );
                }
                if (empty($result['data']['position'])) {
                    $result['data']['position'] = 'auto';
                }
            }
            
            return $result;
        }
        
        // BALANCED MODE: Provide smart defaults but don't override AI decisions
        if ($result['action'] === 'generate_content') {
            $sectionType = $result['data']['sectionType'] ?? '';
            
            // Smart header detection - SUGGEST but don't force
            $isHeaderRequest = str_contains($lowerMessage, 'header') 
                || str_contains($lowerMessage, 'title section') 
                || str_contains($lowerMessage, 'page title')
                || str_contains($lowerMessage, 'banner')
                || str_contains($lowerMessage, 'top section');
            
            // Only suggest page-header if AI didn't already choose appropriately
            if ($isHeaderRequest && !$isHomePage && !in_array($sectionType, ['page-header', 'hero'])) {
                Log::debug('Header request detected - suggesting page-header (not forcing)');
                
                // Suggest but don't force - AI may have good reason for different choice
                if (empty($sectionType)) {
                    $result['data']['sectionType'] = 'page-header';
                    $pageTitle = ucwords(str_replace(['-', '_'], ' ', $currentPage));
                    $result['data']['content'] = [
                        'title' => $result['data']['content']['title'] ?? $pageTitle,
                        'subtitle' => $result['data']['content']['subtitle'] ?? "Welcome to our {$pageTitle} page",
                        'backgroundImage' => '',
                    ];
                }
            }
            
            // REMOVED: Forced position overrides
            // AI can now place sections where it thinks best
            // Users can always manually reorder
            
            // Provide defaults only if missing (don't override AI choices)
            if (empty($result['data']['style'])) {
                $result['data']['style'] = $this->getDefaultStyleForSection($sectionType, $context);
            }
            
            if (empty($result['data']['position'])) {
                // Smart default based on section type, but AI can override
                $result['data']['position'] = $this->suggestPositionForSection($sectionType, $pageSections);
            }
        }
        
        Log::debug('enforceIntelligentRules output', [
            'sectionType' => $result['data']['sectionType'] ?? 'none',
            'position' => $result['data']['position'] ?? 'none',
            'hasStyle' => !empty($result['data']['style'])
        ]);
        
        return $result;
    }
    
    /**
     * Suggest a position for a section type (not forced, just a default)
     */
    private function suggestPositionForSection(string $sectionType, array $existingSections): string
    {
        // These are suggestions, not rules - AI can override
        switch ($sectionType) {
            case 'page-header':
            case 'hero':
                return 'first';
            case 'contact':
            case 'map':
                return 'last';
            default:
                return 'auto';
        }
    }
    
    /**
     * Get default style for a section type based on context
     */
    private function getDefaultStyleForSection(string $sectionType, array $context): array
    {
        $siteColors = $context['siteColors'] ?? [];
        $primary = $siteColors['primary'] ?? '#2563eb';
        
        switch ($sectionType) {
            case 'page-header':
            case 'hero':
                return [
                    'backgroundColor' => $primary,
                    'textColor' => '#ffffff',
                ];
            case 'cta':
                return [
                    'backgroundColor' => $primary,
                    'textColor' => '#ffffff',
                ];
            case 'stats':
                return [
                    'backgroundColor' => $primary,
                    'textColor' => '#ffffff',
                ];
            case 'testimonials':
                return [
                    'backgroundColor' => '#f9fafb',
                    'textColor' => '#111827',
                ];
            default:
                return [
                    'backgroundColor' => '#ffffff',
                    'textColor' => '#111827',
                ];
        }
    }
    
    /**
     * Build system prompt for smart chat
     */
    private function buildSmartChatSystemPrompt(array $context): string
    {
        $businessType = $context['businessType'] ?? 'business';
        $siteName = $context['siteName'] ?? 'the website';
        $currentPage = $context['currentPage'] ?? 'unknown';
        
        // Get industry-specific knowledge
        $industryKnowledge = $this->getIndustryKnowledge($businessType);
        
        // Build proactive suggestions based on context
        $proactiveSuggestions = $this->buildProactiveSuggestions($context);
        
        // Get existing page sections for context
        $existingSections = '';
        $sectionsList = [];
        if (!empty($context['pageSections'])) {
            $sectionsList = $context['pageSections'];
            $existingSections = "Current page sections (in order): " . implode(' → ', $context['pageSections']);
        }
        
        // Get existing site pages - CRITICAL for avoiding duplicate suggestions
        $existingPages = '';
        $pagesList = [];
        if (!empty($context['sitePages'])) {
            $pagesList = $context['sitePages'];
            $existingPages = "EXISTING SITE PAGES: " . implode(', ', $context['sitePages']);
        }
        
        // Get site colors for style matching
        $siteColors = '';
        $colorData = [];
        if (!empty($context['siteColors'])) {
            $colorData = is_array($context['siteColors']) ? $context['siteColors'] : [];
            $colors = is_array($context['siteColors']) ? json_encode($context['siteColors']) : $context['siteColors'];
            $siteColors = "Site color scheme: {$colors}";
        }
        
        // Analyze page structure
        $pageAnalysis = $this->analyzePageStructure($sectionsList, $currentPage);
        
        // Build page awareness section
        $pageAwareness = $this->buildPageAwareness($pagesList, $currentPage);
        
        // Get section schemas from template service
        $sectionSchemas = SectionTemplateService::generateAISchemaDoc();
        
        return <<<PROMPT
You are an EXPERT website builder AI assistant for "{$siteName}", a {$businessType} business. You create PROFESSIONAL, POLISHED websites through intelligent conversation.

═══════════════════════════════════════════════════════════════
CURRENT CONTEXT
═══════════════════════════════════════════════════════════════
- Site: {$siteName} ({$businessType})
- Current page: {$currentPage}
- {$existingPages}
- {$existingSections}
- {$siteColors}
- {$pageAnalysis}

{$pageAwareness}

═══════════════════════════════════════════════════════════════
{$sectionSchemas}
═══════════════════════════════════════════════════════════════
GUIDELINES (flexible, not strict rules)
═══════════════════════════════════════════════════════════════

TYPICAL SECTION ORDER (can be changed if user wants):
1. page-header or hero (usually first)
2. about/services/features (main content)
3. stats/testimonials (social proof)
4. pricing/products (if applicable)
5. faq (if applicable)
6. cta (call to action)
7. contact/map (often last)

HOWEVER: If user explicitly requests a different order, RESPECT their choice.
Examples of when to break typical order:
- "Put contact form at the top" → Do it
- "I want testimonials first" → Do it
- "Surprise me with something different" → Be creative!
- "Create an unconventional layout" → Experiment!

STYLE SUGGESTIONS (not requirements):
• Consider using site's color scheme for consistency
• Alternate light/dark backgrounds for visual rhythm
• Headers often use primary color with white text
• But feel free to suggest alternatives if appropriate

═══════════════════════════════════════════════════════════════
CONTENT QUALITY
═══════════════════════════════════════════════════════════════

PREFER:
✓ Real, specific content over placeholder text
✓ Zambian context when appropriate (K for Kwacha, +260 phones, local names)
✓ Content that matches the business type
✓ Concise but compelling copy
✓ Clear calls-to-action
✓ Professional, friendly tone

FOR STATS: Use realistic, impressive numbers (3-4 max)
FOR TESTIMONIALS: Realistic names, roles, specific quotes
FOR SERVICES: 3-6 items with benefit-focused titles

{$industryKnowledge}

{$proactiveSuggestions}

═══════════════════════════════════════════════════════════════
CREATIVITY MODE
═══════════════════════════════════════════════════════════════

When user says "surprise me", "be creative", "something different", or "unconventional":
• Ignore typical section ordering
• Try unexpected combinations
• Experiment with layouts
• Suggest unique approaches
• Be bold and innovative

═══════════════════════════════════════════════════════════════
LEARNING FROM USER FEEDBACK
═══════════════════════════════════════════════════════════════

You have access to feedback data showing what this user (and similar users) have accepted or rejected.

HOW TO USE FEEDBACK DATA:
• If acceptance rate is HIGH (>70%): Continue with similar approaches
• If acceptance rate is LOW (<50%): Try different approaches, ask more questions
• If certain content types are "preferred": Prioritize those in suggestions
• If certain content types are "avoided": Be more careful, offer alternatives
• Industry-specific insights: Apply learnings from similar businesses

ADAPTIVE BEHAVIOR:
• After a rejection: Acknowledge and try a different approach
• After multiple rejections: Ask clarifying questions
• When user retries: Generate something noticeably different
• For new users (no data): Use global best practices

═══════════════════════════════════════════════════════════════
RESPONSE FORMAT
═══════════════════════════════════════════════════════════════

Return ONLY valid JSON (no markdown, no extra text):

{
    "action": "generate_content|create_page|change_style|update_navigation|update_footer|generate_seo|chat|clarify|analyze_page",
    "message": "Friendly explanation of what you're doing and why",
    "data": {
        // For generate_content:
        "sectionType": "page-header|hero|about|services|etc",
        "content": { /* complete section content */ },
        "position": "first|last|auto|specific index",
        "style": {
            "backgroundColor": "#hex",
            "textColor": "#hex",
            "gradientFrom": "#hex (optional)",
            "gradientTo": "#hex (optional)"
        }
        
        // For change_style:
        "styleChanges": { "backgroundColor": "#...", "textColor": "#...", "textPosition": "center" },
        "sectionType": "target section type"
        
        // For create_page:
        "pageType": "about|services|contact|etc",
        "title": "Page Title",
        "sections": [{ "type": "...", "content": {...}, "style": {...} }]
        
        // For analyze_page (when user asks for feedback/improvements):
        "suggestions": [
            { "type": "add_section|improve_content|change_style|seo", "description": "what to improve", "priority": "high|medium|low" }
        ]
    }
}

═══════════════════════════════════════════════════════════════
HANDLING ANALYTICAL QUESTIONS
═══════════════════════════════════════════════════════════════

When user asks questions like:
- "Are there any improvements?"
- "What do you think of this page?"
- "What's missing?"
- "How can I make this better?"
- "Any suggestions?"

Use action: "analyze_page" and provide thoughtful analysis in the message field.
DO NOT automatically generate content - instead, LIST suggestions and let user choose.

Example response for "Any improvements for this page?":
{
    "action": "analyze_page",
    "message": "Looking at your Blog page, here are some suggestions:\n\n1. **Add a newsletter signup** - Capture readers' emails\n2. **Include author bios** - Build trust and credibility\n3. **Add social sharing buttons** - Increase reach\n4. **Consider a 'Related Posts' section** - Keep readers engaged\n\nWould you like me to implement any of these?",
    "data": {
        "suggestions": [
            { "type": "add_section", "description": "Newsletter signup form", "priority": "high" },
            { "type": "add_section", "description": "Author bio section", "priority": "medium" },
            { "type": "improve_content", "description": "Add social sharing", "priority": "medium" }
        ]
    }
}

═══════════════════════════════════════════════════════════════
IMPORTANT PRINCIPLES
═══════════════════════════════════════════════════════════════

1. Return valid JSON only
2. Avoid placeholder text when possible
3. Consider existing styles for consistency (unless user wants change)
4. RESPECT USER REQUESTS - if they want something specific, do it
5. Generate complete content (all required fields)
6. Be helpful - make smart assumptions for vague requests
7. Explain your choices in the message field
8. BE FLEXIBLE - guidelines are not laws
9. When in doubt, ask for clarification rather than assuming

REMEMBER: The user has full control. They can always manually adjust anything you suggest. Your job is to help, not restrict.
PROMPT;
    }
    
    /**
     * Analyze page structure to provide intelligent suggestions
     */
    private function analyzePageStructure(array $sections, string $currentPage): string
    {
        $analysis = [];
        
        // Check if page has a header
        $hasHeader = in_array('page-header', $sections) || in_array('hero', $sections);
        if (!$hasHeader && strtolower($currentPage) !== 'home') {
            $analysis[] = "⚠️ Page is missing a header section";
        }
        
        // Check section count
        if (count($sections) === 0) {
            $analysis[] = "📝 Page is empty - needs content";
        } elseif (count($sections) < 3) {
            $analysis[] = "📝 Page has few sections - could use more content";
        }
        
        // Check for social proof
        $hasSocialProof = in_array('testimonials', $sections) || in_array('stats', $sections);
        if (!$hasSocialProof && count($sections) > 2) {
            $analysis[] = "💡 Consider adding testimonials or stats for credibility";
        }
        
        // Check for CTA
        if (!in_array('cta', $sections) && count($sections) > 3) {
            $analysis[] = "💡 A call-to-action section could improve conversions";
        }
        
        if (empty($analysis)) {
            return "Page structure: Well organized";
        }
        
        return "Page analysis:\n" . implode("\n", $analysis);
    }
    
    /**
     * Build page awareness section to prevent duplicate page suggestions
     */
    private function buildPageAwareness(array $existingPages, string $currentPage): string
    {
        if (empty($existingPages)) {
            return '';
        }
        
        $normalizedPages = array_map('strtolower', $existingPages);
        
        return <<<AWARENESS
═══════════════════════════════════════════════════════════════
⚠️ CRITICAL: PAGE AWARENESS - READ CAREFULLY
═══════════════════════════════════════════════════════════════

The site ALREADY HAS these pages: {$this->formatPageList($existingPages)}

RULES FOR PAGE SUGGESTIONS:
1. NEVER suggest creating a page that already exists
2. If user asks about a page that exists, offer to IMPROVE it instead
3. When suggesting new pages, only suggest ones NOT in the list above
4. If user asks "what pages should I add?", exclude existing pages from suggestions

EXAMPLES:
- If "About" exists and user says "add about page" → Respond: "You already have an About page. Would you like me to improve it or add sections to it?"
- If user asks "what's missing?" → Only suggest pages NOT in the existing list
- If user wants to "create Contact page" but it exists → Offer to enhance the existing Contact page

Current page being edited: {$currentPage}
AWARENESS;
    }
    
    /**
     * Format page list for display
     */
    private function formatPageList(array $pages): string
    {
        return implode(', ', array_map(fn($p) => "'{$p}'", $pages));
    }
    
    /**
     * Get industry-specific knowledge for better content generation
     */
    private function getIndustryKnowledge(string $businessType): string
    {
        $knowledge = [
            'restaurant' => <<<KNOWLEDGE
INDUSTRY KNOWLEDGE - RESTAURANT/FOOD:
- Key sections: Menu highlights, Opening hours, Location/delivery areas, Chef's specials, Customer reviews
- Important content: Food photography descriptions, Cuisine type, Price ranges, Dietary options (vegetarian, halal)
- Zambian context: Popular dishes (nshima, village chicken, kapenta, ifisashi), local ingredients
- Best practices: Mouth-watering descriptions, clear pricing in Kwacha, delivery/takeaway info
- Stats to highlight: Years in business, Customers served, Menu items, 5-star reviews
- Common pages: Menu, About Us, Contact/Reservations, Gallery, Catering
KNOWLEDGE,
            
            'church' => <<<KNOWLEDGE
INDUSTRY KNOWLEDGE - CHURCH/MINISTRY:
- Key sections: Service times, Beliefs/doctrine, Ministries, Events calendar, Sermons
- Important content: Welcome message, Pastor's bio, Community outreach, Youth programs
- Zambian context: Denominations common in Zambia, community focus, family values
- Best practices: Warm welcoming tone, clear service schedule, easy contact for prayer requests
- Stats to highlight: Years of ministry, Members, Outreach programs, Lives touched
- Common pages: About, Services, Ministries, Events, Sermons, Contact, Give
KNOWLEDGE,
            
            'tutor' => <<<KNOWLEDGE
INDUSTRY KNOWLEDGE - TUTORING/EDUCATION:
- Key sections: Subjects offered, Tutor qualifications, Success stories, Pricing packages
- Important content: Teaching methodology, Grade levels, Exam preparation (Grade 7, 9, 12)
- Zambian context: ECZ curriculum, popular subjects (Math, Science, English), school calendar
- Best practices: Highlight results/pass rates, flexible scheduling, online/in-person options
- Stats to highlight: Students helped, Pass rate improvement, Years experience, Subjects covered
- Common pages: Subjects, About Tutors, Pricing, Success Stories, Contact, Resources
KNOWLEDGE,
            
            'healthcare' => <<<KNOWLEDGE
INDUSTRY KNOWLEDGE - HEALTHCARE:
- Key sections: Services, Doctors/specialists, Facilities, Insurance accepted, Emergency info
- Important content: Medical services list, Doctor credentials, Operating hours, Location
- Zambian context: Common health concerns, NHIMA acceptance, local health challenges
- Best practices: Trust-building content, clear emergency contacts, professional credentials
- Stats to highlight: Patients treated, Years of service, Specialists on staff, Success rates
- Common pages: Services, Our Doctors, Facilities, Insurance, Contact, Emergency
KNOWLEDGE,
            
            'beauty' => <<<KNOWLEDGE
INDUSTRY KNOWLEDGE - BEAUTY/SALON:
- Key sections: Services menu, Stylists, Gallery of work, Booking, Products
- Important content: Service descriptions with pricing, Stylist specialties, Before/after photos
- Zambian context: African hair care, braiding styles, local beauty trends
- Best practices: Visual portfolio, clear pricing in Kwacha, online booking option
- Stats to highlight: Happy clients, Years experience, Services offered, Stylists on team
- Common pages: Services, Gallery, Our Team, Book Now, Products, Contact
KNOWLEDGE,
            
            'fitness' => <<<KNOWLEDGE
INDUSTRY KNOWLEDGE - FITNESS/GYM:
- Key sections: Membership plans, Classes schedule, Trainers, Facilities, Transformation stories
- Important content: Class types, Equipment list, Personal training options, Operating hours
- Zambian context: Local fitness culture, affordable membership tiers
- Best practices: Motivational tone, clear membership pricing, trial offers
- Stats to highlight: Members, Classes per week, Trainers, Weight lost by members
- Common pages: Memberships, Classes, Trainers, Facilities, Success Stories, Contact
KNOWLEDGE,
            
            'real-estate' => <<<KNOWLEDGE
INDUSTRY KNOWLEDGE - REAL ESTATE:
- Key sections: Property listings, Services (buy/sell/rent), Agent profiles, Market insights
- Important content: Property details, Location benefits, Pricing, Virtual tours
- Zambian context: Popular areas (Lusaka, Copperbelt), property types, Kwacha pricing
- Best practices: High-quality property images, clear pricing, easy inquiry forms
- Stats to highlight: Properties sold, Years in business, Happy clients, Areas covered
- Common pages: Properties, Buy, Sell, Rent, About Us, Contact, Blog
KNOWLEDGE,
            
            'technology' => <<<KNOWLEDGE
INDUSTRY KNOWLEDGE - TECHNOLOGY/IT:
- Key sections: Services, Portfolio, Technologies used, Team, Case studies
- Important content: Technical capabilities, Project examples, Client logos, Process
- Zambian context: Local tech ecosystem, digital transformation needs
- Best practices: Showcase expertise, clear service descriptions, portfolio highlights
- Stats to highlight: Projects completed, Clients served, Years experience, Technologies mastered
- Common pages: Services, Portfolio, About, Team, Blog, Contact
KNOWLEDGE,
        ];
        
        return $knowledge[$businessType] ?? <<<KNOWLEDGE
INDUSTRY KNOWLEDGE - GENERAL BUSINESS:
- Key sections: Services/Products, About Us, Testimonials, Contact
- Important content: Clear value proposition, Service descriptions, Team info
- Zambian context: Local business practices, Kwacha pricing, +260 phone format
- Best practices: Professional tone, clear CTAs, easy contact options
- Stats to highlight: Years in business, Clients served, Projects completed, Team size
- Common pages: Home, About, Services, Contact, Testimonials
KNOWLEDGE;
    }
    
    /**
     * Build proactive suggestions based on site context
     */
    private function buildProactiveSuggestions(array $context): string
    {
        $suggestions = [];
        
        // Check for missing essential pages
        $sitePages = $context['sitePages'] ?? [];
        $essentialPages = ['about', 'contact', 'services'];
        foreach ($essentialPages as $page) {
            $hasPage = false;
            foreach ($sitePages as $sitePage) {
                if (stripos($sitePage, $page) !== false) {
                    $hasPage = true;
                    break;
                }
            }
            if (!$hasPage) {
                $suggestions[] = "Missing '{$page}' page - consider creating one";
            }
        }
        
        // Check for empty or minimal sections
        $pageSections = $context['pageSections'] ?? [];
        if (count($pageSections) < 3) {
            $suggestions[] = "Page has few sections - consider adding more content sections";
        }
        
        // Check for missing testimonials (important for trust)
        if (!in_array('testimonials', $pageSections)) {
            $suggestions[] = "No testimonials section - adding social proof can increase conversions";
        }
        
        // Check for missing CTA
        if (!in_array('cta', $pageSections)) {
            $suggestions[] = "No call-to-action section - consider adding one to drive conversions";
        }
        
        // Check color contrast issues
        $colors = $context['siteColors'] ?? [];
        if (!empty($colors)) {
            // Basic contrast check
            $bgColor = $colors['background'] ?? '#ffffff';
            $textColor = $colors['text'] ?? '#000000';
            if ($bgColor === $textColor) {
                $suggestions[] = "Text and background colors are the same - this will make content unreadable";
            }
        }
        
        if (empty($suggestions)) {
            return "PROACTIVE MODE: Site looks well-structured. Focus on user's specific requests.";
        }
        
        $suggestionList = implode("\n- ", $suggestions);
        return "PROACTIVE SUGGESTIONS (mention these if relevant to user's request):\n- {$suggestionList}\n\nIf the user asks for general help or says \"what should I do\", share these suggestions.";
    }
    
    /**
     * Build user prompt for smart chat with enhanced context
     */
    private function buildSmartChatUserPrompt(string $userMessage, array $context): string
    {
        $contextInfo = $this->buildRichContext($context);
        $conversationHistory = $this->buildConversationHistory($context['conversationHistory'] ?? []);
        $sectionAnalysis = $this->buildSectionContentAnalysis($context);
        $visualContext = $this->buildVisualContext($context);
        $feedbackContext = $this->buildFeedbackContext($context);
        
        return <<<PROMPT
CURRENT CONTEXT:
{$contextInfo}

{$sectionAnalysis}

{$visualContext}

{$feedbackContext}

{$conversationHistory}

USER REQUEST: "{$userMessage}"

Analyze the request considering ALL the context above. If improving existing content, keep what works and enhance what doesn't. Remember to return ONLY valid JSON.
PROMPT;
    }
    
    /**
     * Build section content analysis for AI to understand existing content
     */
    private function buildSectionContentAnalysis(array $context): string
    {
        $analysis = [];
        
        // Analyze selected section content
        if (!empty($context['selectedSectionContent'])) {
            $content = $context['selectedSectionContent'];
            $sectionType = $context['selectedSection'] ?? 'unknown';
            
            // Check if content is empty or minimal
            $contentLength = strlen(is_string($content) ? $content : json_encode($content));
            $isEmpty = $contentLength < 50;
            $isMinimal = $contentLength < 200;
            
            $analysis[] = "SELECTED SECTION ({$sectionType}):";
            if ($isEmpty) {
                $analysis[] = "- Status: EMPTY or nearly empty - needs content generation";
            } elseif ($isMinimal) {
                $analysis[] = "- Status: MINIMAL content - could be expanded";
            } else {
                $analysis[] = "- Status: Has content - can be improved or modified";
            }
            
            // Include actual content preview for AI to analyze
            $preview = is_string($content) ? $content : json_encode($content);
            $preview = substr($preview, 0, 500);
            $analysis[] = "- Current content: {$preview}";
        }
        
        // Analyze all page sections
        if (!empty($context['allSectionsContent']) && is_array($context['allSectionsContent'])) {
            $analysis[] = "\nPAGE SECTIONS OVERVIEW:";
            foreach ($context['allSectionsContent'] as $section) {
                $type = $section['type'] ?? 'unknown';
                $hasContent = !empty($section['content']);
                $contentPreview = '';
                
                if ($hasContent) {
                    $contentData = $section['content'];
                    // Extract key content for preview
                    if (isset($contentData['title'])) {
                        $contentPreview = "Title: \"{$contentData['title']}\"";
                    }
                    if (isset($contentData['items']) && is_array($contentData['items'])) {
                        $itemCount = count($contentData['items']);
                        $contentPreview .= " ({$itemCount} items)";
                    }
                }
                
                $status = $hasContent ? "✓ Has content" : "✗ Empty";
                $analysis[] = "- {$type}: {$status}" . ($contentPreview ? " - {$contentPreview}" : "");
            }
        }
        
        if (empty($analysis)) {
            return "SECTION ANALYSIS: No section content available for analysis.";
        }
        
        return "SECTION CONTENT ANALYSIS:\n" . implode("\n", $analysis);
    }
    
    /**
     * Build visual context for style-aware suggestions
     */
    private function buildVisualContext(array $context): string
    {
        $visual = [];
        
        // Current color scheme
        if (!empty($context['siteColors']) && is_array($context['siteColors'])) {
            $colors = $context['siteColors'];
            $visual[] = "COLOR SCHEME:";
            foreach ($colors as $name => $value) {
                $visual[] = "- {$name}: {$value}";
            }
            
            // Analyze color harmony
            $primary = $colors['primary'] ?? null;
            $background = $colors['background'] ?? '#ffffff';
            if ($primary && $background) {
                $visual[] = "- Contrast: " . ($this->isGoodContrast($primary, $background) ? "Good" : "May need improvement");
            }
        }
        
        // Current section styles
        if (!empty($context['selectedSectionStyle']) && is_array($context['selectedSectionStyle'])) {
            $style = $context['selectedSectionStyle'];
            $visual[] = "\nSELECTED SECTION STYLE:";
            if (isset($style['backgroundColor'])) {
                $visual[] = "- Background: {$style['backgroundColor']}";
            }
            if (isset($style['textColor'])) {
                $visual[] = "- Text color: {$style['textColor']}";
            }
            if (isset($style['textPosition'])) {
                $visual[] = "- Text alignment: {$style['textPosition']}";
            }
            if (isset($style['paddingY'])) {
                $visual[] = "- Vertical padding: {$style['paddingY']}px";
            }
        }
        
        if (empty($visual)) {
            return "VISUAL CONTEXT: No style information available.";
        }
        
        return implode("\n", $visual);
    }
    
    /**
     * Build feedback context from user's past interactions
     */
    private function buildFeedbackContext(array $context): string
    {
        $feedback = [];
        
        // Get AI insights from database (global learning across all sites)
        if (!empty($context['siteId'])) {
            try {
                $businessType = $context['businessType'] ?? null;
                $aiInsights = \App\Infrastructure\GrowBuilder\Models\AIFeedback::getInsightsForAI(
                    (int) $context['siteId'],
                    $businessType
                );
                if (!empty($aiInsights) && $aiInsights !== "No feedback data yet - this is a new site.") {
                    $feedback[] = $aiInsights;
                }
            } catch (\Exception $e) {
                // Silently fail if table doesn't exist yet
                \Illuminate\Support\Facades\Log::debug('AI feedback table not available', ['error' => $e->getMessage()]);
            }
        }
        
        // Also use aiInsights passed from frontend (if available)
        if (!empty($context['aiInsights'])) {
            $insights = $context['aiInsights'];
            if (is_string($insights)) {
                $feedback[] = $insights;
            } elseif (is_array($insights)) {
                // Format array insights
                if (!empty($insights['acceptance_rate'])) {
                    $feedback[] = "Session acceptance rate: {$insights['acceptance_rate']}%";
                }
                if (!empty($insights['preferred_actions'])) {
                    $preferred = implode(', ', $insights['preferred_actions']);
                    $feedback[] = "Session preferred actions: {$preferred}";
                }
            }
        }
        
        // Track applied vs rejected suggestions from session history
        if (!empty($context['feedbackHistory']) && is_array($context['feedbackHistory'])) {
            $applied = 0;
            $rejected = 0;
            $preferredTypes = [];
            
            foreach ($context['feedbackHistory'] as $item) {
                if ($item['applied'] ?? false) {
                    $applied++;
                    $type = $item['type'] ?? 'unknown';
                    $preferredTypes[$type] = ($preferredTypes[$type] ?? 0) + 1;
                } else {
                    $rejected++;
                }
            }
            
            if ($applied > 0 || $rejected > 0) {
                $feedback[] = "CURRENT SESSION FEEDBACK:";
                $feedback[] = "- Suggestions applied: {$applied}";
                $feedback[] = "- Suggestions rejected: {$rejected}";
                
                if (!empty($preferredTypes)) {
                    arsort($preferredTypes);
                    $topTypes = array_slice(array_keys($preferredTypes), 0, 3);
                    $feedback[] = "- Preferred content types: " . implode(", ", $topTypes);
                }
                
                // Calculate acceptance rate
                $total = $applied + $rejected;
                if ($total > 0) {
                    $rate = round(($applied / $total) * 100);
                    $feedback[] = "- Session acceptance rate: {$rate}%";
                    
                    if ($rate < 50) {
                        $feedback[] = "- Note: Low acceptance rate - be more careful with suggestions";
                    }
                }
            }
        }
        
        // Recent preferences
        if (!empty($context['userPreferences'])) {
            $prefs = $context['userPreferences'];
            $feedback[] = "\nUSER PREFERENCES:";
            if (isset($prefs['preferredTone'])) {
                $feedback[] = "- Preferred tone: {$prefs['preferredTone']}";
            }
            if (isset($prefs['preferredStyle'])) {
                $feedback[] = "- Preferred style: {$prefs['preferredStyle']}";
            }
        }
        
        if (empty($feedback)) {
            return "";
        }
        
        return implode("\n", $feedback);
    }
    
    /**
     * Check if two colors have good contrast
     */
    private function isGoodContrast(string $color1, string $color2): bool
    {
        // Simple luminance-based contrast check
        $lum1 = $this->getRelativeLuminance($color1);
        $lum2 = $this->getRelativeLuminance($color2);
        
        $lighter = max($lum1, $lum2);
        $darker = min($lum1, $lum2);
        
        $ratio = ($lighter + 0.05) / ($darker + 0.05);
        
        return $ratio >= 4.5; // WCAG AA standard
    }
    
    /**
     * Calculate relative luminance of a color
     */
    private function getRelativeLuminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        
        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);
        
        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Generate content for a section based on business context
     */
    public function generateSectionContent(
        string $sectionType,
        string $businessName,
        string $businessType,
        ?string $businessDescription = null,
        ?string $tone = 'professional',
        ?string $language = 'en'
    ): array {
        $prompts = $this->getSectionPrompt($sectionType, $businessName, $businessType, $businessDescription, $tone);
        
        try {
            $response = $this->callAI($prompts['system'], $prompts['user']);
            return $this->parseSectionResponse($sectionType, $response);
        } catch (\Exception $e) {
            Log::error('AI content generation failed', [
                'section' => $sectionType,
                'error' => $e->getMessage(),
            ]);
            return $this->getFallbackContent($sectionType, $businessName);
        }
    }
    
    /**
     * Generate SEO meta title
     */
    public function generateMetaTitle(string $pageTitle, string $pageContent, string $businessName): string
    {
        $systemPrompt = <<<PROMPT
You are an expert SEO copywriter. Generate a compelling meta title (page title for search results) that:
- Is between 30-60 characters (optimal for search results)
- Includes the primary keyword near the beginning
- Includes the business/brand name
- Is compelling and click-worthy
- Uses separator like | or - between parts
- Avoids keyword stuffing

Return ONLY the meta title text, no quotes or explanation.
PROMPT;

        $userPrompt = <<<PROMPT
Generate a meta title for:
- Business: {$businessName}
- Page: {$pageTitle}
- Content summary: {$pageContent}

Write a compelling 30-60 character meta title for search results.
PROMPT;
        
        try {
            $response = $this->callAI($systemPrompt, $userPrompt);
            $title = trim($response, " \n\r\t\v\0\"'");
            
            // Ensure it's not too short
            if (strlen($title) < 20) {
                $title = "{$pageTitle} | {$businessName}";
            }
            
            return substr($title, 0, 70);
        } catch (\Exception $e) {
            // Fallback: Page Title | Business Name
            $fallback = "{$pageTitle} | {$businessName}";
            return substr($fallback, 0, 70);
        }
    }
    
    /**
     * Generate SEO meta description
     */
    public function generateMetaDescription(string $pageTitle, string $pageContent, string $businessName): string
    {
        $systemPrompt = <<<PROMPT
You are an expert SEO copywriter. Generate a compelling meta description that:
- Is between 120-155 characters (optimal for search results)
- Includes the business name naturally
- Contains a clear value proposition or benefit
- Includes a subtle call-to-action (e.g., "Learn more", "Discover", "Get started")
- Uses active voice and engaging language
- Avoids generic phrases like "Welcome to" or "We are a company that"

Return ONLY the meta description text, no quotes or explanation.
PROMPT;

        $userPrompt = <<<PROMPT
Generate a meta description for:
- Business: {$businessName}
- Page: {$pageTitle}
- Content summary: {$pageContent}

Write a compelling 120-155 character meta description that would make someone want to click.
PROMPT;
        
        try {
            $response = $this->callAI($systemPrompt, $userPrompt);
            $description = trim($response, " \n\r\t\v\0\"'");
            
            // Ensure it's not too short (minimum 80 chars for good SEO)
            if (strlen($description) < 80) {
                $description = "{$businessName} - {$pageTitle}. Discover quality services and solutions tailored to your needs. Contact us today to learn more.";
            }
            
            return substr($description, 0, 160);
        } catch (\Exception $e) {
            return "{$businessName} offers professional {$pageTitle} services. Discover quality solutions tailored to your needs. Contact us today to get started.";
        }
    }
    
    /**
     * Generate SEO keywords
     */
    public function generateKeywords(string $businessName, string $businessType, string $pageContent): array
    {
        $systemPrompt = <<<PROMPT
You are an SEO expert specializing in keyword research. Generate relevant, searchable keywords that:
- Include the business name and type
- Mix short-tail and long-tail keywords
- Include location-based keywords if relevant (Zambia, Africa)
- Focus on user intent (what people search for)
- Avoid generic terms that are too competitive

Return ONLY a JSON array of 8-12 keyword strings, no explanation.
Example: ["keyword 1", "keyword 2", "keyword 3"]
PROMPT;

        $userPrompt = <<<PROMPT
Generate SEO keywords for:
- Business: {$businessName}
- Type: {$businessType}
- Content: {$pageContent}

Return a JSON array of 8-12 relevant keywords.
PROMPT;
        
        try {
            $response = $this->callAI($systemPrompt, $userPrompt);
            // Clean up response - remove markdown code blocks if present
            $response = preg_replace('/```json?\s*/', '', $response);
            $response = preg_replace('/```\s*/', '', $response);
            $response = trim($response);
            
            $keywords = json_decode($response, true);
            
            if (is_array($keywords) && count($keywords) >= 3) {
                return array_slice($keywords, 0, 12);
            }
            
            // Fallback if parsing fails
            return [$businessName, $businessType, "{$businessName} {$businessType}", "{$businessType} services", "{$businessType} Zambia", "professional {$businessType}"];
        } catch (\Exception $e) {
            return [$businessName, $businessType, "{$businessName} services", "professional {$businessType}", "{$businessType} Zambia"];
        }
    }
    
    /**
     * Suggest color palette based on business type
     */
    public function suggestColorPalette(string $businessType, ?string $mood = 'professional'): array
    {
        $cacheKey = "color_palette_{$businessType}_{$mood}";
        
        return Cache::remember($cacheKey, 86400, function () use ($businessType, $mood) {
            $systemPrompt = "You are a professional web designer. Suggest a color palette for a website. Return as JSON with keys: primary, secondary, accent, background, text. Use hex color codes.";
            $userPrompt = "Suggest a {$mood} color palette for a {$businessType} business website.";
            
            try {
                $response = $this->callAI($systemPrompt, $userPrompt);
                $colors = json_decode($response, true);
                
                if (is_array($colors) && isset($colors['primary'])) {
                    return $colors;
                }
            } catch (\Exception $e) {
                // Fall through to default
            }
            
            return $this->getDefaultColorPalette($businessType);
        });
    }
    
    /**
     * Improve/rewrite text content
     */
    public function improveText(string $text, string $style = 'professional', ?string $instruction = null): string
    {
        $systemPrompt = "You are a professional copywriter. Improve the given text to be more engaging and {$style}. Keep the same meaning but make it better.";
        $userPrompt = $instruction 
            ? "Improve this text ({$instruction}): {$text}"
            : "Improve this text: {$text}";
        
        try {
            return $this->callAI($systemPrompt, $userPrompt);
        } catch (\Exception $e) {
            return $text;
        }
    }
    
    /**
     * Translate content to local languages
     */
    public function translateContent(string $text, string $targetLanguage): string
    {
        $languages = [
            'bem' => 'Bemba',
            'nya' => 'Nyanja/Chewa',
            'ton' => 'Tonga',
            'loz' => 'Lozi',
            'sw' => 'Swahili',
            'fr' => 'French',
        ];
        
        $langName = $languages[$targetLanguage] ?? $targetLanguage;
        
        $systemPrompt = "You are a professional translator fluent in {$langName}. Translate the text accurately while maintaining the tone and meaning.";
        $userPrompt = "Translate to {$langName}: {$text}";
        
        try {
            return $this->callAI($systemPrompt, $userPrompt);
        } catch (\Exception $e) {
            return $text;
        }
    }
    
    /**
     * Suggest images based on content
     */
    public function suggestImageKeywords(string $sectionType, string $content, string $businessType): array
    {
        $systemPrompt = "You are a visual content expert. Suggest search keywords for finding relevant stock photos. Return as JSON array of 5 search terms.";
        $userPrompt = "Suggest image search keywords for a {$sectionType} section of a {$businessType} website. Content: {$content}";
        
        try {
            $response = $this->callAI($systemPrompt, $userPrompt);
            $keywords = json_decode($response, true);
            return is_array($keywords) ? $keywords : [$businessType, $sectionType];
        } catch (\Exception $e) {
            return [$businessType, $sectionType, 'business', 'professional'];
        }
    }
    
    /**
     * Generate testimonial content (for demo purposes)
     */
    public function generateTestimonials(string $businessName, string $businessType, int $count = 3): array
    {
        $systemPrompt = "You are a marketing expert. Generate realistic customer testimonials. Return as JSON array with objects containing: name, role, company, content, rating (1-5).";
        $userPrompt = "Generate {$count} realistic testimonials for {$businessName}, a {$businessType} business. Make them sound authentic and specific.";
        
        try {
            $response = $this->callAI($systemPrompt, $userPrompt);
            $testimonials = json_decode($response, true);
            return is_array($testimonials) ? $testimonials : [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Generate FAQ content
     */
    public function generateFAQs(string $businessName, string $businessType, int $count = 5): array
    {
        $systemPrompt = "You are a customer service expert. Generate common FAQs for a business. Return as JSON array with objects containing: question, answer.";
        $userPrompt = "Generate {$count} FAQs for {$businessName}, a {$businessType} business. Include questions customers commonly ask.";
        
        try {
            $response = $this->callAI($systemPrompt, $userPrompt);
            $faqs = json_decode($response, true);
            return is_array($faqs) ? $faqs : [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Generate a complete page with AI-powered content based on business type
     */
    public function generatePage(
        string $pageType,
        string $businessName,
        string $businessType,
        ?string $businessDescription = null,
        ?array $existingColors = null
    ): array {
        $systemPrompt = $this->getPageGenerationPrompt($pageType, $businessType);
        $userPrompt = $this->buildPageUserPrompt($pageType, $businessName, $businessType, $businessDescription);
        
        try {
            $response = $this->callAI($systemPrompt, $userPrompt);
            $pageData = $this->parsePageResponse($response);
            
            // Apply business-appropriate styling
            $pageData = $this->applyBusinessStyling($pageData, $businessType, $existingColors);
            
            return $pageData;
        } catch (\Exception $e) {
            Log::error('AI page generation failed', [
                'pageType' => $pageType,
                'error' => $e->getMessage(),
            ]);
            return $this->getFallbackPage($pageType, $businessName, $businessType);
        }
    }
    
    /**
     * Generate a page with detailed/comprehensive requirements
     * Handles complex prompts with specific sections, tone, style, and content guidelines
     */
    public function generatePageDetailed(
        string $pageType,
        string $businessName,
        string $businessType,
        array $detailedRequirements,
        ?array $existingColors = null
    ): array {
        // Build a comprehensive system prompt based on requirements
        $systemPrompt = $this->buildDetailedSystemPrompt($detailedRequirements);
        
        // Build user prompt with all the specific requirements
        $userPrompt = $this->buildDetailedUserPrompt($pageType, $businessName, $businessType, $detailedRequirements);
        
        try {
            $response = $this->callAI($systemPrompt, $userPrompt);
            $pageData = $this->parsePageResponse($response);
            
            // Apply styling based on requirements or defaults
            $pageData = $this->applyBusinessStyling($pageData, $businessType, $existingColors);
            
            return $pageData;
        } catch (\Exception $e) {
            Log::error('AI detailed page generation failed', [
                'pageType' => $pageType,
                'error' => $e->getMessage(),
            ]);
            return $this->getFallbackPage($pageType, $businessName, $businessType);
        }
    }
    
    /**
     * Build system prompt for detailed page generation
     */
    private function buildDetailedSystemPrompt(array $requirements): string
    {
        $tone = $requirements['tone'] ?? 'professional';
        $contentGuidelines = $requirements['contentGuidelines'] ?? [];
        
        $guidelinesText = '';
        if (!empty($contentGuidelines)) {
            $guidelinesText = "\n\nContent Guidelines:\n- " . implode("\n- ", $contentGuidelines);
        }
        
        return <<<PROMPT
You are an expert web designer and copywriter creating a professional webpage.

Tone & Style: {$tone}, credible, and client-focused.
{$guidelinesText}

CRITICAL REQUIREMENTS:
1. All content must be original - NO "Lorem ipsum" or filler text
2. Use semantic section types that match our builder: hero, about, services, features, testimonials, pricing, faq, contact, cta, team, gallery, stats
3. Content should be specific, detailed, and realistic
4. Include proper heading hierarchy in content
5. Make content suitable for the target audience

Return ONLY valid JSON in this exact format:
{
    "title": "Page Title",
    "subtitle": "Page subtitle or tagline",
    "sections": [
        {
            "type": "section_type",
            "content": {
                "title": "Section Title",
                "subtitle": "Optional subtitle",
                "description": "Main content text",
                "items": [...] // for lists, features, team members, etc.
            },
            "style": {
                "backgroundColor": "#ffffff",
                "textColor": "#111827"
            }
        }
    ]
}
PROMPT;
    }
    
    /**
     * Build user prompt for detailed page generation
     */
    private function buildDetailedUserPrompt(
        string $pageType,
        string $businessName,
        string $businessType,
        array $requirements
    ): string {
        $sections = $requirements['sections'] ?? [];
        $businessContext = $requirements['businessContext'] ?? '';
        $targetAudience = $requirements['targetAudience'] ?? '';
        $stylePreferences = $requirements['stylePreferences'] ?? '';
        
        $sectionsText = '';
        if (!empty($sections)) {
            $sectionsText = "\n\nRequired Sections (in order):\n";
            foreach ($sections as $i => $section) {
                $num = $i + 1;
                $sectionsText .= "{$num}. {$section}\n";
            }
        }
        
        $contextText = $businessContext ? "\n\nBusiness Context: {$businessContext}" : '';
        $audienceText = $targetAudience ? "\n\nTarget Audience: {$targetAudience}" : '';
        $styleText = $stylePreferences ? "\n\nStyle Preferences: {$stylePreferences}" : '';
        
        return <<<PROMPT
Create a complete, professional "{$pageType}" page for "{$businessName}".

Business Type: {$businessType}
{$contextText}
{$audienceText}
{$sectionsText}
{$styleText}

Generate comprehensive, original content for each section. Make it specific to this business type and suitable for the target audience. Include realistic details, not generic placeholders.

For team sections, use realistic professional names and roles.
For testimonials, create authentic-sounding reviews with specific details.
For services/features, be specific to the {$businessType} industry.
For CTAs, make them compelling and action-oriented.
PROMPT;
    }
    
    /**
     * Get the system prompt for page generation based on page type
     */
    private function getPageGenerationPrompt(string $pageType, string $businessType): string
    {
        $basePrompt = "You are an expert web designer and copywriter specializing in {$businessType} businesses. Generate compelling, professional website content. Return ONLY valid JSON.";
        
        $pagePrompts = [
            'about' => "{$basePrompt} Create an About page with sections. Return JSON: {\"title\": \"string\", \"subtitle\": \"string\", \"sections\": [{\"type\": \"about|features|stats|team|cta\", \"content\": {...}}]}",
            'services' => "{$basePrompt} Create a Services page showcasing offerings. Return JSON: {\"title\": \"string\", \"subtitle\": \"string\", \"sections\": [{\"type\": \"services|features|pricing|cta\", \"content\": {...}}]}",
            'contact' => "{$basePrompt} Create a Contact page. Return JSON: {\"title\": \"string\", \"subtitle\": \"string\", \"sections\": [{\"type\": \"contact|features|map|cta\", \"content\": {...}}]}",
            'pricing' => "{$basePrompt} Create a Pricing page with plans. Return JSON: {\"title\": \"string\", \"subtitle\": \"string\", \"sections\": [{\"type\": \"pricing|features|faq|cta\", \"content\": {...}}]}",
            'faq' => "{$basePrompt} Create an FAQ page. Return JSON: {\"title\": \"string\", \"subtitle\": \"string\", \"sections\": [{\"type\": \"faq|cta\", \"content\": {...}}]}",
            'testimonials' => "{$basePrompt} Create a Testimonials page. Return JSON: {\"title\": \"string\", \"subtitle\": \"string\", \"sections\": [{\"type\": \"testimonials|stats|cta\", \"content\": {...}}]}",
            'gallery' => "{$basePrompt} Create a Gallery/Portfolio page. Return JSON: {\"title\": \"string\", \"subtitle\": \"string\", \"sections\": [{\"type\": \"gallery|testimonials|cta\", \"content\": {...}}]}",
            'blog' => "{$basePrompt} Create a Blog page with article previews. Return JSON: {\"title\": \"string\", \"subtitle\": \"string\", \"sections\": [{\"type\": \"blog|cta\", \"content\": {...}}]}",
        ];
        
        return $pagePrompts[$pageType] ?? $basePrompt;
    }
    
    /**
     * Build user prompt for page generation
     */
    private function buildPageUserPrompt(string $pageType, string $businessName, string $businessType, ?string $description): string
    {
        $context = $description ? " Business description: {$description}." : "";
        
        $prompts = [
            'about' => "Create an About page for '{$businessName}', a {$businessType} business in Zambia.{$context} Include: 1) Page header with compelling title, 2) About section with company story (2-3 paragraphs), 3) Core values/features (4 items), 4) Stats section (4 impressive metrics), 5) Team section (3 members with Zambian names), 6) CTA section. Make content specific to {$businessType} industry.",
            
            'services' => "Create a Services page for '{$businessName}', a {$businessType} business in Zambia.{$context} Include: 1) Page header, 2) Services section with 4-6 specific services for {$businessType}, 3) Features/benefits section (4 items), 4) CTA section. Use industry-specific terminology.",
            
            'contact' => "Create a Contact page for '{$businessName}', a {$businessType} business in Zambia.{$context} Include: 1) Page header, 2) Contact form section with Lusaka address and Zambian phone format (+260), 3) Features section (4 reasons to contact), 4) Map placeholder. Make it welcoming and professional.",
            
            'pricing' => "Create a Pricing page for '{$businessName}', a {$businessType} business in Zambia.{$context} Include: 1) Page header, 2) Pricing section with 3 plans (use Kwacha currency K), 3) Features section (what's included), 4) FAQ section (4 pricing questions), 5) CTA. Make pricing realistic for Zambian market.",
            
            'faq' => "Create an FAQ page for '{$businessName}', a {$businessType} business in Zambia.{$context} Include: 1) Page header, 2) FAQ section with 8-10 questions specific to {$businessType} industry, 3) CTA section. Questions should address common customer concerns.",
            
            'testimonials' => "Create a Testimonials page for '{$businessName}', a {$businessType} business in Zambia.{$context} Include: 1) Page header, 2) About section (brief intro), 3) Testimonials section with 4-6 reviews from customers with Zambian names, 4) Stats section, 5) CTA. Make testimonials specific to {$businessType} services.",
            
            'gallery' => "Create a Gallery/Portfolio page for '{$businessName}', a {$businessType} business in Zambia.{$context} Include: 1) Page header, 2) About section (portfolio intro), 3) Gallery placeholder section, 4) Testimonials (2-3), 5) CTA. Focus on showcasing work quality.",
            
            'blog' => "Create a Blog page for '{$businessName}', a {$businessType} business in Zambia.{$context} Include: 1) Page header with title 'Our Blog' and subtitle about latest news/insights, 2) Blog section with 3 sample article previews relevant to {$businessType} (title, excerpt, date), 3) CTA section encouraging newsletter signup. Make article topics relevant to {$businessType} industry.",
        ];
        
        return $prompts[$pageType] ?? "Create a {$pageType} page for '{$businessName}', a {$businessType} business.{$context}";
    }
    
    /**
     * Parse AI response for page content
     */
    private function parsePageResponse(string $response): array
    {
        try {
            // Log the raw response for debugging
            Log::debug('AI Page Response', ['response' => substr($response, 0, 500)]);
            
            // Extract JSON from response
            $jsonMatch = preg_match('/\{[\s\S]*\}/', $response, $matches);
            if ($jsonMatch) {
                $data = json_decode($matches[0], true);
                if (is_array($data)) {
                    Log::debug('Parsed page data', [
                        'has_sections' => isset($data['sections']),
                        'sections_count' => count($data['sections'] ?? []),
                        'keys' => array_keys($data),
                    ]);
                    
                    if (isset($data['sections'])) {
                        return $data;
                    }
                }
            }
            
            Log::warning('Failed to parse AI page response', ['response_length' => strlen($response)]);
            return ['title' => 'Page', 'subtitle' => '', 'sections' => []];
        } catch (\Exception $e) {
            Log::error('Error parsing page response', ['error' => $e->getMessage()]);
            return ['title' => 'Page', 'subtitle' => '', 'sections' => []];
        }
    }
    
    /**
     * Apply business-appropriate styling to page sections
     */
    private function applyBusinessStyling(array $pageData, string $businessType, ?array $existingColors = null): array
    {
        $colors = $existingColors ?? $this->getBusinessColors($businessType);
        
        // Add page header section if not present
        if (!empty($pageData['sections'])) {
            array_unshift($pageData['sections'], [
                'type' => 'page-header',
                'content' => [
                    'title' => $pageData['title'] ?? 'Page',
                    'subtitle' => $pageData['subtitle'] ?? '',
                    'backgroundColor' => $colors['dark'],
                    'textColor' => '#ffffff',
                    'textPosition' => 'center',
                ],
                'style' => ['minHeight' => 220],
            ]);
        }
        
        // Apply alternating backgrounds and styling
        $lightBg = true;
        foreach ($pageData['sections'] as $i => &$section) {
            if ($i === 0) continue; // Skip header
            
            if (!isset($section['style'])) {
                $section['style'] = [];
            }
            
            // Alternate backgrounds
            if ($section['type'] === 'cta') {
                $section['style']['backgroundColor'] = $colors['primary'];
            } else {
                $section['style']['backgroundColor'] = $lightBg ? '#ffffff' : '#f8fafc';
                $lightBg = !$lightBg;
            }
        }
        
        return $pageData;
    }
    
    /**
     * Get business-appropriate colors
     */
    private function getBusinessColors(string $businessType): array
    {
        $colorSchemes = [
            'restaurant' => ['primary' => '#dc2626', 'dark' => '#7f1d1d', 'accent' => '#fbbf24'],
            'church' => ['primary' => '#7c3aed', 'dark' => '#4c1d95', 'accent' => '#fbbf24'],
            'tutor' => ['primary' => '#2563eb', 'dark' => '#1e3a8a', 'accent' => '#10b981'],
            'healthcare' => ['primary' => '#0891b2', 'dark' => '#164e63', 'accent' => '#10b981'],
            'beauty' => ['primary' => '#db2777', 'dark' => '#831843', 'accent' => '#f472b6'],
            'fitness' => ['primary' => '#ea580c', 'dark' => '#7c2d12', 'accent' => '#22c55e'],
            'real-estate' => ['primary' => '#0d9488', 'dark' => '#134e4a', 'accent' => '#fbbf24'],
            'technology' => ['primary' => '#3b82f6', 'dark' => '#1e3a8a', 'accent' => '#8b5cf6'],
            'agriculture' => ['primary' => '#16a34a', 'dark' => '#14532d', 'accent' => '#fbbf24'],
            'retail' => ['primary' => '#f59e0b', 'dark' => '#78350f', 'accent' => '#3b82f6'],
            'services' => ['primary' => '#1e40af', 'dark' => '#0f172a', 'accent' => '#10b981'],
            'default' => ['primary' => '#1e40af', 'dark' => '#0f172a', 'accent' => '#10b981'],
        ];
        
        return $colorSchemes[$businessType] ?? $colorSchemes['default'];
    }
    
    /**
     * Get fallback page when AI fails
     */
    private function getFallbackPage(string $pageType, string $businessName, string $businessType): array
    {
        $colors = $this->getBusinessColors($businessType);
        
        $fallbacks = [
            'about' => [
                'title' => "About {$businessName}",
                'subtitle' => 'Our story, mission, and the team behind our success',
                'sections' => [
                    ['type' => 'page-header', 'content' => ['title' => "About {$businessName}", 'subtitle' => 'Our story, mission, and values', 'backgroundColor' => $colors['dark'], 'textColor' => '#ffffff', 'textPosition' => 'center'], 'style' => ['minHeight' => 220]],
                    ['type' => 'about', 'content' => ['title' => 'Our Story', 'description' => "Welcome to {$businessName}. We are dedicated to providing exceptional {$businessType} services to our community. With years of experience and a commitment to excellence, we strive to exceed expectations in everything we do."], 'style' => ['backgroundColor' => '#ffffff']],
                    ['type' => 'features', 'content' => ['title' => 'Our Values', 'items' => [['title' => 'Excellence', 'description' => 'We pursue the highest standards.'], ['title' => 'Integrity', 'description' => 'Honesty guides our actions.'], ['title' => 'Innovation', 'description' => 'We embrace new ideas.'], ['title' => 'Service', 'description' => 'Customer satisfaction is our priority.']]], 'style' => ['backgroundColor' => '#f8fafc']],
                    ['type' => 'cta', 'content' => ['title' => 'Ready to Work With Us?', 'description' => 'Contact us today to learn more', 'buttonText' => 'Get in Touch', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => $colors['primary']]],
                ],
            ],
            'services' => [
                'title' => 'Our Services',
                'subtitle' => 'Comprehensive solutions tailored to your needs',
                'sections' => [
                    ['type' => 'page-header', 'content' => ['title' => 'Our Services', 'subtitle' => 'What we offer', 'backgroundColor' => $colors['dark'], 'textColor' => '#ffffff', 'textPosition' => 'center'], 'style' => ['minHeight' => 220]],
                    ['type' => 'services', 'content' => ['title' => 'What We Offer', 'items' => [['title' => 'Service 1', 'description' => 'Professional service tailored to your needs.'], ['title' => 'Service 2', 'description' => 'Expert solutions for your requirements.'], ['title' => 'Service 3', 'description' => 'Quality service you can trust.']]], 'style' => ['backgroundColor' => '#ffffff']],
                    ['type' => 'cta', 'content' => ['title' => 'Need Our Services?', 'description' => 'Contact us for a free consultation', 'buttonText' => 'Contact Us', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => $colors['primary']]],
                ],
            ],
            'contact' => [
                'title' => 'Contact Us',
                'subtitle' => 'Get in touch with our team',
                'sections' => [
                    ['type' => 'page-header', 'content' => ['title' => 'Contact Us', 'subtitle' => "We'd love to hear from you", 'backgroundColor' => $colors['dark'], 'textColor' => '#ffffff', 'textPosition' => 'center'], 'style' => ['minHeight' => 220]],
                    ['type' => 'contact', 'content' => ['title' => 'Send Us a Message', 'description' => 'Fill out the form and we will respond within 24 hours.', 'showForm' => true, 'email' => 'info@example.com', 'phone' => '+260 97X XXX XXX', 'address' => 'Lusaka, Zambia'], 'style' => ['backgroundColor' => '#ffffff']],
                ],
            ],
            'blog' => [
                'title' => 'Blog',
                'subtitle' => 'Latest news and insights',
                'sections' => [
                    ['type' => 'page-header', 'content' => ['title' => 'Our Blog', 'subtitle' => 'Latest news, tips, and insights', 'backgroundColor' => $colors['dark'], 'textColor' => '#ffffff', 'textPosition' => 'center'], 'style' => ['minHeight' => 220]],
                    ['type' => 'blog', 'content' => ['title' => 'Latest Articles', 'posts' => [
                        ['title' => 'Getting Started with Our Services', 'excerpt' => 'Learn how to make the most of what we offer and achieve your goals faster.', 'date' => date('M d, Y'), 'image' => null],
                        ['title' => 'Tips for Success in ' . ucfirst($businessType), 'excerpt' => 'Expert advice and proven strategies to help you succeed in your endeavors.', 'date' => date('M d, Y', strtotime('-3 days')), 'image' => null],
                        ['title' => 'Industry Updates and Trends', 'excerpt' => 'Stay informed about the latest developments and what they mean for you.', 'date' => date('M d, Y', strtotime('-7 days')), 'image' => null],
                    ]], 'style' => ['backgroundColor' => '#ffffff']],
                    ['type' => 'cta', 'content' => ['title' => 'Want to Stay Updated?', 'description' => 'Subscribe to our newsletter for the latest updates', 'buttonText' => 'Subscribe Now', 'buttonLink' => '#contact'], 'style' => ['backgroundColor' => $colors['primary']]],
                ],
            ],
            'pricing' => [
                'title' => 'Pricing',
                'subtitle' => 'Simple, transparent pricing',
                'sections' => [
                    ['type' => 'page-header', 'content' => ['title' => 'Our Pricing', 'subtitle' => 'Choose the plan that works for you', 'backgroundColor' => $colors['dark'], 'textColor' => '#ffffff', 'textPosition' => 'center'], 'style' => ['minHeight' => 220]],
                    ['type' => 'pricing', 'content' => ['title' => 'Pricing Plans', 'plans' => [
                        ['name' => 'Basic', 'price' => 'K500/mo', 'features' => ['Core features', 'Email support', 'Up to 5 users'], 'popular' => false, 'buttonText' => 'Get Started'],
                        ['name' => 'Professional', 'price' => 'K1,500/mo', 'features' => ['All Basic features', 'Priority support', 'Up to 25 users', 'Advanced analytics'], 'popular' => true, 'buttonText' => 'Get Started'],
                        ['name' => 'Enterprise', 'price' => 'Custom', 'features' => ['All Professional features', '24/7 support', 'Unlimited users', 'Custom integrations'], 'popular' => false, 'buttonText' => 'Contact Us'],
                    ]], 'style' => ['backgroundColor' => '#ffffff']],
                    ['type' => 'faq', 'content' => ['title' => 'Pricing FAQ', 'items' => [
                        ['question' => 'Can I change plans later?', 'answer' => 'Yes, you can upgrade or downgrade your plan at any time.'],
                        ['question' => 'Is there a free trial?', 'answer' => 'Yes, we offer a 14-day free trial on all plans.'],
                        ['question' => 'What payment methods do you accept?', 'answer' => 'We accept MTN Mobile Money, Airtel Money, and bank transfers.'],
                    ]], 'style' => ['backgroundColor' => '#f8fafc']],
                    ['type' => 'cta', 'content' => ['title' => 'Ready to Get Started?', 'description' => 'Contact us for a personalized quote', 'buttonText' => 'Contact Sales', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => $colors['primary']]],
                ],
            ],
            'faq' => [
                'title' => 'FAQ',
                'subtitle' => 'Frequently asked questions',
                'sections' => [
                    ['type' => 'page-header', 'content' => ['title' => 'Frequently Asked Questions', 'subtitle' => 'Find answers to common questions', 'backgroundColor' => $colors['dark'], 'textColor' => '#ffffff', 'textPosition' => 'center'], 'style' => ['minHeight' => 220]],
                    ['type' => 'faq', 'content' => ['title' => 'Common Questions', 'items' => [
                        ['question' => 'What services do you offer?', 'answer' => 'We offer a comprehensive range of ' . $businessType . ' services tailored to meet your specific needs.'],
                        ['question' => 'How can I get started?', 'answer' => 'Simply contact us through our form or give us a call, and we\'ll guide you through the process.'],
                        ['question' => 'What are your business hours?', 'answer' => 'We\'re available Monday to Friday, 8am to 5pm. Weekend appointments available on request.'],
                        ['question' => 'Do you offer free consultations?', 'answer' => 'Yes! We offer a free initial consultation to understand your needs and provide recommendations.'],
                        ['question' => 'What payment methods do you accept?', 'answer' => 'We accept MTN Mobile Money, Airtel Money, bank transfers, and major credit cards.'],
                        ['question' => 'How long does it take to see results?', 'answer' => 'Results vary depending on the service, but most clients see improvements within the first few weeks.'],
                    ]], 'style' => ['backgroundColor' => '#ffffff']],
                    ['type' => 'cta', 'content' => ['title' => 'Still Have Questions?', 'description' => 'Our team is here to help', 'buttonText' => 'Contact Us', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => $colors['primary']]],
                ],
            ],
            'testimonials' => [
                'title' => 'Testimonials',
                'subtitle' => 'What our clients say',
                'sections' => [
                    ['type' => 'page-header', 'content' => ['title' => 'Client Testimonials', 'subtitle' => 'Hear from our satisfied customers', 'backgroundColor' => $colors['dark'], 'textColor' => '#ffffff', 'textPosition' => 'center'], 'style' => ['minHeight' => 220]],
                    ['type' => 'testimonials', 'content' => ['title' => 'What Our Clients Say', 'items' => [
                        ['name' => 'Mwila Chanda', 'role' => 'Business Owner', 'text' => 'Excellent service! They exceeded our expectations and delivered outstanding results.', 'rating' => 5],
                        ['name' => 'Grace Tembo', 'role' => 'Manager', 'text' => 'Professional team that truly understands customer needs. Highly recommended!', 'rating' => 5],
                        ['name' => 'John Banda', 'role' => 'Entrepreneur', 'text' => 'The best decision we made was choosing to work with them. Amazing experience!', 'rating' => 5],
                    ]], 'style' => ['backgroundColor' => '#f8fafc']],
                    ['type' => 'stats', 'content' => ['title' => 'Our Track Record', 'items' => [
                        ['value' => '500+', 'label' => 'Happy Clients'],
                        ['value' => '98%', 'label' => 'Satisfaction Rate'],
                        ['value' => '10+', 'label' => 'Years Experience'],
                        ['value' => '24/7', 'label' => 'Support'],
                    ]], 'style' => ['backgroundColor' => $colors['primary']]],
                    ['type' => 'cta', 'content' => ['title' => 'Ready to Join Our Happy Clients?', 'description' => 'Get started today', 'buttonText' => 'Contact Us', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#1f2937']],
                ],
            ],
            'gallery' => [
                'title' => 'Gallery',
                'subtitle' => 'Our work and portfolio',
                'sections' => [
                    ['type' => 'page-header', 'content' => ['title' => 'Our Gallery', 'subtitle' => 'Browse our portfolio and recent work', 'backgroundColor' => $colors['dark'], 'textColor' => '#ffffff', 'textPosition' => 'center'], 'style' => ['minHeight' => 220]],
                    ['type' => 'about', 'content' => ['title' => 'Our Portfolio', 'description' => 'Take a look at some of our recent projects and the quality work we deliver to our clients. Each project represents our commitment to excellence and attention to detail.'], 'style' => ['backgroundColor' => '#ffffff']],
                    ['type' => 'gallery', 'content' => ['title' => 'Recent Work', 'images' => []], 'style' => ['backgroundColor' => '#f8fafc']],
                    ['type' => 'cta', 'content' => ['title' => 'Like What You See?', 'description' => 'Let us create something amazing for you', 'buttonText' => 'Start Your Project', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => $colors['primary']]],
                ],
            ],
        ];
        
        return $fallbacks[$pageType] ?? [
            'title' => ucfirst($pageType),
            'subtitle' => '',
            'sections' => [
                ['type' => 'page-header', 'content' => ['title' => ucfirst($pageType), 'subtitle' => '', 'backgroundColor' => $colors['dark'], 'textColor' => '#ffffff', 'textPosition' => 'center'], 'style' => ['minHeight' => 220]],
            ],
        ];
    }
    
    /**
     * Call the AI API (supports multiple providers)
     */
    private function callAI(string $systemPrompt, string $userPrompt): string
    {
        if (!$this->isConfigured()) {
            throw new \Exception('AI service not configured');
        }
        
        return match ($this->provider) {
            'gemini' => $this->callGemini($systemPrompt, $userPrompt),
            'ollama' => $this->callOllama($systemPrompt, $userPrompt),
            default => $this->callOpenAICompatible($systemPrompt, $userPrompt), // OpenAI, Groq
        };
    }
    
    /**
     * Call OpenAI-compatible API (OpenAI, Groq, etc.)
     */
    private function callOpenAICompatible(string $systemPrompt, string $userPrompt): string
    {
        $request = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(30);
        
        // Bypass SSL verification in development (for Windows SSL certificate issues)
        if (app()->environment('local', 'development')) {
            $request = $request->withoutVerifying();
        }
        
        $response = $request->post("{$this->baseUrl}/chat/completions", [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 4096, // Increased for longer, more detailed responses
        ]);
        
        if (!$response->successful()) {
            Log::error('AI API request failed', [
                'provider' => $this->provider,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('AI API request failed: ' . $response->body());
        }
        
        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }
    
    /**
     * Call Google Gemini API
     */
    private function callGemini(string $systemPrompt, string $userPrompt): string
    {
        $request = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(30);
        
        // Bypass SSL verification in development (for Windows SSL certificate issues)
        if (app()->environment('local', 'development')) {
            $request = $request->withoutVerifying();
        }
        
        $response = $request->post("{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "{$systemPrompt}\n\n{$userPrompt}"],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1000,
            ],
        ]);
        
        if (!$response->successful()) {
            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }
        
        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
    
    /**
     * Call Ollama API (local)
     */
    private function callOllama(string $systemPrompt, string $userPrompt): string
    {
        $response = Http::timeout(60)->post("{$this->baseUrl}/generate", [
            'model' => $this->model,
            'prompt' => "{$systemPrompt}\n\n{$userPrompt}",
            'stream' => false,
            'options' => [
                'temperature' => 0.7,
            ],
        ]);
        
        if (!$response->successful()) {
            Log::error('Ollama API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Ollama API request failed: ' . $response->body());
        }
        
        $data = $response->json();
        return $data['response'] ?? '';
    }
    
    /**
     * Get section-specific prompt
     */
    private function getSectionPrompt(string $sectionType, string $businessName, string $businessType, ?string $description, string $tone): array
    {
        $context = $description ? " Description: {$description}" : "";
        
        $prompts = [
            'hero' => [
                'system' => "You are a professional copywriter. Generate compelling hero section content. Return as JSON with: title, subtitle, buttonText, buttonLink.",
                'user' => "Create hero section content for {$businessName}, a {$businessType} business.{$context} Tone: {$tone}",
            ],
            'about' => [
                'system' => "You are a professional copywriter. Generate about section content. Return as JSON with: title, content (2-3 paragraphs), image (suggest 'about-business').",
                'user' => "Create about section content for {$businessName}, a {$businessType} business.{$context} Tone: {$tone}",
            ],
            'services' => [
                'system' => "You are a professional copywriter. Generate services section content. Return as JSON with: title, subtitle, items (array of {title, description, icon}).",
                'user' => "Create services section with 4 services for {$businessName}, a {$businessType} business.{$context} Tone: {$tone}",
            ],
            'features' => [
                'system' => "You are a professional copywriter. Generate features section content. Return as JSON with: title, subtitle, items (array of {title, description, icon}).",
                'user' => "Create features section with 4 key features for {$businessName}, a {$businessType} business.{$context} Tone: {$tone}",
            ],
            'cta' => [
                'system' => "You are a professional copywriter. Generate call-to-action section content. Return as JSON with: title, subtitle, buttonText, buttonLink.",
                'user' => "Create a compelling CTA section for {$businessName}, a {$businessType} business.{$context} Tone: {$tone}",
            ],
            'contact' => [
                'system' => "You are a professional copywriter. Generate contact section content. Return as JSON with: title, subtitle, address, phone, email, hours.",
                'user' => "Create contact section content for {$businessName}, a {$businessType} business.{$context} Tone: {$tone}",
            ],
        ];
        
        return $prompts[$sectionType] ?? [
            'system' => "You are a professional copywriter. Generate website section content. Return as JSON.",
            'user' => "Create {$sectionType} section content for {$businessName}, a {$businessType} business.{$context} Tone: {$tone}",
        ];
    }

    /**
     * Parse AI response for section content
     */
    private function parseSectionResponse(string $sectionType, string $response): array
    {
        try {
            // Try to extract JSON from the response
            $jsonMatch = preg_match('/\{[\s\S]*\}|\[[\s\S]*\]/', $response, $matches);
            if ($jsonMatch) {
                $data = json_decode($matches[0], true);
                if (is_array($data)) {
                    return $data;
                }
            }
            
            // If no valid JSON, return as raw content
            return ['content' => $response];
        } catch (\Exception $e) {
            return ['content' => $response];
        }
    }
    
    /**
     * Get fallback content when AI fails
     */
    private function getFallbackContent(string $sectionType, string $businessName): array
    {
        $fallbacks = [
            'hero' => [
                'title' => "Welcome to {$businessName}",
                'subtitle' => 'Your trusted partner for quality services',
                'buttonText' => 'Get Started',
                'buttonLink' => '#contact',
            ],
            'about' => [
                'title' => "About {$businessName}",
                'content' => "We are dedicated to providing exceptional services to our customers. With years of experience and a commitment to quality, we strive to exceed expectations in everything we do.",
            ],
            'services' => [
                'title' => 'Our Services',
                'subtitle' => 'What we offer',
                'items' => [
                    ['title' => 'Service 1', 'description' => 'Description of service 1', 'icon' => 'star'],
                    ['title' => 'Service 2', 'description' => 'Description of service 2', 'icon' => 'heart'],
                    ['title' => 'Service 3', 'description' => 'Description of service 3', 'icon' => 'check'],
                ],
            ],
            'features' => [
                'title' => 'Why Choose Us',
                'subtitle' => 'Our key features',
                'items' => [
                    ['title' => 'Quality', 'description' => 'We deliver quality in everything we do', 'icon' => 'check-badge'],
                    ['title' => 'Experience', 'description' => 'Years of industry experience', 'icon' => 'academic-cap'],
                    ['title' => 'Support', 'description' => '24/7 customer support', 'icon' => 'phone'],
                ],
            ],
            'cta' => [
                'title' => 'Ready to Get Started?',
                'subtitle' => 'Contact us today to learn more about our services',
                'buttonText' => 'Contact Us',
                'buttonLink' => '#contact',
            ],
            'contact' => [
                'title' => 'Contact Us',
                'subtitle' => 'Get in touch with us',
                'address' => 'Lusaka, Zambia',
                'phone' => '+260 XXX XXX XXX',
                'email' => 'info@example.com',
            ],
        ];
        
        return $fallbacks[$sectionType] ?? ['title' => $sectionType, 'content' => 'Content goes here'];
    }
    
    /**
     * Get default color palette by business type
     */
    private function getDefaultColorPalette(string $businessType): array
    {
        $palettes = [
            'restaurant' => [
                'primary' => '#dc2626',
                'secondary' => '#fbbf24',
                'accent' => '#16a34a',
                'background' => '#fef3c7',
                'text' => '#1f2937',
            ],
            'church' => [
                'primary' => '#7c3aed',
                'secondary' => '#fbbf24',
                'accent' => '#2563eb',
                'background' => '#f5f3ff',
                'text' => '#1f2937',
            ],
            'tutor' => [
                'primary' => '#2563eb',
                'secondary' => '#10b981',
                'accent' => '#f59e0b',
                'background' => '#eff6ff',
                'text' => '#1f2937',
            ],
            'portfolio' => [
                'primary' => '#1f2937',
                'secondary' => '#6b7280',
                'accent' => '#3b82f6',
                'background' => '#ffffff',
                'text' => '#1f2937',
            ],
            'default' => [
                'primary' => '#2563eb',
                'secondary' => '#64748b',
                'accent' => '#10b981',
                'background' => '#f8fafc',
                'text' => '#1f2937',
            ],
        ];
        
        return $palettes[$businessType] ?? $palettes['default'];
    }
}
