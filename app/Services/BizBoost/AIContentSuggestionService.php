<?php

namespace App\Services\BizBoost;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIContentSuggestionService
{
    private string $provider;
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private array $extraBody = [];

    public function __construct()
    {
        $this->provider = config('services.ai.provider', 'nvidia');

        switch ($this->provider) {
            case 'nvidia':
                $this->apiKey  = config('services.ai.nvidia_key', '');
                $this->model   = config('services.ai.nvidia_model', 'deepseek-ai/deepseek-v4-flash');
                $this->baseUrl = config('services.ai.nvidia_url', 'https://integrate.api.nvidia.com/v1');
                break;
            case 'gemini':
                $this->apiKey  = config('services.ai.gemini_key', '');
                $this->model   = config('services.ai.gemini_model', 'gemini-pro');
                $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
                break;
            case 'ollama':
                $this->apiKey  = '';
                $this->model   = config('services.ai.ollama_model', 'llama3');
                $this->baseUrl = config('services.ai.ollama_url', 'http://localhost:11434/api');
                break;
            default:
                $this->apiKey  = config('services.ai.openai_key', '');
                $this->model   = config('services.ai.openai_model', 'gpt-3.5-turbo');
                $this->baseUrl = config('services.ai.openai_url', 'https://api.openai.com/v1');
        }

        if ($this->provider === 'nvidia' && str_contains($this->model, 'flash')) {
            $this->extraBody = [
                'chat_template_kwargs' => [
                    'thinking'         => true,
                    'reasoning_effort' => 'high',
                ],
            ];
        }
    }

    public function isConfigured(): bool
    {
        if ($this->provider === 'ollama') {
            return !empty($this->baseUrl);
        }
        return !empty($this->apiKey);
    }

    public function generateContent(string $systemPrompt, string $userPrompt): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('AI service is not configured. Check your .env settings.');
        }

        return match ($this->provider) {
            'gemini' => $this->callGemini($systemPrompt, $userPrompt),
            'ollama' => $this->callOllama($systemPrompt, $userPrompt),
            default  => $this->callOpenAICompatible($systemPrompt, $userPrompt),
        };
    }

    public function generateAdvisorResponse(string $message, array $context): string
    {
        $insights = $context['insights'] ?? [];
        $businessName = $context['business_name'] ?? 'your business';
        $industry = $context['industry'] ?? 'general';
        $salesTotal = number_format($insights['total_sales_30d'] ?? 0, 2);
        $customerCount = $insights['total_customers'] ?? 0;
        $postsCount = $insights['posts_30d'] ?? 0;
        $activeCampaigns = $insights['active_campaigns'] ?? 0;

        $systemPrompt = <<<PROMPT
You are an expert AI Business Advisor for {$businessName}, a {$industry} business in Zambia.
Your role is to provide actionable, practical advice based on the business's real data.

Business Metrics:
- Total Customers: {$customerCount}
- Sales (30 days): K{$salesTotal}
- Posts (30 days): {$postsCount}
- Active Campaigns: {$activeCampaigns}

Guidelines:
1. Be specific and reference their actual numbers
2. Suggest concrete actions they can take today
3. Recommend BizBoost features that would help (AI Content, Campaigns, WhatsApp, etc.)
4. Keep responses concise but valuable (2-4 paragraphs)
5. Tone: professional, encouraging, focused on growth
6. If they ask about something outside your scope, redirect to how BizBoost can help
PROMPT;

        try {
            return $this->generateContent($systemPrompt, $message);
        } catch (\Exception $e) {
            Log::error('Advisor AI call failed', ['error' => $e->getMessage()]);
            return $this->fallbackAdvisorResponse($message, $context);
        }
    }

    public function generateContentForParams(array $params, object $business): string
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

        $typeLabels = [
            'caption' => 'social media caption',
            'ad' => 'advertisement copy',
            'description' => 'product description',
            'idea' => 'marketing ideas',
            'whatsapp' => 'WhatsApp message',
            'promo' => 'promotion announcement',
        ];

        $typeLabel = $typeLabels[$type] ?? $type;

        $systemPrompt = <<<PROMPT
You are a marketing assistant for a {$business->industry} business in Zambia called '{$business->name}'.
Generate a {$typeLabel} with a {$tone} tone in {$languageNames[$language]}.

Context: {$context}

