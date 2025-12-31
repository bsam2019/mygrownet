<?php

namespace App\Services\GrowBuilder;

/**
 * Section Template Service
 * Provides section schemas and templates for AI content generation
 * 
 * This mirrors the frontend sectionTemplates.ts to ensure AI generates
 * valid content that matches what the section components expect.
 */
class SectionTemplateService
{
    /**
     * Get all section templates with their schemas
     */
    public static function getTemplates(): array
    {
        return [
            'hero' => [
                'type' => 'hero',
                'name' => 'Hero',
                'description' => 'Full-screen impact section with headline, subtitle, and call-to-action',
                'category' => 'structure',
                'required' => ['title'],
                'optional' => ['subtitle', 'buttonText', 'buttonLink', 'secondaryButtonText', 'secondaryButtonLink', 'backgroundImage', 'textPosition', 'layout', 'image', 'slides', 'autoPlay', 'slideInterval'],
                'layouts' => ['centered', 'split-right', 'split-left', 'video', 'slideshow'],
                'defaults' => [
                    'layout' => 'centered',
                    'title' => 'Welcome to Our Business',
                    'subtitle' => 'We help you grow and succeed',
                    'buttonText' => 'Get Started',
                    'buttonLink' => '#contact',
                    'textPosition' => 'center',
                ],
                'style' => ['backgroundColor' => '#1e40af', 'textColor' => '#ffffff'],
                'aiHints' => 'Create compelling, action-oriented headline. Subtitle expands value proposition. Include clear CTA. Layout options: centered (default), split-right, split-left, video, slideshow.',
            ],

            'page-header' => [
                'type' => 'page-header',
                'name' => 'Page Header',
                'description' => 'Title banner for inner pages',
                'category' => 'structure',
                'required' => ['title'],
                'optional' => ['subtitle', 'backgroundImage', 'textPosition'],
                'defaults' => ['title' => 'Page Title', 'textPosition' => 'center'],
                'style' => ['backgroundColor' => '#1e40af', 'textColor' => '#ffffff'],
                'aiHints' => 'Keep title concise. Subtitle optional but provides context.',
            ],

            'about' => [
                'type' => 'about',
                'name' => 'About',
                'description' => 'Company story with text and optional image',
                'category' => 'content',
                'required' => ['title', 'description'],
                'optional' => ['image', 'layout'],
                'layouts' => ['image-right', 'image-left', 'image-top'],
                'defaults' => [
                    'layout' => 'image-right',
                    'title' => 'About Us',
                    'description' => 'Tell your company story here.',
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Write authentic company story. Focus on mission, values, unique selling points. Layout options: image-right (default), image-left, image-top.',
            ],

            'services' => [
                'type' => 'services',
                'name' => 'Services',
                'description' => 'Grid of service cards with icons',
                'category' => 'content',
                'required' => ['title', 'items'],
                'optional' => ['subtitle', 'textPosition', 'layout', 'columns'],
                'layouts' => ['grid', 'list', 'cards-images', 'alternating'],
                'itemFields' => ['icon', 'title', 'description', 'image', 'link'],
                'itemCount' => ['min' => 3, 'max' => 6, 'default' => 3],
                'defaults' => [
                    'layout' => 'grid',
                    'title' => 'Our Services',
                    'columns' => 3,
                    'items' => [
                        ['icon' => 'chart', 'title' => 'Service 1', 'description' => 'Description'],
                        ['icon' => 'users', 'title' => 'Service 2', 'description' => 'Description'],
                        ['icon' => 'cog', 'title' => 'Service 3', 'description' => 'Description'],
                    ],
                ],
                'style' => ['backgroundColor' => '#f9fafb', 'textColor' => '#111827'],
                'aiHints' => 'Create 3-6 services with benefit-focused titles. Each needs icon, title, description. Layout options: grid (default), list, cards-images, alternating.',
            ],

            'features' => [
                'type' => 'features',
                'name' => 'Features',
                'description' => 'List of features or benefits',
                'category' => 'content',
                'required' => ['title', 'items'],
                'optional' => ['subtitle', 'textPosition', 'layout'],
                'layouts' => ['grid', 'checklist', 'steps'],
                'itemFields' => ['icon', 'title', 'description'],
                'itemCount' => ['min' => 3, 'max' => 8, 'default' => 4],
                'defaults' => [
                    'layout' => 'grid',
                    'title' => 'Why Choose Us',
                    'items' => [
                        ['icon' => 'check', 'title' => 'Feature 1', 'description' => 'Benefit description'],
                        ['icon' => 'check', 'title' => 'Feature 2', 'description' => 'Benefit description'],
                    ],
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Focus on benefits, not just features. Use action-oriented language. Layout options: grid (default), checklist, steps.',
            ],

            'testimonials' => [
                'type' => 'testimonials',
                'name' => 'Testimonials',
                'description' => 'Customer reviews and testimonials',
                'category' => 'social-proof',
                'required' => ['title', 'items'],
                'optional' => ['subtitle', 'textPosition', 'layout', 'autoPlay'],
                'layouts' => ['grid', 'carousel', 'single', 'photos'],
                'itemFields' => ['name', 'role', 'text', 'rating', 'image'],
                'itemCount' => ['min' => 2, 'max' => 6, 'default' => 3],
                'defaults' => [
                    'layout' => 'grid',
                    'title' => 'What Our Clients Say',
                    'items' => [
                        ['name' => 'John Banda', 'role' => 'CEO', 'text' => 'Great service!', 'rating' => 5],
                    ],
                ],
                'style' => ['backgroundColor' => '#f9fafb', 'textColor' => '#111827'],
                'aiHints' => 'Create realistic testimonials with specific details. Use Zambian names. Include role/company. Layout options: grid (default), carousel, single, photos.',
            ],

            'pricing' => [
                'type' => 'pricing',
                'name' => 'Pricing',
                'description' => 'Pricing plans and packages',
                'category' => 'commerce',
                'required' => ['title', 'plans'],
                'optional' => ['subtitle', 'textPosition', 'layout'],
                'layouts' => ['cards', 'table', 'toggle'],
                'itemFields' => ['name', 'price', 'yearlyPrice', 'description', 'features', 'buttonText', 'buttonLink', 'popular'],
                'itemCount' => ['min' => 2, 'max' => 4, 'default' => 3],
                'defaults' => [
                    'layout' => 'cards',
                    'title' => 'Pricing Plans',
                    'plans' => [
                        ['name' => 'Basic', 'price' => 'K99/mo', 'features' => "Feature 1\nFeature 2", 'buttonText' => 'Get Started'],
                        ['name' => 'Pro', 'price' => 'K199/mo', 'features' => "All Basic\nFeature 3", 'popular' => true, 'buttonText' => 'Get Started'],
                    ],
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Create 2-4 pricing tiers. Use K (Kwacha) for Zambian businesses. Mark one as popular. Layout options: cards (default), table, toggle.',
            ],

            'contact' => [
                'type' => 'contact',
                'name' => 'Contact',
                'description' => 'Contact form with business information',
                'category' => 'forms',
                'required' => ['title'],
                'optional' => ['description', 'email', 'phone', 'address', 'showForm', 'textPosition', 'layout', 'mapEmbedUrl'],
                'layouts' => ['stacked', 'side-by-side', 'with-map'],
                'defaults' => [
                    'layout' => 'stacked',
                    'title' => 'Contact Us',
                    'email' => 'hello@example.com',
                    'phone' => '+260 97 123 4567',
                    'showForm' => true,
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Include realistic contact details. Use +260 format for Zambian phones. Layout options: stacked (default), side-by-side, with-map.',
            ],

            'faq' => [
                'type' => 'faq',
                'name' => 'FAQ',
                'description' => 'Frequently asked questions accordion',
                'category' => 'content',
                'required' => ['title', 'items'],
                'optional' => ['textPosition', 'layout'],
                'layouts' => ['accordion', 'two-column', 'list'],
                'itemFields' => ['question', 'answer'],
                'itemCount' => ['min' => 3, 'max' => 10, 'default' => 5],
                'defaults' => [
                    'layout' => 'accordion',
                    'title' => 'Frequently Asked Questions',
                    'items' => [
                        ['question' => 'Question 1?', 'answer' => 'Answer here.'],
                    ],
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Create relevant FAQs for the business type. Answers should be helpful and concise. Layout options: accordion (default), two-column, list.',
            ],

            'team' => [
                'type' => 'team',
                'name' => 'Team',
                'description' => 'Team member profiles',
                'category' => 'content',
                'required' => ['title', 'items'],
                'optional' => ['textPosition', 'layout'],
                'layouts' => ['grid', 'social', 'compact'],
                'itemFields' => ['name', 'role', 'bio', 'image', 'linkedin', 'twitter', 'email'],
                'itemCount' => ['min' => 2, 'max' => 8, 'default' => 4],
                'defaults' => [
                    'layout' => 'grid',
                    'title' => 'Meet Our Team',
                    'items' => [
                        ['name' => 'John Banda', 'role' => 'CEO', 'bio' => 'Founder with 10+ years experience'],
                    ],
                ],
                'style' => ['backgroundColor' => '#f9fafb', 'textColor' => '#111827'],
                'aiHints' => 'Use realistic Zambian names. Include role and brief bio. Layout options: grid (default), social, compact.',
            ],

            'stats' => [
                'type' => 'stats',
                'name' => 'Stats',
                'description' => 'Key statistics and numbers',
                'category' => 'social-proof',
                'required' => ['items'],
                'optional' => ['title', 'textPosition', 'layout'],
                'layouts' => ['row', 'grid', 'icons'],
                'itemFields' => ['icon', 'value', 'label'],
                'itemCount' => ['min' => 3, 'max' => 4, 'default' => 4],
                'defaults' => [
                    'layout' => 'row',
                    'title' => 'Our Impact',
                    'items' => [
                        ['value' => '500+', 'label' => 'Happy Clients'],
                        ['value' => '10+', 'label' => 'Years Experience'],
                    ],
                ],
                'style' => ['backgroundColor' => '#2563eb', 'textColor' => '#ffffff'],
                'aiHints' => 'Use impressive but realistic numbers. 3-4 stats max. Include + or K/M for large numbers. Layout options: row (default), grid, icons.',
            ],

            'cta' => [
                'type' => 'cta',
                'name' => 'Call to Action',
                'description' => 'Call-to-action banner',
                'category' => 'structure',
                'required' => ['title', 'buttonText'],
                'optional' => ['description', 'buttonLink', 'textPosition', 'layout', 'image'],
                'layouts' => ['banner', 'split', 'minimal'],
                'defaults' => [
                    'layout' => 'banner',
                    'title' => 'Ready to Get Started?',
                    'description' => 'Contact us today',
                    'buttonText' => 'Contact Us',
                    'buttonLink' => '#contact',
                ],
                'style' => ['backgroundColor' => '#2563eb', 'textColor' => '#ffffff'],
                'aiHints' => 'Create urgency with action-oriented language. Clear, compelling CTA. Layout options: banner (default), split, minimal.',
            ],

            'gallery' => [
                'type' => 'gallery',
                'name' => 'Gallery',
                'description' => 'Image gallery grid',
                'category' => 'media',
                'required' => ['title'],
                'optional' => ['images', 'textPosition', 'layout', 'columns'],
                'layouts' => ['grid', 'masonry', 'lightbox'],
                'defaults' => ['layout' => 'grid', 'title' => 'Our Gallery', 'columns' => 4, 'images' => []],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Gallery requires user to upload images. AI suggests title only. Layout options: grid (default), masonry, lightbox.',
            ],

            'member-cta' => [
                'type' => 'member-cta',
                'name' => 'Member Signup',
                'description' => 'Member registration call-to-action',
                'category' => 'structure',
                'required' => ['title'],
                'optional' => ['subtitle', 'description', 'benefits', 'registerText', 'showLoginLink'],
                'defaults' => [
                    'title' => 'Join Our Community',
                    'benefits' => ['Exclusive content', 'Member discounts'],
                    'registerText' => 'Sign Up Now',
                    'showLoginLink' => true,
                ],
                'style' => ['backgroundColor' => '#1e40af', 'textColor' => '#ffffff'],
                'aiHints' => 'Highlight membership benefits. Create compelling reasons to join.',
            ],

            'blog' => [
                'type' => 'blog',
                'name' => 'Blog',
                'description' => 'Latest blog posts display',
                'category' => 'content',
                'required' => ['title'],
                'optional' => ['description', 'layout', 'columns', 'postsCount', 'showImage', 'showDate', 'showExcerpt'],
                'defaults' => [
                    'title' => 'Latest News',
                    'layout' => 'grid',
                    'columns' => 3,
                    'postsCount' => 3,
                    'showImage' => true,
                    'showDate' => true,
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Blog displays posts from dashboard. AI configures display options.',
            ],

            'products' => [
                'type' => 'products',
                'name' => 'Products',
                'description' => 'Product catalog grid',
                'category' => 'commerce',
                'required' => ['title'],
                'optional' => ['subtitle', 'columns', 'limit', 'showCategory', 'showViewAll', 'featuredOnly'],
                'defaults' => [
                    'title' => 'Our Products',
                    'columns' => 3,
                    'limit' => 6,
                    'showCategory' => true,
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Products displays items from dashboard. AI configures display options.',
            ],

            'product-search' => [
                'type' => 'product-search',
                'name' => 'Product Search',
                'description' => 'Search bar with category filters for products',
                'category' => 'commerce',
                'required' => [],
                'optional' => ['placeholder', 'showCategories', 'showSort'],
                'defaults' => [
                    'placeholder' => 'Search products...',
                    'showCategories' => true,
                    'showSort' => true,
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Product search widget for filtering products. Configure placeholder and filter options.',
            ],

            'video' => [
                'type' => 'video',
                'name' => 'Video',
                'description' => 'Embedded video section',
                'category' => 'media',
                'required' => ['title'],
                'optional' => ['description', 'videoUrl'],
                'defaults' => ['title' => 'Watch Our Story'],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Video requires user to provide YouTube/Vimeo URL. AI suggests title.',
            ],

            'map' => [
                'type' => 'map',
                'name' => 'Map',
                'description' => 'Google Maps embed with address',
                'category' => 'forms',
                'required' => ['title'],
                'optional' => ['embedUrl', 'address', 'showAddress'],
                'defaults' => ['title' => 'Find Us', 'showAddress' => true],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Map requires Google Maps embed URL. AI suggests title and address format.',
            ],

            'text' => [
                'type' => 'text',
                'name' => 'Text Block',
                'description' => 'Rich text content block',
                'category' => 'content',
                'required' => ['content'],
                'optional' => [],
                'defaults' => ['content' => '<p>Enter your text content here...</p>'],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Create well-formatted text. Can include headings, lists, paragraphs.',
            ],

            'divider' => [
                'type' => 'divider',
                'name' => 'Divider',
                'description' => 'Visual separator',
                'category' => 'structure',
                'required' => [],
                'optional' => ['style', 'height'],
                'defaults' => ['style' => 'line', 'height' => 40],
                'style' => ['backgroundColor' => '#ffffff'],
                'aiHints' => 'Simple visual separator. Style: line, dots, or space.',
            ],
        ];
    }

    /**
     * Get template for a specific section type
     */
    public static function getTemplate(string $type): ?array
    {
        return self::getTemplates()[$type] ?? null;
    }

    /**
     * Get section types by category
     */
    public static function getByCategory(): array
    {
        $templates = self::getTemplates();
        $byCategory = [];

        foreach ($templates as $template) {
            $category = $template['category'];
            if (!isset($byCategory[$category])) {
                $byCategory[$category] = [];
            }
            $byCategory[$category][] = $template;
        }

        return $byCategory;
    }

    /**
     * Generate schema documentation for AI prompt
     */
    public static function generateAISchemaDoc(): string
    {
        $templates = self::getTemplates();
        $doc = "SECTION SCHEMAS (use these exact field names):\n\n";

        foreach ($templates as $type => $template) {
            $required = implode(', ', $template['required']);
            $optional = implode(', ', $template['optional']);
            
            $doc .= "• {$type}: {$template['description']}\n";
            $doc .= "  Required: {$required}\n";
            if ($optional) {
                $doc .= "  Optional: {$optional}\n";
            }
            if (isset($template['layouts'])) {
                $layouts = implode(', ', $template['layouts']);
                $doc .= "  Layouts: {$layouts}\n";
            }
            if (isset($template['itemFields'])) {
                $itemFields = implode(', ', $template['itemFields']);
                $doc .= "  Item fields: {$itemFields}\n";
            }
            if (isset($template['itemCount'])) {
                $doc .= "  Items: {$template['itemCount']['min']}-{$template['itemCount']['max']} (default: {$template['itemCount']['default']})\n";
            }
            $doc .= "  Hint: {$template['aiHints']}\n\n";
        }

        return $doc;
    }

    /**
     * Validate section content against schema
     */
    public static function validateContent(string $type, array $content): array
    {
        $template = self::getTemplate($type);
        if (!$template) {
            return ['valid' => false, 'errors' => ["Unknown section type: {$type}"]];
        }

        $errors = [];
        
        // Check required fields
        foreach ($template['required'] as $field) {
            if (!isset($content[$field]) || empty($content[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Validate item counts if applicable
        if (isset($template['itemCount']) && isset($content['items'])) {
            $count = count($content['items']);
            if ($count < $template['itemCount']['min']) {
                $errors[] = "Too few items: {$count} (minimum: {$template['itemCount']['min']})";
            }
            if ($count > $template['itemCount']['max']) {
                $errors[] = "Too many items: {$count} (maximum: {$template['itemCount']['max']})";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get default content for a section type
     */
    public static function getDefaults(string $type): array
    {
        $template = self::getTemplate($type);
        return $template['defaults'] ?? [];
    }

    /**
     * Get default style for a section type
     */
    public static function getDefaultStyle(string $type): array
    {
        $template = self::getTemplate($type);
        return $template['style'] ?? ['backgroundColor' => '#ffffff', 'textColor' => '#111827'];
    }
}
