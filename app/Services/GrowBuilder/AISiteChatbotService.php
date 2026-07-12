<?php

namespace App\Services\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AISiteChatbotService
{
    private AIContentService $aiService;

    public function __construct(AIContentService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Build searchable content index for a site
     */
    public function buildContentIndex(GrowBuilderSite $site): array
    {
        $cacheKey = "chatbot_index:{$site->id}";
        $ttl = 3600; // 1 hour

        return Cache::remember($cacheKey, $ttl, function () use ($site) {
            $pages = $site->pages()->where('is_published', true)->get();
            $chunks = [];

            foreach ($pages as $page) {
                $sections = is_string($page->sections)
                    ? json_decode($page->sections, true) ?? []
                    : ($page->sections ?? []);

                foreach ($sections as $section) {
                    $content = $section['content'] ?? [];
                    $text = $this->extractText($content);
                    if (strlen($text) > 20) {
                        $chunks[] = [
                            'page' => $page->title,
                            'section' => $section['type'] ?? 'unknown',
                            'text' => $text,
                            'tokens' => str_word_count($text),
                        ];
                    }
                }

                // Also index page meta
                $meta = $page->meta ?? [];
                if (!empty($meta['description'])) {
                    $chunks[] = [
                        'page' => $page->title,
                        'section' => 'meta',
                        'text' => $meta['description'],
                        'tokens' => str_word_count($meta['description']),
                    ];
                }
            }

            return $chunks;
        });
    }

    /**
     * Answer a visitor's question based on site content
     */
    public function answer(GrowBuilderSite $site, string $question): array
    {
        $index = $this->buildContentIndex($site);

        // Find relevant content via keyword matching first (fast path)
        $relevant = $this->findRelevantContent($index, $question);

        if (empty($relevant)) {
            return $this->noAnswerResponse();
        }

        // Build context from relevant content
        $contextText = '';
        foreach (array_slice($relevant, 0, 5) as $item) {
            $contextText .= "[{$item['page']} - {$item['section']}]: {$item['text']}\n\n";
        }

        // Use existing AI to answer based on site content
        try {
            $systemPrompt = <<<PROMPT
You are a helpful customer service chatbot for the website "{$site->name}".
Answer the visitor's question STRICTLY based on the website content provided below.
If the content doesn't contain the answer, say "I'm not sure about that — I'll connect you with the team."
Be friendly, concise, and conversational. Use natural language.
Never make up information not present in the content.

Website Content:
{$contextText}
PROMPT;

            $response = $this->aiService->smartChatWithPrompt($systemPrompt, $question);
            $message = is_array($response) ? ($response['message'] ?? '') : $response;

            $hasAnswer = !str_contains($message, "I'm not sure about that");
            if (!$hasAnswer) {
                return $this->noAnswerResponse($message);
            }

            return [
                'answer' => $message,
                'has_answer' => true,
                'capture_lead' => false,
            ];
        } catch (\Exception $e) {
            Log::error('Chatbot AI error', ['error' => $e->getMessage()]);
            return $this->fallbackAnswer($question, $relevant);
        }
    }

    /**
     * Find relevant content chunks using keyword matching
     */
    private function findRelevantContent(array $index, string $question): array
    {
        $keywords = $this->extractKeywords($question);
        $scored = [];

        foreach ($index as $item) {
            $text = strtolower($item['text']);
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $score += substr_count($text, $keyword);
                }
            }
            if ($score > 0) {
                $scored[] = array_merge($item, ['relevance' => $score]);
            }
        }

        usort($scored, fn($a, $b) => $b['relevance'] - $a['relevance']);
        return $scored;
    }

    /**
     * Extract meaningful keywords from a question
     */
    private function extractKeywords(string $question): array
    {
        $stopWords = ['the', 'a', 'an', 'is', 'are', 'was', 'were', 'do', 'does', 'did',
            'can', 'will', 'would', 'could', 'should', 'what', 'when', 'where', 'who',
            'how', 'which', 'why', 'this', 'that', 'these', 'those', 'i', 'you', 'we',
            'they', 'he', 'she', 'it', 'my', 'your', 'his', 'her', 'its', 'our', 'their',
            'am', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did',
            'shall', 'may', 'might', 'must', 'of', 'in', 'on', 'at', 'to', 'for', 'with',
            'by', 'about', 'from', 'into', 'through', 'during', 'before', 'after',
            'above', 'below', 'between', 'out', 'off', 'over', 'under', 'again',
            'further', 'then', 'once', 'here', 'there', 'and', 'but', 'or', 'nor', 'not',
            'so', 'yet', 'both', 'either', 'neither', 'each', 'every', 'all', 'any',
            'few', 'more', 'most', 'other', 'some', 'such', 'no', 'only', 'own', 'same'];

        $words = preg_split('/\s+/', strtolower($question));
        $keywords = array_diff($words, $stopWords);
        $keywords = array_filter($keywords, fn($w) => strlen($w) > 2);

        // Add common variations
        $variations = [];
        foreach ($keywords as $word) {
            $variations[] = $word;
            $variations[] = rtrim($word, 's'); // singular
            $variations[] = $word . 's'; // plural
            $variations[] = rtrim($word, 'ing'); // stem
        }

        return array_unique($variations);
    }

    /**
     * Extract readable text from section content
     */
    private function extractText(array $content): string
    {
        $fields = ['title', 'subtitle', 'content', 'description', 'text', 'name',
            'company', 'position', 'question', 'answer', 'service', 'detail',
            'address', 'phone', 'email', 'hours', 'buttonText', 'label', 'value'];
        $texts = [];

        foreach ($fields as $field) {
            if (!empty($content[$field]) && is_string($content[$field])) {
                $texts[] = $content[$field];
            }
        }

        // Handle items array (services, features, testimonials, etc.)
        if (!empty($content['items']) && is_array($content['items'])) {
            foreach ($content['items'] as $item) {
                $texts[] = $this->extractText(is_array($item) ? $item : []);
            }
        }

        return implode('. ', array_filter($texts, 'is_string'));
    }

    /**
     * Simple fallback using keyword matching without AI
     */
    private function fallbackAnswer(string $question, array $relevant): array
    {
        if (empty($relevant)) {
            return $this->noAnswerResponse();
        }

        $best = $relevant[0];
        return [
            'answer' => "Based on our website: " . mb_substr($best['text'], 0, 300),
            'has_answer' => true,
            'capture_lead' => false,
        ];
    }

    private function noAnswerResponse(?string $message = null): array
    {
        return [
            'answer' => $message ?? "I'm not sure about that — I'll connect you with the team. Please leave your email and we'll get back to you.",
            'has_answer' => false,
            'capture_lead' => true,
        ];
    }

    /**
     * Record a lead from the chatbot
     */
    public function recordLead(GrowBuilderSite $site, string $email, ?string $question = null): void
    {
        $site->chatbotLeads()->create([
            'email' => $email,
            'question' => $question,
            'created_at' => now(),
        ]);
    }
}