PROMPT;

        if (!empty($params['product_name'])) {
            $systemPrompt .= "Product/Service: {$params['product_name']}\n";
        }

        $systemPrompt .= "\nRequirements:\n";
        if ($params['include_emoji'] ?? false) {
            $systemPrompt .= "- Include relevant emojis\n";
        }
        if ($params['include_hashtags'] ?? false) {
            $systemPrompt .= "- Include 3-5 relevant hashtags\n";
        }
        if ($params['include_cta'] ?? false) {
            $systemPrompt .= "- Include a clear call-to-action\n";
        }

        $userPrompt = "Generate the {$typeLabel} now.";

        try {
            return $this->generateContent($systemPrompt, $userPrompt);
        } catch (\Exception $e) {
            Log::error('Content generation AI call failed', ['error' => $e->getMessage()]);
            return $this->fallbackContent($params);
        }
    }

    private function callOpenAICompatible(string $systemPrompt, string $userPrompt): string
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (!empty($this->apiKey)) {
            $headers['Authorization'] = "Bearer {$this->apiKey}";
        }

        $request = Http::withHeaders($headers)->timeout(120);

        if (app()->environment('local', 'development')) {
            $request = $request->withoutVerifying();
        }

        $payload = [
            'model'    => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens'  => 16384,
        ];

        if (!empty($this->extraBody)) {
            $payload['extra_body'] = $this->extraBody;
        }

        $response = $request->post("{$this->baseUrl}/chat/completions", $payload);

        if (!$response->successful()) {
            $errorBody = $response->body();
            Log::error('AI API request failed', [
                'provider' => $this->provider,
                'model' => $this->model,
                'status' => $response->status(),
                'body' => substr($errorBody, 0, 500),
            ]);
            throw new \RuntimeException("AI API request failed (HTTP {$response->status()})");
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    private function callGemini(string $systemPrompt, string $userPrompt): string
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(30)
            ->post("{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    ['parts' => [['text' => "{$systemPrompt}\n\n{$userPrompt}"]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1000,
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini API request failed');
        }

        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    private function callOllama(string $systemPrompt, string $userPrompt): string
    {
        $response = Http::timeout(60)
            ->post("{$this->baseUrl}/generate", [
                'model' => $this->model,
                'prompt' => "{$systemPrompt}\n\n{$userPrompt}",
                'stream' => false,
                'options' => ['temperature' => 0.7],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Ollama API request failed');
        }

        $data = $response->json();
        return $data['response'] ?? '';
    }

    private function fallbackAdvisorResponse(string $message, array $context): string
    {
        $message = strtolower($message);
        $insights = $context['insights'] ?? [];
        $businessName = $context['business_name'] ?? 'your business';

        if (str_contains($message, 'sales') || str_contains($message, 'revenue')) {
            $salesCount = $insights['sales_count_30d'] ?? 0;
            $salesTotal = number_format($insights['total_sales_30d'] ?? 0, 2);
            return "Based on your data, {$businessName} has made {$salesCount} sales totaling K{$salesTotal} in the last 30 days. To boost sales, consider running a promotion campaign, engaging inactive customers, or showcasing your top product. Would you like me to help you create a sales campaign?";
        }

        if (str_contains($message, 'market') || str_contains($message, 'promote')) {
            return "You can improve your marketing by posting consistently (aim for 3-4 posts per week), using our industry-specific templates, scheduling ahead, and engaging with customer comments. Would you like me to suggest some post ideas?";
        }

        if (str_contains($message, 'customer') || str_contains($message, 'client')) {
            return "To grow your customer base, ask for referrals from happy customers, offer loyalty rewards, collect feedback, and use WhatsApp broadcasts to keep customers informed. Would you like help setting up a customer engagement campaign?";
        }

        return "Thanks for your question! Based on your data, I recommend keeping up with regular social media posts, engaging with your customers through WhatsApp, and considering a promotional campaign. Is there something specific you'd like help with?";
    }

    private function fallbackContent(array $params): string
    {
        $mockResponses = [
            'caption' => "✨ New arrivals just dropped! Come check out our latest collection - you won't want to miss this! 🛍️\n\nVisit us today or DM for more info.\n\n#NewArrivals #ShopLocal #ZambianBusiness #QualityProducts",
            'ad' => "🎉 SPECIAL OFFER! 🎉\n\nGet 20% OFF on all items this weekend only!\n\n✅ Quality products\n✅ Great prices\n✅ Excellent service\n\nDon't miss out - offer ends Sunday!",
            'description' => "Discover our premium quality products, carefully selected to meet your needs. Made with the finest materials and designed for lasting satisfaction.",
            'idea' => "Here are 5 marketing ideas:\n\n1. Run a 'Customer of the Week' feature\n2. Create a loyalty program\n3. Partner with complementary local businesses\n4. Host a live product demonstration\n5. Share behind-the-scenes content",
            'whatsapp' => "Hi! 👋\n\nThank you for your interest in our products! We have some amazing deals available right now. Would you like me to share our latest catalog with you?",
            'promo' => "🔥 FLASH SALE ALERT! 🔥\n\nFor the next 48 hours only:\n\n💰 Up to 30% OFF selected items\n🚚 FREE delivery on orders over K500\n🎁 FREE gift with every purchase",
        ];

        return $mockResponses[$params['content_type']] ?? $mockResponses['caption'];
    }
}
