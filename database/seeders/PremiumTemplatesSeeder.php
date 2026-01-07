<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use Illuminate\Database\Seeder;

class PremiumTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $this->createTechStartupTemplate();
        $this->createLuxuryRestaurantTemplate();
        $this->createCreativeAgencyTemplate();
        $this->createFitnessStudioTemplate();
        $this->createEcommerceStoreTemplate();
    }

    private function createTechStartupTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'tech-startup-pro'],
            [
                'name' => 'Tech Startup Pro',
                'description' => 'Modern SaaS landing page with glassmorphism, gradient meshes, and smooth animations. Perfect for tech startups and software companies.',
                'industry' => 'technology',
                'thumbnail' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=400&q=80',
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 1,
                'theme' => [
                    'primaryColor' => '#6366f1',
                    'secondaryColor' => '#8b5cf6',
                    'accentColor' => '#ec4899',
                    'backgroundColor' => '#0f172a',
                    'textColor' => '#f8fafc',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'TechFlow',
                        'sticky' => true,
                        'transparent' => true,
                        'showCta' => true,
                        'ctaText' => 'Start Free Trial',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 TechFlow. All rights reserved.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page - Tech Startup with Glassmorphism
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => [
                'sections' => [
                    // Hero - Animated with Product Screenshot
                    [
                        'type' => 'hero',
                        'content' => [
                            'layout' => 'split-right',
                            'title' => 'Ship Products 10x Faster with AI',
                            'subtitle' => 'The all-in-one platform for modern development teams. Build, deploy, and scale applications with intelligent automation.',
                            'buttonText' => 'Start Free Trial',
                            'buttonLink' => '/contact',
                            'secondaryButtonText' => 'Book a Demo',
                            'secondaryButtonLink' => '/contact',
                            'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80',
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#0f172a',
                            'gradientTo' => '#1e1b4b',
                            'gradientDirection' => 'to-br',
                            'textColor' => '#ffffff',
                            'minHeight' => 650,
                            'paddingTop' => 60,
                            'paddingBottom' => 60,
                        ],
                    ],
                    // Logo Cloud with Animation
                    [
                        'type' => 'logo-cloud',
                        'content' => [
                            'title' => 'Trusted by 10,000+ Development Teams',
                            'subtitle' => 'From startups to Fortune 500 companies',
                            'logos' => [
                                ['name' => 'Airbnb', 'image' => ''],
                                ['name' => 'Stripe', 'image' => ''],
                                ['name' => 'Shopify', 'image' => ''],
                                ['name' => 'Netflix', 'image' => ''],
                                ['name' => 'Uber', 'image' => ''],
                                ['name' => 'Spotify', 'image' => ''],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#0f172a',
                            'textColor' => '#94a3b8',
                            'paddingTop' => 60,
                            'paddingBottom' => 60,
                        ],
                    ],
                    // Problem/Solution Section
                    [
                        'type' => 'about',
                        'content' => [
                            'title' => 'Stop Wasting Time on DevOps',
                            'description' => 'Traditional deployment pipelines are slow, complex, and error-prone. TechFlow automates your entire workflow - from code commit to production deployment - in minutes, not hours. Focus on building features, not managing infrastructure.',
                            'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80',
                        ],
                        'style' => [
                            'backgroundColor' => '#1e293b',
                            'textColor' => '#f8fafc',
                            'paddingTop' => 100,
                            'paddingBottom' => 100,
                        ],
                    ],
                    // Features with Real Tech Features
                    [
                        'type' => 'features',
                        'content' => [
                            'title' => 'Everything You Need to Ship Faster',
                            'subtitle' => 'Built for modern development workflows',
                            'layout' => 'grid',
                            'items' => [
                                [
                                    'icon' => 'code',
                                    'title' => 'Git Integration',
                                    'description' => 'Connect GitHub, GitLab, or Bitbucket. Auto-deploy on every push with preview environments for pull requests.',
                                ],
                                [
                                    'icon' => 'server',
                                    'title' => 'Global Edge Network',
                                    'description' => 'Deploy to 200+ locations worldwide. Sub-50ms response times with automatic CDN and caching.',
                                ],
                                [
                                    'icon' => 'database',
                                    'title' => 'Managed Databases',
                                    'description' => 'PostgreSQL, MySQL, Redis, and MongoDB. Automatic backups, scaling, and zero-downtime migrations.',
                                ],
                                [
                                    'icon' => 'shield',
                                    'title' => 'Enterprise Security',
                                    'description' => 'SOC 2 Type II certified. DDoS protection, WAF, SSL certificates, and automatic security patches.',
                                ],
                                [
                                    'icon' => 'chart',
                                    'title' => 'Real-time Analytics',
                                    'description' => 'Monitor performance, errors, and user behavior. Custom dashboards and alerting built-in.',
                                ],
                                [
                                    'icon' => 'users',
                                    'title' => 'Team Collaboration',
                                    'description' => 'Role-based access control, audit logs, and seamless team workflows. Integrate with Slack and Teams.',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#0f172a',
                            'textColor' => '#f8fafc',
                            'paddingTop' => 100,
                            'paddingBottom' => 100,
                        ],
                    ],
                    // How It Works - Step by Step
                    [
                        'type' => 'timeline',
                        'content' => [
                            'title' => 'Deploy in 3 Simple Steps',
                            'items' => [
                                [
                                    'step' => '1',
                                    'title' => 'Connect Your Repository',
                                    'description' => 'Link your Git repository in seconds. We automatically detect your framework and configure optimal settings.',
                                ],
                                [
                                    'step' => '2',
                                    'title' => 'Configure & Customize',
                                    'description' => 'Set environment variables, choose your region, and configure build settings. Or use our smart defaults.',
                                ],
                                [
                                    'step' => '3',
                                    'title' => 'Deploy & Scale',
                                    'description' => 'Push your code and watch it deploy automatically. Scale instantly with zero configuration.',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#1e293b',
                            'textColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Integration Showcase
                    [
                        'type' => 'services',
                        'content' => [
                            'title' => 'Integrates with Your Stack',
                            'subtitle' => 'Works with the tools you already use',
                            'layout' => 'grid',
                            'items' => [
                                ['title' => 'React & Next.js', 'description' => 'Optimized for modern React frameworks'],
                                ['title' => 'Vue & Nuxt', 'description' => 'First-class Vue.js support'],
                                ['title' => 'Node.js & Express', 'description' => 'Deploy any Node.js application'],
                                ['title' => 'Python & Django', 'description' => 'Full Python ecosystem support'],
                                ['title' => 'Docker Containers', 'description' => 'Bring your own Dockerfile'],
                                ['title' => 'Static Sites', 'description' => 'Hugo, Jekyll, Gatsby, and more'],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#0f172a',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Stats Section - Real Metrics
                    [
                        'type' => 'stats',
                        'content' => [
                            'title' => 'Powering the Modern Web',
                            'items' => [
                                ['value' => '10K+', 'label' => 'Companies'],
                                ['value' => '99.99%', 'label' => 'Uptime SLA'],
                                ['value' => '2.5B+', 'label' => 'Requests/Day'],
                                ['value' => '<100ms', 'label' => 'Avg Response'],
                            ],
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#6366f1',
                            'gradientTo' => '#8b5cf6',
                            'gradientDirection' => 'to-r',
                            'textColor' => '#ffffff',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // FAQ Section
                    [
                        'type' => 'faq',
                        'content' => [
                            'title' => 'Frequently Asked Questions',
                            'items' => [
                                [
                                    'question' => 'How long does it take to deploy?',
                                    'answer' => 'Most deployments complete in under 2 minutes. We automatically optimize your build process and cache dependencies.',
                                ],
                                [
                                    'question' => 'Can I migrate from AWS/Heroku?',
                                    'answer' => 'Yes! We provide migration guides and tools for all major platforms. Most migrations take less than an hour.',
                                ],
                                [
                                    'question' => 'What about database backups?',
                                    'answer' => 'Automatic daily backups with point-in-time recovery. You can restore to any point in the last 30 days.',
                                ],
                                [
                                    'question' => 'Do you support custom domains?',
                                    'answer' => 'Yes, unlimited custom domains with automatic SSL certificates. DNS configuration is handled automatically.',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#1e293b',
                            'textColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Final CTA
                    [
                        'type' => 'cta',
                        'content' => [
                            'title' => 'Start Shipping Faster Today',
                            'description' => 'Join 10,000+ developers who trust TechFlow. No credit card required.',
                            'buttonText' => 'Start Free Trial',
                            'buttonLink' => '/contact',
                            'secondaryButtonText' => 'Talk to Sales',
                            'secondaryButtonLink' => '/contact',
                        ],
                        'style' => [
                            'backgroundColor' => '#0f172a',
                            'textColor' => '#ffffff',
                            'paddingTop' => 100,
                            'paddingBottom' => 100,
                        ],
                    ],
                ],
            ],
        ]);

        // Features Page - Detailed feature breakdown
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Features',
            'slug' => 'features',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => [
                'sections' => [
                    // Hero
                    [
                        'type' => 'hero',
                        'content' => [
                            'layout' => 'centered',
                            'title' => 'Powerful Features for Modern Teams',
                            'subtitle' => 'Everything you need to build, deploy, and scale your applications with confidence.',
                            'buttonText' => 'Start Free Trial',
                            'buttonLink' => '/contact',
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#0f172a',
                            'gradientTo' => '#1e1b4b',
                            'gradientDirection' => 'to-br',
                            'textColor' => '#ffffff',
                            'minHeight' => 400,
                        ],
                    ],
                    // Core Features Grid
                    [
                        'type' => 'features',
                        'content' => [
                            'title' => 'Core Platform Features',
                            'subtitle' => 'Built for speed, security, and scale',
                            'layout' => 'grid',
                            'items' => [
                                [
                                    'icon' => 'rocket',
                                    'title' => 'Instant Deployments',
                                    'description' => 'Deploy in seconds with zero-config. Automatic builds, previews for every PR, and instant rollbacks.',
                                ],
                                [
                                    'icon' => 'globe',
                                    'title' => 'Global CDN',
                                    'description' => '200+ edge locations worldwide. Your content delivered in milliseconds, anywhere.',
                                ],
                                [
                                    'icon' => 'lock',
                                    'title' => 'Enterprise Security',
                                    'description' => 'SOC 2 certified, DDoS protection, WAF, and automatic SSL. Your data is always safe.',
                                ],
                                [
                                    'icon' => 'chart',
                                    'title' => 'Real-time Analytics',
                                    'description' => 'Monitor performance, track errors, and understand user behavior with built-in analytics.',
                                ],
                                [
                                    'icon' => 'database',
                                    'title' => 'Managed Databases',
                                    'description' => 'PostgreSQL, MySQL, Redis with automatic backups, scaling, and zero-downtime migrations.',
                                ],
                                [
                                    'icon' => 'code',
                                    'title' => 'Git Integration',
                                    'description' => 'Connect GitHub, GitLab, or Bitbucket. Auto-deploy on push with preview environments.',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#1e293b',
                            'textColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Developer Experience
                    [
                        'type' => 'about',
                        'content' => [
                            'title' => 'Built for Developer Experience',
                            'description' => 'We obsess over the details so you can focus on building. From our CLI to our dashboard, every interaction is designed to be fast, intuitive, and delightful. No more fighting with infrastructure.',
                            'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
                        ],
                        'style' => [
                            'backgroundColor' => '#0f172a',
                            'textColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // CTA
                    [
                        'type' => 'cta',
                        'content' => [
                            'title' => 'Ready to Get Started?',
                            'description' => 'Join thousands of developers shipping faster with TechFlow.',
                            'buttonText' => 'Start Free Trial',
                            'buttonLink' => '/contact',
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#6366f1',
                            'gradientTo' => '#8b5cf6',
                            'textColor' => '#ffffff',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                ],
            ],
        ]);

        // Pricing Page - Pricing tiers
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Pricing',
            'slug' => 'pricing',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => [
                'sections' => [
                    // Hero
                    [
                        'type' => 'hero',
                        'content' => [
                            'layout' => 'centered',
                            'title' => 'Simple, Transparent Pricing',
                            'subtitle' => 'Start free, scale as you grow. No hidden fees, no surprises.',
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#0f172a',
                            'gradientTo' => '#1e1b4b',
                            'textColor' => '#ffffff',
                            'minHeight' => 350,
                        ],
                    ],
                    // Pricing Cards
                    [
                        'type' => 'pricing',
                        'content' => [
                            'title' => 'Choose Your Plan',
                            'plans' => [
                                [
                                    'name' => 'Hobby',
                                    'price' => 'Free',
                                    'period' => 'forever',
                                    'description' => 'Perfect for side projects',
                                    'features' => [
                                        '3 projects',
                                        '100GB bandwidth',
                                        'Automatic HTTPS',
                                        'Community support',
                                    ],
                                    'buttonText' => 'Get Started',
                                    'buttonLink' => '/contact',
                                ],
                                [
                                    'name' => 'Pro',
                                    'price' => '$20',
                                    'period' => '/month',
                                    'description' => 'For professional developers',
                                    'features' => [
                                        'Unlimited projects',
                                        '1TB bandwidth',
                                        'Preview deployments',
                                        'Priority support',
                                        'Team collaboration',
                                        'Custom domains',
                                    ],
                                    'buttonText' => 'Start Free Trial',
                                    'buttonLink' => '/contact',
                                    'highlighted' => true,
                                ],
                                [
                                    'name' => 'Enterprise',
                                    'price' => 'Custom',
                                    'period' => '',
                                    'description' => 'For large organizations',
                                    'features' => [
                                        'Everything in Pro',
                                        'Unlimited bandwidth',
                                        'SLA guarantee',
                                        'Dedicated support',
                                        'SSO & SAML',
                                        'Audit logs',
                                    ],
                                    'buttonText' => 'Contact Sales',
                                    'buttonLink' => '/contact',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#1e293b',
                            'textColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // FAQ
                    [
                        'type' => 'faq',
                        'content' => [
                            'title' => 'Pricing FAQ',
                            'items' => [
                                [
                                    'question' => 'Can I change plans anytime?',
                                    'answer' => 'Yes, you can upgrade or downgrade your plan at any time. Changes take effect immediately.',
                                ],
                                [
                                    'question' => 'What payment methods do you accept?',
                                    'answer' => 'We accept all major credit cards, PayPal, and bank transfers for enterprise customers.',
                                ],
                                [
                                    'question' => 'Is there a free trial?',
                                    'answer' => 'Yes! Pro plan comes with a 14-day free trial. No credit card required.',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#0f172a',
                            'textColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                ],
            ],
        ]);

        // Contact Page - Contact form and info
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => [
                'sections' => [
                    // Hero
                    [
                        'type' => 'hero',
                        'content' => [
                            'layout' => 'centered',
                            'title' => 'Get in Touch',
                            'subtitle' => 'Have questions? We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.',
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#0f172a',
                            'gradientTo' => '#1e1b4b',
                            'textColor' => '#ffffff',
                            'minHeight' => 350,
                        ],
                    ],
                    // Contact Form
                    [
                        'type' => 'contact',
                        'content' => [
                            'title' => 'Send Us a Message',
                            'subtitle' => 'Fill out the form below and we\'ll get back to you within 24 hours.',
                            'email' => 'hello@techflow.com',
                            'phone' => '+1 (555) 123-4567',
                            'address' => '123 Tech Street, San Francisco, CA 94105',
                            'showMap' => false,
                        ],
                        'style' => [
                            'backgroundColor' => '#1e293b',
                            'textColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Additional Contact Options
                    [
                        'type' => 'features',
                        'content' => [
                            'title' => 'Other Ways to Reach Us',
                            'layout' => 'grid',
                            'items' => [
                                [
                                    'icon' => 'chat',
                                    'title' => 'Live Chat',
                                    'description' => 'Chat with our support team in real-time. Available 24/7 for Pro and Enterprise customers.',
                                ],
                                [
                                    'icon' => 'book',
                                    'title' => 'Documentation',
                                    'description' => 'Browse our comprehensive docs and tutorials to find answers quickly.',
                                ],
                                [
                                    'icon' => 'users',
                                    'title' => 'Community',
                                    'description' => 'Join our Discord community with 10,000+ developers sharing tips and solutions.',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#0f172a',
                            'textColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function createLuxuryRestaurantTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'luxury-restaurant-pro'],
            [
                'name' => 'Luxury Restaurant Pro',
                'description' => 'Elegant restaurant website with parallax scrolling, image galleries, and sophisticated animations. Perfect for fine dining establishments.',
                'industry' => 'food-beverage',
                'thumbnail' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&q=80',
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 2,
                'theme' => [
                    'primaryColor' => '#d97706',
                    'secondaryColor' => '#1c1917',
                    'accentColor' => '#fbbf24',
                    'backgroundColor' => '#0a0a0a',
                    'textColor' => '#fafaf9',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'La Maison',
                        'sticky' => true,
                        'transparent' => true,
                        'showCta' => true,
                        'ctaText' => 'Reserve Table',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 La Maison. All rights reserved.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page - Luxury Restaurant
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => [
                'sections' => [
                    // Hero with Full-Screen Image
                    [
                        'type' => 'hero',
                        'content' => [
                            'layout' => 'centered',
                            'title' => 'An Unforgettable Culinary Experience',
                            'subtitle' => 'Michelin-starred cuisine in the heart of Lusaka. Reservations recommended.',
                            'buttonText' => 'Reserve Your Table',
                            'buttonLink' => '/contact',
                            'secondaryButtonText' => 'View Menu',
                            'secondaryButtonLink' => '/menu',
                            'backgroundImage' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920&q=80',
                            'overlayColor' => 'black',
                            'overlayOpacity' => 60,
                        ],
                        'style' => [
                            'textColor' => '#ffffff',
                            'minHeight' => 800,
                        ],
                    ],
                    // About Section with Image
                    [
                        'type' => 'about',
                        'content' => [
                            'title' => 'Where Tradition Meets Innovation',
                            'description' => 'For over 20 years, La Maison has been crafting exceptional dining experiences. Our award-winning chef combines classical French techniques with modern innovation, using only the finest locally-sourced ingredients.',
                            'image' => 'https://images.unsplash.com/photo-1600565193348-f74bd3c7ccdf?w=800&q=80',
                        ],
                        'style' => [
                            'backgroundColor' => '#1c1917',
                            'textColor' => '#fafaf9',
                            'paddingTop' => 100,
                            'paddingBottom' => 100,
                        ],
                    ],
                    // Gallery Section
                    [
                        'type' => 'gallery',
                        'content' => [
                            'title' => 'A Feast for the Eyes',
                            'layout' => 'masonry',
                            'images' => [
                                'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80',
                                'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=600&q=80',
                                'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80',
                                'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=600&q=80',
                                'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=600&q=80',
                                'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?w=600&q=80',
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#0a0a0a',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Testimonials
                    [
                        'type' => 'testimonials',
                        'content' => [
                            'title' => 'What Our Guests Say',
                            'layout' => 'carousel',
                            'items' => [
                                [
                                    'text' => 'An absolutely extraordinary dining experience. Every dish was a masterpiece.',
                                    'name' => 'Sarah Johnson',
                                    'role' => 'Food Critic',
                                    'rating' => 5,
                                ],
                                [
                                    'text' => 'The attention to detail is impeccable. Best restaurant in Lusaka, hands down.',
                                    'name' => 'Michael Chen',
                                    'role' => 'Regular Guest',
                                    'rating' => 5,
                                ],
                                [
                                    'text' => 'From the ambiance to the service to the food - perfection in every way.',
                                    'name' => 'Emma Williams',
                                    'role' => 'Travel Blogger',
                                    'rating' => 5,
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#1c1917',
                            'textColor' => '#fafaf9',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // CTA Section
                    [
                        'type' => 'cta',
                        'content' => [
                            'title' => 'Reserve Your Table Today',
                            'description' => 'Experience culinary excellence. Limited seating available.',
                            'buttonText' => 'Make Reservation',
                            'buttonLink' => '/contact',
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#d97706',
                            'gradientTo' => '#b45309',
                            'gradientDirection' => 'to-r',
                            'textColor' => '#ffffff',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                ],
            ],
        ]);

        // Menu Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Menu',
            'slug' => 'menu',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => []],
        ]);

        // Reservations Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Reservations',
            'slug' => 'reservations',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => []],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => []],
        ]);
    }

    private function createCreativeAgencyTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'creative-agency-pro'],
            [
                'name' => 'Creative Agency Pro',
                'description' => 'Bold, asymmetric design with stunning typography and portfolio showcases. Perfect for design agencies and creative studios.',
                'industry' => 'creative',
                'thumbnail' => 'https://images.unsplash.com/photo-1558655146-9f40138edfeb?w=400&q=80',
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 3,
                'theme' => [
                    'primaryColor' => '#ec4899',
                    'secondaryColor' => '#8b5cf6',
                    'accentColor' => '#f59e0b',
                    'backgroundColor' => '#ffffff',
                    'textColor' => '#0f172a',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'STUDIO',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Start Project',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 STUDIO. All rights reserved.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page - Creative Agency
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => [
                'sections' => [
                    // Hero with Bold Typography
                    [
                        'type' => 'hero',
                        'content' => [
                            'layout' => 'centered',
                            'title' => 'We Create Digital Experiences That Matter',
                            'subtitle' => 'Award-winning design studio crafting brands, websites, and digital products for forward-thinking companies.',
                            'buttonText' => 'View Our Work',
                            'buttonLink' => '/portfolio',
                            'secondaryButtonText' => 'Start a Project',
                            'secondaryButtonLink' => '/contact',
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#fdf4ff',
                            'gradientTo' => '#fef3c7',
                            'gradientDirection' => 'to-br',
                            'textColor' => '#0f172a',
                            'minHeight' => 700,
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Services with Icons
                    [
                        'type' => 'services',
                        'content' => [
                            'title' => 'What We Do',
                            'subtitle' => 'Full-service creative solutions',
                            'layout' => 'grid',
                            'items' => [
                                [
                                    'icon' => 'paint',
                                    'title' => 'Brand Identity',
                                    'description' => 'Crafting memorable brands that stand out and resonate with your audience.',
                                ],
                                [
                                    'icon' => 'code',
                                    'title' => 'Web Design',
                                    'description' => 'Beautiful, responsive websites that convert visitors into customers.',
                                ],
                                [
                                    'icon' => 'mobile',
                                    'title' => 'App Development',
                                    'description' => 'Native and cross-platform apps that users love to use.',
                                ],
                                [
                                    'icon' => 'video',
                                    'title' => 'Motion Design',
                                    'description' => 'Engaging animations and video content that tells your story.',
                                ],
                                [
                                    'icon' => 'megaphone',
                                    'title' => 'Digital Marketing',
                                    'description' => 'Data-driven campaigns that grow your business.',
                                ],
                                [
                                    'icon' => 'camera',
                                    'title' => 'Photography',
                                    'description' => 'Professional photography that captures your brand essence.',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                            'textColor' => '#0f172a',
                            'paddingTop' => 100,
                            'paddingBottom' => 100,
                        ],
                    ],
                    // Portfolio Grid
                    [
                        'type' => 'gallery',
                        'content' => [
                            'title' => 'Selected Work',
                            'subtitle' => 'Projects we\'re proud of',
                            'layout' => 'bento',
                            'images' => [
                                'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&q=80',
                                'https://images.unsplash.com/photo-1558655146-d09347e92766?w=800&q=80',
                                'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=800&q=80',
                                'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=800&q=80',
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Stats
                    [
                        'type' => 'stats',
                        'content' => [
                            'title' => 'By the Numbers',
                            'items' => [
                                ['value' => '200+', 'label' => 'Projects Delivered'],
                                ['value' => '50+', 'label' => 'Happy Clients'],
                                ['value' => '15', 'label' => 'Awards Won'],
                                ['value' => '10+', 'label' => 'Years Experience'],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#0f172a',
                            'textColor' => '#ffffff',
                            'paddingTop' => 60,
                            'paddingBottom' => 60,
                        ],
                    ],
                    // CTA
                    [
                        'type' => 'cta',
                        'content' => [
                            'title' => 'Let\'s Create Something Amazing',
                            'description' => 'Ready to bring your vision to life? Let\'s talk.',
                            'buttonText' => 'Start Your Project',
                            'buttonLink' => '/contact',
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#ec4899',
                            'gradientTo' => '#8b5cf6',
                            'gradientDirection' => 'to-r',
                            'textColor' => '#ffffff',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                ],
            ],
        ]);

        // Portfolio Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Portfolio',
            'slug' => 'portfolio',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => []],
        ]);

        // Services Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Services',
            'slug' => 'services',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => []],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => []],
        ]);
    }

    private function createFitnessStudioTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'fitness-studio-pro'],
            [
                'name' => 'Fitness Studio Pro',
                'description' => 'High-energy design with dynamic imagery and motivational content. Perfect for gyms, fitness studios, and personal trainers.',
                'industry' => 'fitness',
                'thumbnail' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=400&q=80',
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 4,
                'theme' => [
                    'primaryColor' => '#ef4444',
                    'secondaryColor' => '#0f172a',
                    'accentColor' => '#f59e0b',
                    'backgroundColor' => '#ffffff',
                    'textColor' => '#0f172a',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'APEX FITNESS',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Join Now',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Apex Fitness. All rights reserved.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page - Fitness Studio
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => [
                'sections' => [
                    // Hero with Video Background
                    [
                        'type' => 'hero',
                        'content' => [
                            'layout' => 'centered',
                            'title' => 'Transform Your Body, Transform Your Life',
                            'subtitle' => 'Join Lusaka\'s premier fitness studio. Expert trainers, state-of-the-art equipment, results guaranteed.',
                            'buttonText' => 'Start Free Trial',
                            'buttonLink' => '/contact',
                            'secondaryButtonText' => 'View Classes',
                            'secondaryButtonLink' => '/classes',
                            'backgroundImage' => 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=1920&q=80',
                            'overlayColor' => 'black',
                            'overlayOpacity' => 50,
                        ],
                        'style' => [
                            'textColor' => '#ffffff',
                            'minHeight' => 750,
                        ],
                    ],
                    // Features
                    [
                        'type' => 'features',
                        'content' => [
                            'title' => 'Why Choose Apex Fitness',
                            'layout' => 'grid',
                            'items' => [
                                [
                                    'icon' => 'users',
                                    'title' => 'Expert Trainers',
                                    'description' => 'Certified professionals dedicated to your success.',
                                ],
                                [
                                    'icon' => 'dumbbell',
                                    'title' => 'Premium Equipment',
                                    'description' => 'Latest fitness technology and equipment.',
                                ],
                                [
                                    'icon' => 'calendar',
                                    'title' => 'Flexible Schedule',
                                    'description' => 'Classes from 5 AM to 10 PM, 7 days a week.',
                                ],
                                [
                                    'icon' => 'heart',
                                    'title' => 'Nutrition Coaching',
                                    'description' => 'Personalized meal plans and nutrition guidance.',
                                ],
                                [
                                    'icon' => 'trophy',
                                    'title' => 'Results Driven',
                                    'description' => 'Track your progress with our app and analytics.',
                                ],
                                [
                                    'icon' => 'shield',
                                    'title' => 'Safe Environment',
                                    'description' => 'Clean, sanitized, and COVID-safe facility.',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#f8fafc',
                            'textColor' => '#0f172a',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Classes Grid
                    [
                        'type' => 'services',
                        'content' => [
                            'title' => 'Our Classes',
                            'subtitle' => 'Find your perfect workout',
                            'items' => [
                                [
                                    'title' => 'HIIT Training',
                                    'description' => 'High-intensity interval training for maximum calorie burn.',
                                    'image' => 'https://images.unsplash.com/photo-1549060279-7e168fcee0c2?w=600&q=80',
                                ],
                                [
                                    'title' => 'Strength Training',
                                    'description' => 'Build muscle and increase strength with guided workouts.',
                                    'image' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=600&q=80',
                                ],
                                [
                                    'title' => 'Yoga & Pilates',
                                    'description' => 'Improve flexibility, balance, and mindfulness.',
                                    'image' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&q=80',
                                ],
                                [
                                    'title' => 'Boxing',
                                    'description' => 'Full-body workout combining cardio and strength.',
                                    'image' => 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?w=600&q=80',
                                ],
                                [
                                    'title' => 'Spin Classes',
                                    'description' => 'High-energy cycling workouts with motivating music.',
                                    'image' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?w=600&q=80',
                                ],
                                [
                                    'title' => 'Personal Training',
                                    'description' => 'One-on-one sessions tailored to your goals.',
                                    'image' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=600&q=80',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Testimonials
                    [
                        'type' => 'testimonials',
                        'content' => [
                            'title' => 'Success Stories',
                            'items' => [
                                [
                                    'text' => 'Lost 20kg in 6 months! The trainers are amazing and the community is so supportive.',
                                    'name' => 'John Banda',
                                    'role' => 'Member since 2023',
                                    'rating' => 5,
                                ],
                                [
                                    'text' => 'Best gym in Lusaka. Clean, modern equipment and the classes are incredible.',
                                    'name' => 'Grace Mwansa',
                                    'role' => 'Member since 2022',
                                    'rating' => 5,
                                ],
                                [
                                    'text' => 'Finally found a gym that motivates me. I actually look forward to my workouts now!',
                                    'name' => 'David Phiri',
                                    'role' => 'Member since 2024',
                                    'rating' => 5,
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#0f172a',
                            'textColor' => '#ffffff',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Pricing
                    [
                        'type' => 'pricing',
                        'content' => [
                            'title' => 'Membership Plans',
                            'subtitle' => 'Choose the plan that fits your lifestyle',
                            'plans' => [
                                [
                                    'name' => 'Basic',
                                    'price' => 'K300/mo',
                                    'features' => [
                                        'Gym access',
                                        'Locker room',
                                        'Free WiFi',
                                        'Mobile app',
                                    ],
                                ],
                                [
                                    'name' => 'Premium',
                                    'price' => 'K500/mo',
                                    'featured' => true,
                                    'features' => [
                                        'Everything in Basic',
                                        'All group classes',
                                        'Nutrition coaching',
                                        'Progress tracking',
                                        'Guest passes (2/mo)',
                                    ],
                                ],
                                [
                                    'name' => 'Elite',
                                    'price' => 'K800/mo',
                                    'features' => [
                                        'Everything in Premium',
                                        'Personal training (4/mo)',
                                        'Priority booking',
                                        'Massage therapy',
                                        'Unlimited guest passes',
                                    ],
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // CTA
                    [
                        'type' => 'cta',
                        'content' => [
                            'title' => 'Ready to Start Your Fitness Journey?',
                            'description' => 'Join today and get your first week free!',
                            'buttonText' => 'Claim Free Trial',
                            'buttonLink' => '/contact',
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#ef4444',
                            'gradientTo' => '#dc2626',
                            'gradientDirection' => 'to-r',
                            'textColor' => '#ffffff',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                ],
            ],
        ]);

        // Classes Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Classes',
            'slug' => 'classes',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => []],
        ]);

        // Trainers Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Trainers',
            'slug' => 'trainers',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => []],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Join Now',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => []],
        ]);
    }

    private function createEcommerceStoreTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'ecommerce-store-pro'],
            [
                'name' => 'E-commerce Store Pro',
                'description' => 'Modern online store with product showcases, hover effects, and seamless shopping experience. Perfect for retail businesses.',
                'industry' => 'retail',
                'thumbnail' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&q=80',
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 5,
                'theme' => [
                    'primaryColor' => '#0891b2',
                    'secondaryColor' => '#0f172a',
                    'accentColor' => '#f59e0b',
                    'backgroundColor' => '#ffffff',
                    'textColor' => '#0f172a',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'SHOPIFY',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Shop Now',
                        'ctaLink' => '/products',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Shopify. All rights reserved.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page - E-commerce Store
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => [
                'sections' => [
                    // Hero with Product Showcase
                    [
                        'type' => 'hero',
                        'content' => [
                            'layout' => 'split-right',
                            'title' => 'New Collection Just Dropped',
                            'subtitle' => 'Discover the latest trends in fashion. Free shipping on orders over K500.',
                            'buttonText' => 'Shop Collection',
                            'buttonLink' => '/products',
                            'secondaryButtonText' => 'View Lookbook',
                            'secondaryButtonLink' => '/lookbook',
                            'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=800&q=80',
                        ],
                        'style' => [
                            'backgroundColor' => '#f8fafc',
                            'textColor' => '#0f172a',
                            'minHeight' => 650,
                            'paddingTop' => 60,
                            'paddingBottom' => 60,
                        ],
                    ],
                    // Featured Categories
                    [
                        'type' => 'services',
                        'content' => [
                            'title' => 'Shop by Category',
                            'layout' => 'grid',
                            'items' => [
                                [
                                    'title' => 'Women\'s Fashion',
                                    'description' => 'Dresses, tops, and accessories',
                                    'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=600&q=80',
                                ],
                                [
                                    'title' => 'Men\'s Fashion',
                                    'description' => 'Shirts, pants, and shoes',
                                    'image' => 'https://images.unsplash.com/photo-1490114538077-0a7f8cb49891?w=600&q=80',
                                ],
                                [
                                    'title' => 'Accessories',
                                    'description' => 'Bags, jewelry, and watches',
                                    'image' => 'https://images.unsplash.com/photo-1492707892479-7bc8d5a4ee93?w=600&q=80',
                                ],
                                [
                                    'title' => 'Footwear',
                                    'description' => 'Sneakers, heels, and sandals',
                                    'image' => 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=600&q=80',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Featured Products
                    [
                        'type' => 'products',
                        'content' => [
                            'title' => 'Trending Now',
                            'subtitle' => 'Our most popular items this week',
                            'products' => [
                                [
                                    'name' => 'Classic White Tee',
                                    'price' => 15000,
                                    'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&q=80',
                                ],
                                [
                                    'name' => 'Denim Jacket',
                                    'price' => 45000,
                                    'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=400&q=80',
                                ],
                                [
                                    'name' => 'Leather Bag',
                                    'price' => 35000,
                                    'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&q=80',
                                ],
                                [
                                    'name' => 'Sneakers',
                                    'price' => 55000,
                                    'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&q=80',
                                ],
                                [
                                    'name' => 'Summer Dress',
                                    'price' => 38000,
                                    'image' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=400&q=80',
                                ],
                                [
                                    'name' => 'Sunglasses',
                                    'price' => 12000,
                                    'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=400&q=80',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#f8fafc',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                    // Features
                    [
                        'type' => 'features',
                        'content' => [
                            'title' => 'Why Shop With Us',
                            'layout' => 'grid',
                            'items' => [
                                [
                                    'icon' => 'truck',
                                    'title' => 'Free Shipping',
                                    'description' => 'On orders over K500',
                                ],
                                [
                                    'icon' => 'shield',
                                    'title' => 'Secure Payment',
                                    'description' => 'Safe & encrypted checkout',
                                ],
                                [
                                    'icon' => 'refresh',
                                    'title' => 'Easy Returns',
                                    'description' => '30-day return policy',
                                ],
                                [
                                    'icon' => 'support',
                                    'title' => '24/7 Support',
                                    'description' => 'Always here to help',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                            'paddingTop' => 60,
                            'paddingBottom' => 60,
                        ],
                    ],
                    // Newsletter CTA
                    [
                        'type' => 'cta',
                        'content' => [
                            'title' => 'Get 10% Off Your First Order',
                            'description' => 'Subscribe to our newsletter for exclusive deals and new arrivals.',
                            'buttonText' => 'Subscribe Now',
                            'buttonLink' => '#newsletter',
                        ],
                        'style' => [
                            'backgroundType' => 'gradient',
                            'gradientFrom' => '#0891b2',
                            'gradientTo' => '#0e7490',
                            'gradientDirection' => 'to-r',
                            'textColor' => '#ffffff',
                            'paddingTop' => 80,
                            'paddingBottom' => 80,
                        ],
                    ],
                ],
            ],
        ]);

        // Products Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Products',
            'slug' => 'products',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => []],
        ]);

        // About Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'About',
            'slug' => 'about',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => []],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => []],
        ]);
    }
}
