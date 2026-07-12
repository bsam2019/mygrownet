<?php

namespace App\Services\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIReferenceImportService
{
    private AIContentService $aiService;
    private AIImageService $imageService;

    private const MAX_PAGES = 10;
    private const MAX_SECTION_CHARS = 2000;

    public function __construct(AIContentService $aiService, AIImageService $imageService)
    {
        $this->aiService = $aiService;
        $this->imageService = $imageService;
    }

    /**
     * Analyze a URL and extract site structure
     */
    public function analyzeUrl(string $url): array
    {
        $html = $this->fetchPage($url);
        if (!$html) {
            return ['error' => 'Could not fetch the URL. Make sure it is publicly accessible.'];
        }

        // Extract basic info
        $title = $this->extractTitle($html);
        $metaDescription = $this->extractMetaDescription($html);

        // Extract colors from CSS/styles
        $colors = $this->extractColors($html);

        // Extract navigation links (potential pages)
        $navLinks = $this->extractNavigation($html, $url);

        // Extract section content from the page
        $sections = $this->extractSections($html);

        // Use AI to understand the business type and structure
        $analysis = $this->analyzeWithAI($title, $metaDescription, $sections, $navLinks);

        return [
            'title' => $title,
            'meta_description' => $metaDescription,
            'colors' => $colors,
            'nav_links' => $navLinks,
            'sections' => $sections,
            'analysis' => $analysis,
            'url' => $url,
        ];
    }

    /**
     * Convert analysis into GrowBuilder site structure
     */
    public function convertToSiteStructure(array $analysis): array
    {
        $pages = [];
        $siteSettings = [
            'name' => $analysis['business_name'] ?? $analysis['title'] ?? 'Imported Site',
            'business_type' => $analysis['business_type'] ?? 'business',
            'colors' => $analysis['colors'] ?? ['primary' => '#2563eb', 'secondary' => '#1e40af', 'accent' => '#f59e0b'],
            'description' => $analysis['description'] ?? '',
        ];

        // Generate pages from analysis
        $suggestedPages = $analysis['suggested_pages'] ?? [['type' => 'home', 'name' => 'Home']];
        foreach (array_slice($suggestedPages, 0, self::MAX_PAGES) as $i => $pageInfo) {
            $pageType = $pageInfo['type'] ?? 'page';
            $pageName = $pageInfo['name'] ?? ucfirst($pageType);

            $sections = $this->generateSectionsForPage($pageType, $pageInfo['sections'] ?? []);
            $pages[] = [
                'title' => $pageName,
                'slug' => $i === 0 ? '/' : strtolower(str_replace(' ', '-', $pageName)),
                'type' => $pageType,
                'is_home' => $i === 0,
                'sections' => $sections,
            ];
        }

        return [
            'settings' => $siteSettings,
            'pages' => $pages,
        ];
    }

