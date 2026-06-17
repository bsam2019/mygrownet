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
                    'title' => 'We Build Your Digital Success',
                    'subtitle' => 'Professional websites, powerful tools, and the support you need to grow your business online',
                    'buttonText' => 'Get Started Today',
                    'buttonLink' => '#contact',
                    'textPosition' => 'center',
                ],
                'style' => ['backgroundColor' => '#1e40af', 'textColor' => '#ffffff'],
                'aiHints' => 'Create a compelling, benefit-driven headline that grabs attention. Subtitle should expand the value proposition with specific benefits. Include a clear, action-oriented CTA button. Modern hero designs use bold typography, subtle gradients, and strong contrast. Layout options: centered (default), split-right, split-left, video, slideshow.',
                'imageRequirements' => [
                    'backgroundImage' => [
                        'width' => 1920,
                        'height' => 1080,
                        'aspectRatio' => 1.78, // 16:9 ratio - better for preserving image content
                        'minWidth' => 1280,
                        'maxSize' => 5242880, // 5MB
                        'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                        'description' => 'Full-width hero background image',
                    ],
                    'image' => [
                        'width' => 800,
                        'height' => 600,
                        'aspectRatio' => 1.33,
                        'minWidth' => 600,
                        'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                        'description' => 'Split layout side image',
                    ],
                    'slides' => [
                        'width' => 1920,
                        'height' => 1080,
                        'aspectRatio' => 1.78, // 16:9 ratio for slideshow images too
                        'minWidth' => 1280,
                        'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                        'description' => 'Slideshow images',
                    ],
                ],
            ],

            'page-header' => [
                'type' => 'page-header',
                'name' => 'Page Header',
                'description' => 'Title banner for inner pages',
                'category' => 'structure',
                'required' => ['title'],
                'optional' => ['subtitle', 'backgroundImage', 'textPosition'],
                'defaults' => ['title' => 'Page Title', 'subtitle' => 'Learn more about what we do and how we can help your business grow', 'textPosition' => 'center'],
                'style' => ['backgroundColor' => '#1e40af', 'textColor' => '#ffffff'],
                'aiHints' => 'Keep title concise and impactful. Include a descriptive subtitle that provides context and engages the reader. Use the site\'s primary color scheme for consistency.',
                'imageRequirements' => [
                    'backgroundImage' => [
                        'width' => 1920,
                        'height' => 600,
                        'aspectRatio' => 3.2, // 16:5 ratio - better for page headers with content
                        'minWidth' => 1280,
                        'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                        'description' => 'Page header background image',
                    ],
                ],
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
                    'title' => 'Our Story',
                    'description' => 'We are a passionate team dedicated to helping businesses thrive in the digital age. With years of experience and a commitment to excellence, we deliver solutions that drive real results for our clients across Zambia and beyond.',
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Write an authentic company story that connects emotionally. Focus on mission, values, unique selling points, and what makes this business special. Use professional but warm language. Include specific details about the team, experience, or impact. Layout options: image-right (default), image-left, image-top.',
                'imageRequirements' => [
                    'image' => [
                        'width' => 600,
                        'height' => 400,
                        'aspectRatio' => 1.5,
                        'minWidth' => 400,
                        'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                        'description' => 'About section image',
                    ],
                ],
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
                    'title' => 'What We Offer',
                    'columns' => 3,
                    'items' => [
                        ['icon' => 'chart', 'title' => 'Strategic Planning', 'description' => 'Data-driven strategies tailored to your business goals, helping you navigate market challenges and seize growth opportunities.'],
                        ['icon' => 'users', 'title' => 'Expert Consultation', 'description' => 'One-on-one guidance from industry experts who understand your unique challenges and deliver practical, actionable solutions.'],
                        ['icon' => 'cog', 'title' => 'Digital Solutions', 'description' => 'Cutting-edge technology and digital tools designed to streamline operations, enhance customer experience, and boost revenue.'],
                    ],
                ],
                'style' => ['backgroundColor' => '#f9fafb', 'textColor' => '#111827'],
                'aiHints' => 'Create 3-6 services with benefit-focused, action-oriented titles. Each service needs an icon, a compelling title that emphasizes value, and a description that explains the benefit — not just what it is, but why it matters. Use professional, polished language. Layout options: grid (default), list, cards-images, alternating.',
                'imageRequirements' => [
                    'items.image' => [
                        'width' => 500,
                        'height' => 400,
                        'aspectRatio' => 1.25, // 5:4 ratio - better for service cards
                        'minWidth' => 400,
                        'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                        'description' => 'Service card image',
                    ],
                ],
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
                        ['icon' => 'star', 'title' => 'Proven Track Record', 'description' => 'Years of experience delivering exceptional results for businesses across multiple industries in Zambia and beyond.'],
                        ['icon' => 'heart', 'title' => 'Client-First Approach', 'description' => 'Your success is our priority. We take time to understand your unique needs and craft solutions that truly work for you.'],
                        ['icon' => 'zap', 'title' => 'Fast & Reliable', 'description' => 'We move quickly without compromising quality. Our streamlined processes ensure timely delivery every single time.'],
                        ['icon' => 'shield', 'title' => 'Dedicated Support', 'description' => 'You are never alone. Our support team is always ready to help, guide, and ensure your business runs smoothly.'],
                    ],
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Focus on benefits, not just features. Each feature should clearly communicate value to the customer. Use professional, benefit-driven language. Layout options: grid (default), checklist, steps.',
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
                        ['name' => 'Mwila Banda', 'role' => 'CEO, Lusaka Tech Solutions', 'text' => 'Working with this team transformed our online presence completely. Our website traffic has increased by 200% and our customers love the new design. Highly recommended!', 'rating' => 5],
                        ['name' => 'Chanda Phiri', 'role' => 'Owner, Kitwe Fresh Mart', 'text' => 'The professionalism and attention to detail is outstanding. They took our vision and created something even better than we imagined. A true partner in our growth journey.', 'rating' => 5],
                        ['name' => 'Thandiwe Zulu', 'role' => 'Director, Ndola Community Health', 'text' => 'From the initial consultation to the final launch, the experience was seamless. Our new website has made it so much easier for patients to find and connect with us.', 'rating' => 5],
                    ],
                ],
                'style' => ['backgroundColor' => '#f9fafb', 'textColor' => '#111827'],
                'aiHints' => 'Create realistic, detailed testimonials with specific results and details. Use Zambian names (Banda, Phiri, Zulu, Mwale, Tembo, Mulenga, Banda). Include real-sounding company names and roles. Each testimonial should have a specific benefit or result mentioned. Layout options: grid (default), carousel, single, photos.',
                'imageRequirements' => [
                    'items.image' => [
                        'width' => 200,
                        'height' => 200,
                        'aspectRatio' => 1.0,
                        'minWidth' => 100,
                        'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                        'description' => 'Customer photo (square)',
                    ],
                ],
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
                    'title' => 'Choose Your Plan',
                    'plans' => [
                        ['name' => 'Starter', 'price' => 'K299/mo', 'description' => 'Perfect for small businesses getting started online', 'features' => "Professional website\n5 pages\nContact form\nMobile responsive\nBasic analytics", 'buttonText' => 'Get Started', 'popular' => false],
                        ['name' => 'Business', 'price' => 'K599/mo', 'description' => 'Ideal for growing businesses ready to scale', 'features' => "Everything in Starter\nUnlimited pages\nBlog integration\nSEO optimization\nSocial media integration\nPriority support", 'buttonText' => 'Get Started', 'popular' => true],
                        ['name' => 'Enterprise', 'price' => 'K1,299/mo', 'description' => 'For established businesses needing full solutions', 'features' => "Everything in Business\nCustom integrations\nE-commerce ready\nDedicated account manager\nAdvanced analytics\n24/7 phone support", 'buttonText' => 'Contact Us', 'popular' => false],
                    ],
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Create 2-4 pricing tiers with clear value progression. Use K (Kwacha) for Zambian businesses. Mark one plan as popular (recommended). Each plan should have a clear name, price with period, description of who it\'s for, and feature list as a string with newlines for bullet points. Layout options: cards (default), table, toggle.',
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
                    'title' => 'Get In Touch',
                    'description' => 'We would love to hear from you. Whether you have a question, need a quote, or want to discuss your project — reach out and let us help bring your vision to life.',
                    'email' => 'hello@example.com',
                    'phone' => '+260 97 123 4567',
                    'address' => 'Lusaka, Zambia',
                    'showForm' => true,
                    'textPosition' => 'center',
                ],
                'style' => ['backgroundColor' => '#ffffff', 'textColor' => '#111827'],
                'aiHints' => 'Create a welcoming contact section with a warm description. Use Zambian phone format (+260). Include business address. Make it easy for visitors to reach out. Layout options: stacked (default), side-by-side, with-map.',
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
                        ['name' => 'John Banda', 'role' => 'CEO & Founder', 'bio' => 'Visionary leader with over 15 years of experience in digital transformation and business strategy. Passionate about helping Zambian businesses thrive online.'],
                        ['name' => 'Mary Phiri', 'role' => 'Head of Design', 'bio' => 'Award-winning designer who brings creativity and precision to every project. Specializes in creating beautiful, user-friendly digital experiences.'],
                        ['name' => 'David Tembo', 'role' => 'Technical Lead', 'bio' => 'Full-stack developer with deep expertise in modern web technologies. Ensures every website is fast, secure, and built to last.'],
                        ['name' => 'Sarah Mulenga', 'role' => 'Client Success Manager', 'bio' => 'Dedicated to ensuring every client gets the support they need. With a background in customer experience, she makes sure your voice is always heard.'],
                    ],
                ],
                'style' => ['backgroundColor' => '#f9fafb', 'textColor' => '#111827'],
                'aiHints' => 'Create a professional team section with realistic Zambian names (Banda, Phiri, Tembo, Mulenga, Zulu, Mwale, Kasonde, Mwansa). Each member should have a role/title, and a bio that highlights their expertise, experience, and what they bring to the team. Layout options: grid (default), social, compact.',
                'imageRequirements' => [
                    'items.image' => [
                        'width' => 400,
                        'height' => 400,
                        'aspectRatio' => 1.0,
                        'minWidth' => 200,
                        'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                        'description' => 'Team member photo (square)',
                    ],
                ],
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
                    'title' => 'Ready to Transform Your Business?',
                    'description' => 'Let us help you build a powerful online presence that drives real results. Your digital success story starts here.',
                    'buttonText' => 'Start Your Journey',
                    'buttonLink' => '#contact',
                ],
                'style' => ['backgroundColor' => '#2563eb', 'textColor' => '#ffffff'],
                'aiHints' => 'Create urgency with compelling, benefit-driven language. The CTA should inspire action. Use strong, confident copy that motivates visitors to take the next step. Layout options: banner (default), split, minimal.',
                'imageRequirements' => [
                    'image' => [
                        'width' => 800,
                        'height' => 600,
                        'aspectRatio' => 1.33,
                        'minWidth' => 600,
                        'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                        'description' => 'CTA split layout image',
                    ],
                ],
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
                'imageRequirements' => [
                    'images' => [
                        'width' => 1000,
                        'height' => 800,
                        'aspectRatio' => 1.25,
                        'minWidth' => 600,
                        'formats' => ['jpg', 'jpeg', 'png', 'webp'],
                        'description' => 'Gallery image',
                    ],
                ],
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

    /**
     * Get image requirements for a specific section type and field
     */
    public static function getImageRequirements(string $type, string $field): ?array
    {
        $template = self::getTemplate($type);
        if (!$template || !isset($template['imageRequirements'])) {
            return null;
        }

        // Handle nested fields like 'items.image' or 'slides'
        $requirements = $template['imageRequirements'];
        
        // Direct match
        if (isset($requirements[$field])) {
            return $requirements[$field];
        }

        // Check for pattern match (e.g., 'items.0.image' matches 'items.image')
        foreach ($requirements as $pattern => $req) {
            if (str_starts_with($field, str_replace('.', '.', $pattern))) {
                return $req;
            }
        }

        return null;
    }

    /**
     * Get all image requirements for a section type
     */
    public static function getAllImageRequirements(string $type): array
    {
        $template = self::getTemplate($type);
        return $template['imageRequirements'] ?? [];
    }
}
