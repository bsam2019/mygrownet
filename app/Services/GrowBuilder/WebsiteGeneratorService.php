<?php

namespace App\Services\GrowBuilder;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Website Generator Service
 * Generates complete websites from text prompts using AI
 */
class WebsiteGeneratorService
{
    private AIContentService $aiService;
    private SectionTemplateService $templateService;
    
    public function __construct(AIContentService $aiService)
    {
        $this->aiService = $aiService;
        $this->templateService = new SectionTemplateService();
    }
    
    /**
     * Refine an existing generated website based on user feedback
     */
    public function refineWebsite(array $currentWebsite, string $refinementPrompt, int $userId): array
    {
        try {
            $analysis = $currentWebsite['analysis'] ?? [];
            $pages = $currentWebsite['pages'] ?? [];
            
            // Use AI to understand what the user wants to change
            $refinementIntent = $this->analyzeRefinement($refinementPrompt, $analysis, $pages);
            
            // Apply the refinement
            $updatedPages = $this->applyRefinement($pages, $refinementIntent, $analysis);
            
            return [
                'success' => true,
                'analysis' => $analysis,
                'pages' => $updatedPages,
                'settings' => $currentWebsite['settings'] ?? [],
                'refinement_applied' => $refinementIntent['description'] ?? 'Website updated',
            ];
        } catch (\Exception $e) {
            Log::error('Website refinement failed', [
                'error' => $e->getMessage(),
                'prompt' => $refinementPrompt,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Analyze what the user wants to refine
     */
    private function analyzeRefinement(string $prompt, array $analysis, array $pages): array
    {
        $systemPrompt = <<<PROMPT
You are analyzing a user's refinement request for their generated website.

Current website:
- Business: {$analysis['business_name']}
- Type: {$analysis['business_type']}
- Existing pages: {$this->formatPageList($pages)}

Determine what the user wants to change. Return ONLY valid JSON:
{
    "action": "add_page|modify_page|change_style|add_section|remove_page|general_improvement",
    "target": "page name or 'all' or 'site'",
    "details": {
        "page_type": "about|services|pricing|gallery|etc (for add_page)",
        "modifications": "description of changes (for modify_page)",
        "style_changes": {"colors": "pink and purple", "tone": "friendly"} (for change_style),
        "section_type": "testimonials|pricing|etc (for add_section)"
    },
    "description": "Human-readable description of what will be done"
}
PROMPT;

        $userPrompt = "User wants to refine their website. They said: \"{$prompt}\"\n\nWhat should be done?";
        
        try {
            $response = $this->aiService->smartChat($userPrompt, [
                'siteName' => $analysis['business_name'],
                'businessType' => $analysis['business_type'],
                'sitePages' => array_column($pages, 'name'),
            ]);
            
            // Parse response
            if (isset($response['data']) && is_array($response['data'])) {
                return $response['data'];
            }
            
            if (isset($response['message'])) {
                $cleaned = preg_replace('/```json?\s*/', '', $response['message']);
                $cleaned = preg_replace('/```\s*/', '', $cleaned);
                
                if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
                    $data = json_decode($matches[0], true);
                    if (is_array($data)) {
                        return $data;
                    }
                }
            }
            
            // Fallback: general improvement
            return [
                'action' => 'general_improvement',
                'target' => 'all',
                'details' => ['modifications' => $prompt],
                'description' => 'Applying general improvements',
            ];
        } catch (\Exception $e) {
            Log::error('Refinement analysis failed', ['error' => $e->getMessage()]);
            return [
                'action' => 'general_improvement',
                'target' => 'all',
                'details' => ['modifications' => $prompt],
                'description' => 'Applying requested changes',
            ];
        }
    }
    
    /**
     * Apply refinement to pages
     */
    private function applyRefinement(array $pages, array $intent, array $analysis): array
    {
        $action = $intent['action'] ?? 'general_improvement';
        $target = $intent['target'] ?? 'all';
        $details = $intent['details'] ?? [];
        
        switch ($action) {
            case 'add_page':
                // Add a new page
                $pageType = $details['page_type'] ?? 'about';
                $newPage = $this->generateInnerPage($pageType, $analysis);
                $pages[] = $newPage;
                break;
                
            case 'remove_page':
                // Remove a page
                $pages = array_filter($pages, function($page) use ($target) {
                    return strtolower($page['name']) !== strtolower($target);
                });
                $pages = array_values($pages); // Re-index
                break;
                
            case 'modify_page':
                // Regenerate specific page with modifications
                foreach ($pages as $index => $page) {
                    if (strtolower($page['name']) === strtolower($target) || $target === 'all') {
                        $pageType = strtolower($page['slug']);
                        $modifiedPage = $this->generateInnerPage($pageType, $analysis);
                        $pages[$index] = $modifiedPage;
                    }
                }
                break;
                
            case 'change_style':
                // Apply style changes to all pages
                $styleChanges = $details['style_changes'] ?? [];
                // This would update colors, fonts, etc. - simplified for now
                foreach ($pages as $index => $page) {
                    // Apply style changes to sections
                    if (isset($styleChanges['tone'])) {
                        // Regenerate with different tone
                        $pageType = strtolower($page['slug']);
                        $pages[$index] = $this->generateInnerPage($pageType, $analysis);
                    }
                }
                break;
                
            case 'add_section':
                // Add section to specific page or all pages
                $sectionType = $details['section_type'] ?? 'about';
                // This would add a section - simplified for now
                break;
                
            default:
                // General improvement - regenerate all pages
                $updatedPages = [];
                foreach ($pages as $page) {
                    $pageType = strtolower($page['slug']);
                    if ($page['is_home']) {
                        $updatedPages[] = $this->generateHomePage($analysis);
                    } else {
                        $updatedPages[] = $this->generateInnerPage($pageType, $analysis);
                    }
                }
                $pages = $updatedPages;
                break;
        }
        
        return $pages;
    }
    
    /**
     * Format page list for display
     */
    private function formatPageList(array $pages): string
    {
        $names = array_column($pages, 'name');
        return implode(', ', $names);
    }
    
    /**
     * Generate a complete website from a text prompt
     */
    public function generateWebsite(string $prompt, int $userId): array
    {
        try {
            // Step 1: Analyze the prompt to extract business information
            $analysis = $this->analyzePrompt($prompt);
            
            // Step 2: Generate pages based on business type
            $pages = $this->generatePages($analysis);
            
            // Step 3: Generate site settings
            $settings = $this->generateSettings($analysis);
            
            return [
                'success' => true,
                'analysis' => $analysis,
                'pages' => $pages,
                'settings' => $settings,
            ];
        } catch (\Exception $e) {
            Log::error('Website generation failed', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Analyze prompt using AI to extract structured business information
     */
    private function analyzePrompt(string $prompt): array
    {
        $systemPrompt = <<<PROMPT
You are a business analyst AI. Extract structured information from the business description.

Return ONLY valid JSON in this exact format:
{
    "business_name": "string",
    "business_type": "restaurant|salon|retail|consulting|fitness|education|healthcare|church|tutor|beauty|technology|agriculture|services|other",
    "location": "string (city, country)",
    "services": ["service1", "service2", ...],
    "products": ["product1", "product2", ...],
    "hours": "string (operating hours)",
    "phone": "string (+260 format for Zambia)",
    "email": "string",
    "description": "string (2-3 sentence summary)",
    "target_audience": "string",
    "unique_selling_points": ["point1", "point2", ...],
    "suggested_pages": ["home", "about", "services", "contact", ...]
}

IMPORTANT:
- Extract all available information from the prompt
- Use "other" for business_type if it doesn't match the list
- Suggest 4-6 pages based on business type
- For Zambian businesses, use +260 phone format
- If information is missing, use reasonable defaults or empty strings
PROMPT;

        $userPrompt = "Analyze this business description and extract structured information:\n\n\"{$prompt}\"";
        
        try {
            $response = $this->aiService->smartChat($userPrompt, [
                'siteName' => 'New Website',
                'businessType' => 'business',
            ]);
            
            // If smartChat returns structured data
            if (isset($response['data']) && is_array($response['data'])) {
                return $response['data'];
            }
            
            // Otherwise, try to parse the message as JSON
            if (isset($response['message'])) {
                $cleaned = preg_replace('/```json?\s*/', '', $response['message']);
                $cleaned = preg_replace('/```\s*/', '', $cleaned);
                $cleaned = trim($cleaned);
                
                if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
                    $data = json_decode($matches[0], true);
                    if (is_array($data)) {
                        return $this->normalizeAnalysis($data);
                    }
                }
            }
            
            // Fallback: basic extraction
            return $this->extractBasicInfo($prompt);
        } catch (\Exception $e) {
            Log::error('Prompt analysis failed', ['error' => $e->getMessage()]);
            return $this->extractBasicInfo($prompt);
        }
    }
    
    /**
     * Normalize analysis data to ensure all required fields exist
     */
    private function normalizeAnalysis(array $data): array
    {
        return [
            'business_name' => $data['business_name'] ?? 'My Business',
            'business_type' => $data['business_type'] ?? 'services',
            'location' => $data['location'] ?? 'Zambia',
            'services' => $data['services'] ?? [],
            'products' => $data['products'] ?? [],
            'hours' => $data['hours'] ?? 'Monday - Friday, 8am - 5pm',
            'phone' => $data['phone'] ?? '+260 97X XXX XXX',
            'email' => $data['email'] ?? 'info@example.com',
            'description' => $data['description'] ?? '',
            'target_audience' => $data['target_audience'] ?? 'General public',
            'unique_selling_points' => $data['unique_selling_points'] ?? [],
            'suggested_pages' => $data['suggested_pages'] ?? ['home', 'about', 'services', 'contact'],
        ];
    }
    
    /**
     * Extract basic information from prompt (fallback method)
     */
    private function extractBasicInfo(string $prompt): array
    {
        // Simple keyword-based extraction
        $businessName = 'My Business';
        $businessType = 'services';
        
        // Try to find business name (look for quotes or "called X")
        if (preg_match('/called\s+([A-Z][A-Za-z\s]+)/i', $prompt, $matches)) {
            $businessName = trim($matches[1]);
        } elseif (preg_match('/"([^"]+)"/', $prompt, $matches)) {
            $businessName = trim($matches[1]);
        }
        
        // Detect business type
        $types = [
            'restaurant' => ['restaurant', 'cafe', 'food', 'dining', 'eatery'],
            'salon' => ['salon', 'hair', 'beauty', 'spa'],
            'church' => ['church', 'ministry', 'pastor', 'congregation'],
            'tutor' => ['tutor', 'tutoring', 'education', 'teaching', 'lessons'],
            'fitness' => ['gym', 'fitness', 'training', 'workout'],
            'healthcare' => ['clinic', 'hospital', 'medical', 'health', 'doctor'],
            'technology' => ['tech', 'software', 'web', 'developer', 'IT'],
        ];
        
        $lowerPrompt = strtolower($prompt);
        foreach ($types as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lowerPrompt, $keyword)) {
                    $businessType = $type;
                    break 2;
                }
            }
        }
        
        return [
            'business_name' => $businessName,
            'business_type' => $businessType,
            'location' => 'Zambia',
            'services' => [],
            'products' => [],
            'hours' => 'Monday - Friday, 8am - 5pm',
            'phone' => '+260 97X XXX XXX',
            'email' => 'info@example.com',
            'description' => substr($prompt, 0, 200),
            'target_audience' => 'General public',
            'unique_selling_points' => [],
            'suggested_pages' => ['home', 'about', 'services', 'contact'],
        ];
    }
    
    /**
     * Generate pages based on business analysis
     */
    private function generatePages(array $analysis): array
    {
        $pages = [];
        $suggestedPages = $analysis['suggested_pages'] ?? ['home', 'about', 'services', 'contact'];
        
        // Always include home page first
        if (!in_array('home', $suggestedPages)) {
            array_unshift($suggestedPages, 'home');
        }
        
        foreach ($suggestedPages as $pageType) {
            try {
                if ($pageType === 'home') {
                    $pages[] = $this->generateHomePage($analysis);
                } else {
                    $pages[] = $this->generateInnerPage($pageType, $analysis);
                }
            } catch (\Exception $e) {
                Log::error("Failed to generate {$pageType} page", ['error' => $e->getMessage()]);
                // Continue with other pages
            }
        }
        
        return $pages;
    }
    
    /**
     * Generate home page
     */
    private function generateHomePage(array $analysis): array
    {
        $businessName = $analysis['business_name'] ?? 'My Business';
        $businessType = $analysis['business_type'] ?? 'business';
        $location = $analysis['location'] ?? 'Zambia';
        $description = $analysis['description'] ?? "A {$businessType} in {$location}";
        
        // Generate sections using template (fallback if AI not configured)
        $sections = $this->generateHomePageSections($businessName, $businessType, $location, $description, $analysis);
        
        return [
            'name' => 'Home',
            'slug' => 'home',
            'is_home' => true,
            'title' => 'Home',
            'sections' => $sections,
        ];
    }
    
    /**
     * Generate home page sections with template
     */
    private function generateHomePageSections(string $businessName, string $businessType, string $location, string $description, array $analysis): array
    {
        $services = $analysis['services'] ?? [];
        $phone = $analysis['phone'] ?? '+260 XXX XXX XXX';
        $email = $analysis['email'] ?? 'info@' . strtolower(str_replace(' ', '', $businessName)) . '.com';
        
        return [
            [
                'type' => 'hero',
                'content' => [
                    'title' => "Welcome to {$businessName}",
                    'subtitle' => $description,
                    'buttonText' => 'Get Started',
                    'buttonLink' => '#contact',
                    'textPosition' => 'center',
                ],
                'style' => [
                    'backgroundColor' => '#1e40af',
                    'textColor' => '#ffffff',
                ],
            ],
            [
                'type' => 'about',
                'content' => [
                    'title' => "About {$businessName}",
                    'description' => "We are a leading {$businessType} based in {$location}. {$description}",
                    'imagePosition' => 'right',
                ],
                'style' => [
                    'backgroundColor' => '#ffffff',
                    'textColor' => '#111827',
                ],
            ],
            [
                'type' => 'services',
                'content' => [
                    'title' => 'Our Services',
                    'subtitle' => 'What we offer',
                    'items' => !empty($services) ? array_map(fn($service) => [
                        'title' => $service,
                        'description' => "Professional {$service} services tailored to your needs.",
                    ], array_slice($services, 0, 6)) : [
                        ['title' => 'Service 1', 'description' => 'Professional service description'],
                        ['title' => 'Service 2', 'description' => 'Professional service description'],
                        ['title' => 'Service 3', 'description' => 'Professional service description'],
                    ],
                ],
                'style' => [
                    'backgroundColor' => '#f9fafb',
                    'textColor' => '#111827',
                ],
            ],
            [
                'type' => 'cta',
                'content' => [
                    'title' => 'Ready to Get Started?',
                    'description' => "Contact {$businessName} today and let us help you achieve your goals.",
                    'buttonText' => 'Contact Us',
                    'buttonLink' => '#contact',
                ],
                'style' => [
                    'backgroundColor' => '#1e40af',
                    'textColor' => '#ffffff',
                ],
            ],
            [
                'type' => 'contact',
                'content' => [
                    'title' => 'Get in Touch',
                    'description' => "We'd love to hear from you. Contact us today!",
                    'showForm' => true,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $location,
                ],
                'style' => [
                    'backgroundColor' => '#ffffff',
                    'textColor' => '#111827',
                ],
            ],
        ];
    }
    
    /**
     * Generate inner page (about, services, contact, etc.)
     */
    private function generateInnerPage(string $pageType, array $analysis): array
    {
        $businessName = $analysis['business_name'] ?? 'My Business';
        $businessType = $analysis['business_type'] ?? 'business';
        $location = $analysis['location'] ?? 'Zambia';
        $description = $analysis['description'] ?? "A {$businessType} in {$location}";
        
        // Use AI to generate page content
        $pageData = $this->aiService->generatePage(
            $pageType,
            $businessName,
            $businessType,
            $description
        );
        
        return [
            'name' => ucfirst($pageType),
            'slug' => $pageType,
            'is_home' => false,
            'title' => $pageData['title'] ?? ucfirst($pageType),
            'sections' => $pageData['sections'] ?? [],
        ];
    }
    
    /**
     * Generate site settings
     */
    private function generateSettings(array $analysis): array
    {
        $businessName = $analysis['business_name'] ?? 'My Business';
        $businessType = $analysis['business_type'] ?? 'business';
        $location = $analysis['location'] ?? 'Zambia';
        $description = $analysis['description'] ?? "A {$businessType} in {$location}";
        
        return [
            'name' => $businessName,
            'description' => $description,
            'contact_info' => [
                'email' => $analysis['email'] ?? 'info@example.com',
                'phone' => $analysis['phone'] ?? '+260 XXX XXX XXX',
                'address' => $location,
            ],
            'business_hours' => [
                'hours' => $analysis['hours'] ?? 'Monday - Friday, 8am - 5pm',
            ],
            'seo_settings' => [
                'meta_title' => $businessName . ' - ' . ucfirst($businessType) . ' in ' . $location,
                'meta_description' => $description,
            ],
        ];
    }
}