    /**
     * Fetch HTML content from a URL
     */
    private function fetchPage(string $url): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; GrowBuilderBot/1.0)'])
                ->get($url);

            if (!$response->successful()) return null;
            return $response->body();
        } catch (\Exception $e) {
            Log::error('Reference import fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractTitle(string $html): string
    {
        preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m);
        return trim(strip_tags($m[1] ?? ''));
    }

    private function extractMetaDescription(string $html): string
    {
        preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']/is', $html, $m);
        if (empty($m)) {
            preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']description["\']/is', $html, $m);
        }
        return trim($m[1] ?? '');
    }

    private function extractColors(string $html): array
    {
        $colors = [];
        // Extract hex colors from style tags and inline styles
        preg_match_all('/#[0-9a-fA-F]{6}\b/', $html, $hexMatches);
        $hexCounts = array_count_values($hexMatches[0] ?? []);
        arsort($hexCounts);
        $topColors = array_slice(array_keys($hexCounts), 0, 5);

        if (!empty($topColors)) {
            $colors['primary'] = $topColors[0];
            $colors['secondary'] = $topColors[1] ?? '#1e40af';
            $colors['accent'] = $topColors[2] ?? '#f59e0b';
        }

        return $colors;
    }

    private function extractNavigation(string $html, string $baseUrl): array
    {
        $links = [];
        preg_match_all('/<a[^>]+href=["\']([^"\']+?)["\'][^>]*>([^<]*)<\/a>/is', $html, $matches, PREG_SET_ORDER);

        $parsed = parse_url($baseUrl);
        $host = $parsed['host'] ?? '';

        foreach ($matches as $m) {
            $href = trim($m[1]);
            $text = trim(strip_tags($m[2]));

            if (empty($text) || strlen($text) < 2) continue;
            if (str_starts_with($href, '#') || str_starts_with($href, 'javascript:')) continue;
            if (str_starts_with($href, 'tel:') || str_starts_with($href, 'mailto:')) continue;

            // Resolve relative URLs
            if (!str_starts_with($href, 'http')) {
                if (str_starts_with($href, '/')) {
                    $href = $parsed['scheme'] . '://' . $host . $href;
                } else {
                    $href = $baseUrl . '/' . ltrim($href, '/');
                }
            }

            // Only include internal links
            if (str_contains($href, $host)) {
                $links[] = ['url' => $href, 'text' => $text];
            }
        }

        return $links;
    }

    private function extractSections(string $html): array
    {
        $sections = [];

        // Extract visible text content from major HTML sections
        if (preg_match('/<header[^>]*>(.*?)<\/header>/is', $html, $m)) {
            $sections[] = ['type' => 'hero', 'content' => $this->cleanHtml($m[1])];
        }

        if (preg_match('/<nav[^>]*>(.*?)<\/nav>/is', $html, $m)) {
            $sections[] = ['type' => 'navigation', 'content' => $this->cleanHtml($m[1])];
        }

        // Extract <section> elements
        preg_match_all('/<section[^>]*>(.*?)<\/section>/is', $html, $sectionMatches);
        foreach ($sectionMatches[1] ?? [] as $sectionHtml) {
            $heading = '';
            if (preg_match('/<h[1-3][^>]*>(.*?)<\/h[1-3]>/is', $sectionHtml, $h)) {
                $heading = trim(strip_tags($h[1]));
            }
            $sections[] = ['type' => 'content_section', 'heading' => $heading, 'content' => $this->cleanHtml($sectionHtml)];
        }

        // Extract <main> content
        if (preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $m)) {
            $sections[] = ['type' => 'main_content', 'content' => $this->cleanHtml($m[1])];
        }

        if (preg_match('/<footer[^>]*>(.*?)<\/footer>/is', $html, $m)) {
            $sections[] = ['type' => 'footer', 'content' => $this->cleanHtml($m[1])];
        }

        return $sections;
    }

    private function cleanHtml(string $html): string
    {
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function analyzeWithAI(string $title, string $metaDescription, array $sections, array $navLinks): array
    {
        $sectionsText = '';
        foreach (array_slice($sections, 0, 10) as $s) {
            $sectionsText .= "- {$s['type']}: " . mb_substr($s['content'] ?? '', 0, 300) . "\n";
        }

        $navText = '';
        foreach (array_slice($navLinks, 0, 10) as $link) {
            $navText .= "- {$link['text']} ({$link['url']})\n";
        }

        $prompt = <<<PROMPT
Analyze this website and extract its structure for rebuilding.

Title: {$title}
Meta Description: {$metaDescription}

Navigation Links:
{$navText}

Page Sections Found:
{$sectionsText}

Return a JSON object with:
{
    "business_name": "The business name",
    "business_type": "one of: restaurant|church|salon|retail|professional_service|real_estate|ngo|education|healthcare|fitness|technology|creative|other",
    "description": "Brief 1-sentence description",
    "colors": {"primary": "#hex", "secondary": "#hex", "accent": "#hex"},
    "suggested_pages": [
        {
            "type": "home|about|services|contact|faq|blog|gallery|pricing|testimonials",
            "name": "Page name",
            "sections": ["hero", "about", "services", "testimonials", "cta", "contact", "faq", "features", "team", "stats", "gallery", "pricing"]
        }
    ]
}
PROMPT;

        try {
            $result = $this->aiService->chatWithRawPrompt($prompt);
            $json = $this->extractJson($result);
            if ($json) return $json;
        } catch (\Exception $e) {
            Log::error('AI analysis failed for reference import', ['error' => $e->getMessage()]);
        }

        // Fallback structure
        return [
            'business_name' => $title,
            'business_type' => 'business',
            'description' => $metaDescription ?: "Website imported from URL",
            'colors' => [],
            'suggested_pages' => [
                ['type' => 'home', 'name' => 'Home', 'sections' => ['hero', 'about', 'services', 'testimonials', 'cta', 'contact']],
                ['type' => 'about', 'name' => 'About', 'sections' => ['about', 'team', 'stats']],
                ['type' => 'services', 'name' => 'Services', 'sections' => ['services', 'features', 'pricing']],
                ['type' => 'contact', 'name' => 'Contact', 'sections' => ['contact', 'faq']],
            ],
        ];
    }

    private function generateSectionsForPage(string $pageType, array $wantedSections): array
    {
        $defaultSections = [
            'home' => [
                ['type' => 'hero', 'content' => ['title' => 'Welcome', 'subtitle' => '', 'buttonText' => 'Get Started']],
                ['type' => 'about', 'content' => ['title' => 'About Us', 'content' => '']],
                ['type' => 'services', 'content' => ['title' => 'Our Services', 'items' => []]],
                ['type' => 'testimonials', 'content' => ['title' => 'What People Say', 'items' => []]],
                ['type' => 'contact', 'content' => ['title' => 'Get In Touch']],
            ],
            'about' => [
                ['type' => 'page-header', 'content' => ['title' => 'About Us']],
                ['type' => 'about', 'content' => ['title' => 'Our Story', 'content' => '']],
                ['type' => 'team', 'content' => ['title' => 'Our Team', 'items' => []]],
            ],
            'services' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Services']],
                ['type' => 'services', 'content' => ['title' => 'What We Offer', 'items' => []]],
                ['type' => 'pricing', 'content' => ['title' => 'Pricing', 'items' => []]],
            ],
        ];

        $sections = $defaultSections[$pageType] ?? [
            ['type' => 'page-header', 'content' => ['title' => ucfirst($pageType)]],
            ['type' => 'text', 'content' => ['title' => '', 'content' => '']],
        ];

        return $sections;
    }

    private function extractJson(string $text): ?array
    {
        // Try direct JSON parse first
        $decoded = json_decode($text, true);
        if ($decoded) return $decoded;

        // Try extracting from code blocks
        preg_match('/```(?:json)?\s*\n?(\{[\s\S]*?\})\n?\s*```/', $text, $m);
        if (!empty($m[1])) {
            $decoded = json_decode($m[1], true);
            if ($decoded) return $decoded;
        }

        // Try extracting raw JSON object
        preg_match('/\{[\s\S]*\}/', $text, $m);
        if (!empty($m[0])) {
            $decoded = json_decode($m[0], true);
            if ($decoded) return $decoded;
        }

        return null;
    }
}
