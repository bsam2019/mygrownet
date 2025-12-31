<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use Illuminate\Database\Seeder;

class EnhancedTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $this->enhanceConsultingTemplate();
        $this->enhanceTechTemplate();
        $this->enhanceRestaurantTemplate();
        $this->enhanceSalonTemplate();
        $this->enhanceLawFirmTemplate();
        $this->enhanceGymTemplate();
    }

    private function enhanceConsultingTemplate(): void
    {
        $template = SiteTemplate::where('slug', 'consulting-pro')->first();
        if (!$template) return;

        $template->update([
            'theme' => ['primaryColor' => '#2563eb', 'secondaryColor' => '#64748b', 'accentColor' => '#059669', 'backgroundColor' => '#ffffff', 'textColor' => '#1f2937'],
        ]);

        $homePage = $template->pages()->where('slug', 'home')->first();
        if ($homePage) {
            $homePage->update([
                'content' => ['sections' => [
                    ['type' => 'hero', 'content' => [
                        'layout' => 'split-right',
                        'title' => 'Transform Your Business with Expert Consulting',
                        'subtitle' => 'Strategic guidance that drives measurable results. Join 500+ Zambian companies that increased revenue by 40% in 12 months with our proven methodologies.',
                        'buttonText' => 'Book Free Strategy Session',
                        'buttonLink' => '/contact',
                        'secondaryButtonText' => 'View Case Studies',
                        'secondaryButtonLink' => '/services',
                        'image' => 'https://images.unsplash.com/photo-1553028826-f4804a6dba3b?w=1200&q=80'
                    ], 'style' => ['backgroundColor' => '#0f172a', 'textColor' => '#ffffff', 'minHeight' => 650]],
                    
                    ['type' => 'logo-cloud', 'content' => [
                        'title' => 'Trusted by Leading Zambian Companies',
                        'layout' => 'grid',
                        'grayscale' => true,
                        'items' => [['name' => 'Zambia Airways'], ['name' => 'MTN Zambia'], ['name' => 'Shoprite'], ['name' => 'Zanaco'], ['name' => 'Zambeef'], ['name' => 'Lafarge']]
                    ], 'style' => ['backgroundColor' => '#f9fafb', 'paddingY' => 60]],
                    
                    ['type' => 'stats', 'content' => [
                        'layout' => 'horizontal',
                        'title' => 'Our Impact in Numbers',
                        'animated' => true,
                        'items' => [
                            ['number' => '500', 'suffix' => '+', 'label' => 'Clients Served', 'icon' => 'users'],
                            ['number' => '40', 'suffix' => '%', 'label' => 'Avg Revenue Growth', 'icon' => 'chart'],
                            ['number' => '15', 'suffix' => '+', 'label' => 'Years Experience', 'icon' => 'calendar'],
                            ['number' => '98', 'suffix' => '%', 'label' => 'Client Satisfaction', 'icon' => 'heart']
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'accentColor' => '#2563eb', 'paddingY' => 80]],
                    
                    ['type' => 'services', 'content' => [
                        'title' => 'Comprehensive Business Solutions',
                        'subtitle' => 'From strategy to execution, we deliver results that matter',
                        'layout' => 'cards',
                        'columns' => 3,
                        'items' => [
                            ['title' => 'Strategic Planning', 'description' => 'Comprehensive strategies tailored to your business goals, market position, and competitive landscape.', 'icon' => 'chart'],
                            ['title' => 'Process Optimization', 'description' => 'Streamline operations, reduce costs, and maximize efficiency across your organization.', 'icon' => 'cog'],
                            ['title' => 'Digital Transformation', 'description' => 'Modernize your business with technology solutions that drive growth and innovation.', 'icon' => 'code'],
                            ['title' => 'Financial Advisory', 'description' => 'Expert guidance on financial planning, investment strategies, and risk management.', 'icon' => 'calculator'],
                            ['title' => 'HR & Talent', 'description' => 'Build high-performing teams with our recruitment and organizational development services.', 'icon' => 'users'],
                            ['title' => 'Market Research', 'description' => 'Data-driven insights to understand your market, customers, and competition.', 'icon' => 'search']
                        ]
                    ], 'style' => ['backgroundColor' => '#f9fafb', 'paddingY' => 100]],
                    
                    ['type' => 'timeline', 'content' => [
                        'layout' => 'vertical',
                        'title' => 'Our Journey of Excellence',
                        'subtitle' => 'Milestones that shaped our success',
                        'items' => [
                            ['year' => '2009', 'title' => 'Company Founded', 'description' => 'Started with a vision to transform Zambian businesses through strategic consulting', 'icon' => 'star'],
                            ['year' => '2015', 'title' => 'Regional Expansion', 'description' => 'Opened offices in Lusaka, Ndola, and Kitwe to serve clients nationwide', 'icon' => 'building'],
                            ['year' => '2020', 'title' => 'Award Recognition', 'description' => 'Named "Best Business Consultancy" by Zambia Business Awards', 'icon' => 'trophy'],
                            ['year' => '2024', 'title' => '500+ Clients', 'description' => 'Proud milestone of helping over 500 businesses achieve their goals', 'icon' => 'sparkles']
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'lineColor' => '#2563eb', 'paddingY' => 100]],
                    
                    ['type' => 'about', 'content' => [
                        'layout' => 'image-right',
                        'title' => 'Your Partner in Business Excellence',
                        'description' => 'With over 15 years of experience, we help businesses navigate complex challenges and unlock their full potential. Our team of seasoned consultants brings diverse expertise across industries, delivering customized solutions that drive sustainable growth.',
                        'image' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80',
                        'buttonText' => 'Learn More About Us',
                        'buttonLink' => '/about'
                    ], 'style' => ['backgroundColor' => '#f9fafb', 'paddingY' => 100]],
                    
                    ['type' => 'testimonials', 'content' => [
                        'layout' => 'grid',
                        'title' => 'What Our Clients Say',
                        'subtitle' => 'Real results from real customers',
                        'columns' => 3,
                        'items' => [
                            ['name' => 'James Mwansa', 'role' => 'CEO, TechCorp Zambia', 'text' => 'Their strategic insights helped us increase efficiency by 60% and cut costs by K2M annually. Best investment we ever made.', 'rating' => 5],
                            ['name' => 'Grace Banda', 'role' => 'Founder, Banda Ventures', 'text' => 'Professional, knowledgeable, and always available. They transformed our business operations completely.', 'rating' => 5],
                            ['name' => 'Peter Phiri', 'role' => 'MD, Phiri Industries', 'text' => 'Outstanding results in just 6 months. Revenue up 45%, costs down 30%. Highly recommended!', 'rating' => 5]
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                    
                    ['type' => 'cta-banner', 'content' => [
                        'layout' => 'centered',
                        'title' => 'Ready to Transform Your Business?',
                        'subtitle' => 'Schedule a complimentary strategy session and discover how we can help you achieve your goals.',
                        'buttonText' => 'Get Started Today',
                        'buttonLink' => '/contact',
                        'secondaryButtonText' => 'Call: +260 211 123 456',
                        'secondaryButtonLink' => 'tel:+260211123456'
                    ], 'style' => ['backgroundColor' => '#2563eb', 'textColor' => '#ffffff', 'gradient' => true, 'paddingY' => 100]],
                ]],
            ]);
        }
    }

    private function enhanceTechTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'tech-startup'], [
            'name' => 'Tech Startup',
            'description' => 'Modern SaaS and tech startup template with video hero',
            'industry' => 'tech',
            'thumbnail' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800&q=80',
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 2,
            'theme' => ['primaryColor' => '#0891b2', 'secondaryColor' => '#06b6d4', 'accentColor' => '#8b5cf6', 'backgroundColor' => '#ffffff', 'textColor' => '#1f2937'],
            'settings' => ['navigation' => ['logoText' => 'TechFlow', 'sticky' => true, 'showCta' => true, 'ctaText' => 'Start Free Trial', 'ctaLink' => '/contact'], 'footer' => ['copyrightText' => '© 2024 TechFlow']],
        ]);

        $template->pages()->delete();
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'video-hero', 'content' => [
                    'layout' => 'overlay',
                    'title' => 'Build the Future with Cutting-Edge Technology',
                    'subtitle' => 'Innovative software solutions that scale with your business. Trusted by 10,000+ companies across Africa.',
                    'posterImage' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1920&q=80',
                    'buttonText' => 'Start Your Free Trial',
                    'buttonLink' => '/contact',
                    'secondaryButtonText' => 'Watch Demo',
                    'secondaryButtonLink' => '#demo'
                ], 'style' => ['overlay' => true, 'minHeight' => 700]],
                
                ['type' => 'logo-cloud', 'content' => [
                    'title' => 'Trusted by Industry Leaders',
                    'layout' => 'marquee',
                    'items' => [['name' => 'MTN'], ['name' => 'Airtel'], ['name' => 'Zanaco'], ['name' => 'Stanbic'], ['name' => 'Zambeef']]
                ], 'style' => ['backgroundColor' => '#f0fdfa', 'paddingY' => 50]],
                
                ['type' => 'features', 'content' => [
                    'title' => 'Everything You Need to Succeed',
                    'subtitle' => 'Powerful tools designed for modern businesses',
                    'layout' => 'grid',
                    'columns' => 3,
                    'items' => [
                        ['title' => 'Cloud-Based', 'description' => 'Access your data from anywhere, anytime. No installation required.', 'icon' => 'cloud'],
                        ['title' => 'Bank-Level Security', 'description' => '256-bit encryption and SOC 2 compliance to protect your data.', 'icon' => 'shield'],
                        ['title' => 'Infinitely Scalable', 'description' => 'From startup to enterprise, our platform grows with you.', 'icon' => 'rocket'],
                        ['title' => 'Lightning Fast', 'description' => 'Optimized performance with 99.9% uptime guarantee.', 'icon' => 'bolt'],
                        ['title' => 'Easy Integration', 'description' => 'Connect with 100+ tools you already use via our API.', 'icon' => 'puzzle'],
                        ['title' => '24/7 Support', 'description' => 'Expert support team available around the clock.', 'icon' => 'chat']
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                
                ['type' => 'stats', 'content' => [
                    'layout' => 'grid',
                    'animated' => true,
                    'items' => [
                        ['number' => '10', 'suffix' => 'K+', 'label' => 'Active Users', 'icon' => 'users'],
                        ['number' => '99.9', 'suffix' => '%', 'label' => 'Uptime', 'icon' => 'chart'],
                        ['number' => '50', 'suffix' => '%', 'label' => 'Cost Reduction', 'icon' => 'trending'],
                        ['number' => '4.9', 'suffix' => '/5', 'label' => 'User Rating', 'icon' => 'star']
                    ]
                ], 'style' => ['backgroundColor' => '#0891b2', 'textColor' => '#ffffff', 'paddingY' => 80]],
                
                ['type' => 'pricing', 'content' => [
                    'title' => 'Simple, Transparent Pricing',
                    'subtitle' => 'No hidden fees. Cancel anytime.',
                    'layout' => 'cards',
                    'plans' => [
                        ['name' => 'Starter', 'price' => 'K500', 'period' => '/month', 'features' => ['Up to 5 users', '10GB storage', 'Basic analytics', 'Email support'], 'buttonText' => 'Start Free Trial'],
                        ['name' => 'Professional', 'price' => 'K1,500', 'period' => '/month', 'features' => ['Up to 25 users', '100GB storage', 'Advanced analytics', 'Priority support', 'API access'], 'popular' => true, 'buttonText' => 'Start Free Trial'],
                        ['name' => 'Enterprise', 'price' => 'Custom', 'period' => '', 'features' => ['Unlimited users', 'Unlimited storage', 'Custom integrations', 'Dedicated support', 'SLA guarantee'], 'buttonText' => 'Contact Sales']
                    ]
                ], 'style' => ['backgroundColor' => '#f0fdfa', 'paddingY' => 100]],
                
                ['type' => 'testimonials', 'content' => [
                    'layout' => 'carousel',
                    'title' => 'Loved by Teams Everywhere',
                    'items' => [
                        ['name' => 'Peter Phiri', 'role' => 'CTO, FinTech Solutions', 'text' => 'TechFlow reduced our operational costs by 50% and improved customer satisfaction dramatically. Game-changing technology.', 'rating' => 5],
                        ['name' => 'Grace Mwale', 'role' => 'Operations Manager, Retail Corp', 'text' => 'The ease of use and powerful features make TechFlow indispensable for our daily operations.', 'rating' => 5]
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                
                ['type' => 'cta-banner', 'content' => [
                    'layout' => 'split',
                    'title' => 'Ready to Scale Your Business?',
                    'subtitle' => 'Join thousands of companies using TechFlow to grow faster. Start your 14-day free trial today.',
                    'buttonText' => 'Get Started Free',
                    'buttonLink' => '/contact'
                ], 'style' => ['backgroundColor' => '#0891b2', 'textColor' => '#ffffff', 'gradient' => true, 'paddingY' => 100]],
            ]],
        ]);
    }

    private function enhanceRestaurantTemplate(): void
    {
        $template = SiteTemplate::where('slug', 'restaurant-delight')->first();
        if (!$template) return;

        $template->update(['theme' => ['primaryColor' => '#ea580c', 'secondaryColor' => '#f97316', 'accentColor' => '#fbbf24', 'backgroundColor' => '#ffffff', 'textColor' => '#1f2937']]);

        $homePage = $template->pages()->where('slug', 'home')->first();
        if ($homePage) {
            $homePage->update([
                'content' => ['sections' => [
                    ['type' => 'hero', 'content' => [
                        'layout' => 'centered',
                        'title' => 'Experience Authentic Zambian Cuisine',
                        'subtitle' => 'Fresh ingredients, traditional recipes, and unforgettable flavors. Dine with us and taste the difference that 10 years of culinary excellence makes.',
                        'buttonText' => 'Reserve Your Table',
                        'buttonLink' => '/contact',
                        'secondaryButtonText' => 'View Menu',
                        'secondaryButtonLink' => '#menu',
                        'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1920&q=80'
                    ], 'style' => ['backgroundColor' => '#7c2d12', 'textColor' => '#ffffff', 'minHeight' => 650, 'overlay' => true]],
                    
                    ['type' => 'features', 'content' => [
                        'title' => 'Why Dine With Us',
                        'layout' => 'grid',
                        'columns' => 4,
                        'items' => [
                            ['title' => 'Fresh Ingredients', 'description' => 'Locally sourced, farm-to-table produce', 'icon' => 'leaf'],
                            ['title' => 'Traditional Recipes', 'description' => 'Authentic Zambian flavors passed down generations', 'icon' => 'book'],
                            ['title' => 'Cozy Atmosphere', 'description' => 'Warm, welcoming ambiance for any occasion', 'icon' => 'home'],
                            ['title' => 'Expert Chefs', 'description' => 'Award-winning culinary team', 'icon' => 'star']
                        ]
                    ], 'style' => ['backgroundColor' => '#fffbeb', 'paddingY' => 80]],
                    
                    ['type' => 'services', 'content' => [
                        'title' => 'Our Menu Highlights',
                        'subtitle' => 'From traditional favorites to modern interpretations',
                        'layout' => 'cards',
                        'columns' => 3,
                        'items' => [
                            ['title' => 'Traditional Dishes', 'description' => 'Authentic nshima, ifisashi, kapenta, and village chicken prepared the traditional way', 'icon' => 'star', 'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&q=80'],
                            ['title' => 'Grilled Specialties', 'description' => 'Fresh bream, tilapia, and premium cuts of beef and goat grilled to perfection', 'icon' => 'fire', 'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&q=80'],
                            ['title' => 'Vegetarian Options', 'description' => 'Delicious plant-based meals including chibwabwa, bondwe, and fresh salads', 'icon' => 'leaf', 'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&q=80']
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                    
                    ['type' => 'stats', 'content' => [
                        'layout' => 'horizontal',
                        'title' => 'Our Story in Numbers',
                        'animated' => true,
                        'items' => [
                            ['number' => '10', 'suffix' => '+', 'label' => 'Years Serving', 'icon' => 'calendar'],
                            ['number' => '50', 'suffix' => '+', 'label' => 'Menu Items', 'icon' => 'book'],
                            ['number' => '100', 'suffix' => 'K+', 'label' => 'Happy Customers', 'icon' => 'users'],
                            ['number' => '4.9', 'suffix' => '/5', 'label' => 'Average Rating', 'icon' => 'star']
                        ]
                    ], 'style' => ['backgroundColor' => '#ea580c', 'textColor' => '#ffffff', 'paddingY' => 80]],
                    
                    ['type' => 'timeline', 'content' => [
                        'layout' => 'horizontal',
                        'title' => 'Our Culinary Journey',
                        'items' => [
                            ['year' => '2014', 'title' => 'First Restaurant', 'description' => 'Opened our doors in Lusaka with a dream', 'icon' => 'star'],
                            ['year' => '2018', 'title' => 'Award Winning', 'description' => 'Named Best Restaurant in Zambia', 'icon' => 'trophy'],
                            ['year' => '2022', 'title' => 'Expansion', 'description' => 'Second location in Ndola', 'icon' => 'building'],
                            ['year' => '2024', 'title' => '10 Years', 'description' => 'Celebrating a decade of excellence', 'icon' => 'sparkles']
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'lineColor' => '#ea580c', 'paddingY' => 100]],
                    
                    ['type' => 'about', 'content' => [
                        'layout' => 'image-left',
                        'title' => 'A Culinary Journey Through Zambia',
                        'description' => 'For over 10 years, we\'ve been serving authentic Zambian dishes made with love and the freshest local ingredients. Every meal tells a story of our rich culinary heritage. Our chefs combine traditional techniques with modern presentation to create unforgettable dining experiences.',
                        'image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80'
                    ], 'style' => ['backgroundColor' => '#fffbeb', 'paddingY' => 100]],
                    
                    ['type' => 'testimonials', 'content' => [
                        'layout' => 'carousel',
                        'title' => 'What Our Guests Say',
                        'items' => [
                            ['name' => 'Grace Banda', 'role' => 'Food Blogger', 'text' => 'The best nshima and relish in Lusaka! The atmosphere is warm, the service is excellent, and the food is absolutely delicious. A must-visit!', 'rating' => 5],
                            ['name' => 'James Mwansa', 'role' => 'Regular Customer', 'text' => 'We celebrate every family occasion here. The food is consistently amazing and the staff treats us like family.', 'rating' => 5]
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                    
                    ['type' => 'cta-banner', 'content' => [
                        'layout' => 'with-image',
                        'title' => 'Join Us for an Unforgettable Meal',
                        'subtitle' => 'Book your table now and experience the finest Zambian cuisine. Private dining rooms available for special occasions.',
                        'buttonText' => 'Make a Reservation',
                        'buttonLink' => '/contact',
                        'secondaryButtonText' => 'Call: +260 211 123 456',
                        'secondaryButtonLink' => 'tel:+260211123456'
                    ], 'style' => ['backgroundColor' => '#ea580c', 'textColor' => '#ffffff', 'paddingY' => 100]],
                ]],
            ]);
        }
    }

    private function enhanceSalonTemplate(): void
    {
        $template = SiteTemplate::where('slug', 'beauty-salon')->first();
        if (!$template) return;

        $template->update(['theme' => ['primaryColor' => '#581c87', 'secondaryColor' => '#6b21a8', 'accentColor' => '#c026d3', 'backgroundColor' => '#faf5ff', 'textColor' => '#1f2937']]);

        $homePage = $template->pages()->where('slug', 'home')->first();
        if ($homePage) {
            $homePage->update([
                'content' => ['sections' => [
                    ['type' => 'hero', 'content' => [
                        'layout' => 'split-left',
                        'title' => 'Unleash Your Natural Beauty',
                        'subtitle' => 'Premium beauty services in a luxurious, relaxing environment. Our expert stylists are dedicated to making you look and feel your absolute best.',
                        'buttonText' => 'Book Appointment',
                        'buttonLink' => '/contact',
                        'secondaryButtonText' => 'View Services',
                        'secondaryButtonLink' => '#services',
                        'image' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=1200&q=80'
                    ], 'style' => ['backgroundColor' => '#4c1d95', 'textColor' => '#ffffff', 'minHeight' => 650]],
                    
                    ['type' => 'logo-cloud', 'content' => [
                        'title' => 'Premium Products We Use',
                        'subtitle' => 'Only the best for our clients',
                        'layout' => 'marquee',
                        'items' => [['name' => 'L\'Oréal'], ['name' => 'Redken'], ['name' => 'OPI'], ['name' => 'Dermalogica'], ['name' => 'Moroccan Oil']]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 60]],
                    
                    ['type' => 'services', 'content' => [
                        'title' => 'Our Services',
                        'subtitle' => 'Expert care for every beauty need',
                        'layout' => 'cards',
                        'columns' => 3,
                        'items' => [
                            ['title' => 'Hair Styling', 'description' => 'Cuts, color, treatments, braiding, and extensions by expert stylists', 'icon' => 'scissors'],
                            ['title' => 'Nail Care', 'description' => 'Manicures, pedicures, gel nails, and nail art', 'icon' => 'sparkles'],
                            ['title' => 'Skin Care', 'description' => 'Facials, peels, and customized skin treatments', 'icon' => 'heart'],
                            ['title' => 'Makeup', 'description' => 'Professional makeup for any occasion', 'icon' => 'brush'],
                            ['title' => 'Massage & Spa', 'description' => 'Relaxing massages and body treatments', 'icon' => 'spa'],
                            ['title' => 'Bridal Packages', 'description' => 'Complete bridal beauty services', 'icon' => 'gift']
                        ]
                    ], 'style' => ['backgroundColor' => '#faf5ff', 'paddingY' => 100]],
                    
                    ['type' => 'pricing', 'content' => [
                        'title' => 'Popular Packages',
                        'subtitle' => 'Value-packed beauty experiences',
                        'layout' => 'cards',
                        'plans' => [
                            ['name' => 'Express Glow', 'price' => 'K350', 'features' => ['30-min facial', 'Express manicure', 'Brow shaping'], 'buttonText' => 'Book Now'],
                            ['name' => 'Pamper Day', 'price' => 'K850', 'features' => ['Full facial', 'Manicure & pedicure', 'Hair wash & style', '30-min massage'], 'popular' => true, 'buttonText' => 'Book Now'],
                            ['name' => 'Bridal Package', 'price' => 'K2,500', 'features' => ['Trial session', 'Wedding day makeup', 'Hair styling', 'Manicure & pedicure', 'Touch-up kit'], 'buttonText' => 'Book Now']
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                    
                    ['type' => 'stats', 'content' => [
                        'layout' => 'grid',
                        'animated' => true,
                        'items' => [
                            ['number' => '5000', 'suffix' => '+', 'label' => 'Happy Clients', 'icon' => 'users'],
                            ['number' => '8', 'suffix' => '+', 'label' => 'Years Experience', 'icon' => 'calendar'],
                            ['number' => '15', 'suffix' => '', 'label' => 'Expert Stylists', 'icon' => 'star'],
                            ['number' => '4.9', 'suffix' => '/5', 'label' => 'Client Rating', 'icon' => 'heart']
                        ]
                    ], 'style' => ['backgroundColor' => '#581c87', 'textColor' => '#ffffff', 'paddingY' => 80]],
                    
                    ['type' => 'gallery', 'content' => [
                        'title' => 'Our Work',
                        'subtitle' => 'Transformations that speak for themselves',
                        'layout' => 'grid',
                        'columns' => 3,
                        'images' => [
                            ['src' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=400&q=80', 'alt' => 'Hair styling'],
                            ['src' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=400&q=80', 'alt' => 'Nail art'],
                            ['src' => 'https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?w=400&q=80', 'alt' => 'Makeup'],
                            ['src' => 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=400&q=80', 'alt' => 'Facial'],
                            ['src' => 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?w=400&q=80', 'alt' => 'Bridal'],
                            ['src' => 'https://images.unsplash.com/photo-1519699047748-de8e457a634e?w=400&q=80', 'alt' => 'Hair color']
                        ]
                    ], 'style' => ['backgroundColor' => '#faf5ff', 'paddingY' => 80]],
                    
                    ['type' => 'testimonials', 'content' => [
                        'layout' => 'grid',
                        'title' => 'Client Love',
                        'columns' => 2,
                        'items' => [
                            ['name' => 'Chanda Mwale', 'role' => 'Regular Client', 'text' => 'Best salon in Lusaka! The staff is professional, the atmosphere is relaxing, and I always leave feeling beautiful and confident.', 'rating' => 5],
                            ['name' => 'Sarah Tembo', 'role' => 'Bride', 'text' => 'They made my wedding day perfect! The bridal package was worth every kwacha. I felt like a queen!', 'rating' => 5]
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                    
                    ['type' => 'cta-banner', 'content' => [
                        'layout' => 'centered',
                        'title' => 'Ready for Your Transformation?',
                        'subtitle' => 'Book your appointment today and experience luxury beauty services. First-time clients get 20% off!',
                        'buttonText' => 'Book Now',
                        'buttonLink' => '/contact',
                        'secondaryButtonText' => 'WhatsApp Us',
                        'secondaryButtonLink' => 'https://wa.me/260211123456'
                    ], 'style' => ['backgroundColor' => '#581c87', 'textColor' => '#ffffff', 'gradient' => true, 'paddingY' => 100]],
                ]],
            ]);
        }
    }

    private function enhanceLawFirmTemplate(): void
    {
        $template = SiteTemplate::where('slug', 'law-firm')->first();
        if (!$template) return;

        $template->update(['theme' => ['primaryColor' => '#1e40af', 'secondaryColor' => '#475569', 'accentColor' => '#0891b2', 'backgroundColor' => '#f8fafc', 'textColor' => '#0f172a']]);

        $homePage = $template->pages()->where('slug', 'home')->first();
        if ($homePage) {
            $homePage->update([
                'content' => ['sections' => [
                    ['type' => 'hero', 'content' => [
                        'layout' => 'centered',
                        'title' => 'Expert Legal Counsel You Can Trust',
                        'subtitle' => 'Protecting your rights and interests with integrity, expertise, and dedication. Serving individuals and businesses across Zambia for over 20 years.',
                        'buttonText' => 'Schedule a Consultation',
                        'buttonLink' => '/contact',
                        'secondaryButtonText' => 'Our Practice Areas',
                        'secondaryButtonLink' => '#services',
                        'image' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920&q=80'
                    ], 'style' => ['backgroundColor' => '#1e293b', 'textColor' => '#ffffff', 'minHeight' => 650, 'overlay' => true]],
                    
                    ['type' => 'features', 'content' => [
                        'title' => 'Why Choose Us',
                        'layout' => 'grid',
                        'columns' => 4,
                        'items' => [
                            ['title' => 'Experienced Team', 'description' => '20+ years of combined legal expertise', 'icon' => 'users'],
                            ['title' => 'Proven Results', 'description' => '500+ successful cases handled', 'icon' => 'trophy'],
                            ['title' => 'Client-Focused', 'description' => 'Personalized attention to every case', 'icon' => 'heart'],
                            ['title' => 'Confidential', 'description' => '100% attorney-client privilege', 'icon' => 'shield']
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                    
                    ['type' => 'stats', 'content' => [
                        'layout' => 'horizontal',
                        'animated' => true,
                        'items' => [
                            ['number' => '20', 'suffix' => '+', 'label' => 'Years Experience', 'icon' => 'calendar'],
                            ['number' => '500', 'suffix' => '+', 'label' => 'Cases Won', 'icon' => 'trophy'],
                            ['number' => '10', 'suffix' => '', 'label' => 'Expert Lawyers', 'icon' => 'users'],
                            ['number' => '98', 'suffix' => '%', 'label' => 'Success Rate', 'icon' => 'chart']
                        ]
                    ], 'style' => ['backgroundColor' => '#1e40af', 'textColor' => '#ffffff', 'paddingY' => 80]],
                    
                    ['type' => 'services', 'content' => [
                        'title' => 'Practice Areas',
                        'subtitle' => 'Comprehensive legal services for individuals and businesses',
                        'layout' => 'grid',
                        'columns' => 3,
                        'items' => [
                            ['title' => 'Corporate Law', 'description' => 'Business formation, contracts, mergers, and compliance', 'icon' => 'building'],
                            ['title' => 'Litigation', 'description' => 'Court representation and dispute resolution', 'icon' => 'scale'],
                            ['title' => 'Real Estate', 'description' => 'Property transactions, leases, and disputes', 'icon' => 'home'],
                            ['title' => 'Family Law', 'description' => 'Divorce, custody, adoption, and estate planning', 'icon' => 'users'],
                            ['title' => 'Employment Law', 'description' => 'Workplace rights, contracts, and disputes', 'icon' => 'briefcase'],
                            ['title' => 'Criminal Defense', 'description' => 'Protecting your rights in criminal matters', 'icon' => 'shield']
                        ]
                    ], 'style' => ['backgroundColor' => '#f8fafc', 'paddingY' => 100]],
                    
                    ['type' => 'timeline', 'content' => [
                        'layout' => 'zigzag',
                        'title' => 'Our Legacy of Excellence',
                        'items' => [
                            ['year' => '2004', 'title' => 'Firm Established', 'description' => 'Founded with a commitment to justice and client service', 'icon' => 'star'],
                            ['year' => '2010', 'title' => 'Landmark Victory', 'description' => 'Won precedent-setting case in Supreme Court', 'icon' => 'trophy'],
                            ['year' => '2018', 'title' => 'Regional Expansion', 'description' => 'Opened offices in Kitwe and Ndola', 'icon' => 'building'],
                            ['year' => '2024', 'title' => '20 Years Strong', 'description' => 'Two decades of trusted legal service', 'icon' => 'sparkles']
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'lineColor' => '#1e40af', 'paddingY' => 100]],
                    
                    ['type' => 'team', 'content' => [
                        'title' => 'Meet Our Legal Team',
                        'subtitle' => 'Experienced professionals dedicated to your case',
                        'layout' => 'grid',
                        'columns' => 4,
                        'items' => [
                            ['name' => 'Advocate David Tembo', 'role' => 'Managing Partner', 'bio' => 'LLB, LLM - 25 years in corporate law', 'image' => ''],
                            ['name' => 'Advocate Grace Banda', 'role' => 'Senior Partner', 'bio' => 'LLB - Specialist in family law', 'image' => ''],
                            ['name' => 'Advocate Peter Phiri', 'role' => 'Partner', 'bio' => 'LLB, MBA - Commercial litigation expert', 'image' => ''],
                            ['name' => 'Advocate Sarah Mwale', 'role' => 'Associate', 'bio' => 'LLB - Criminal defense specialist', 'image' => '']
                        ]
                    ], 'style' => ['backgroundColor' => '#f8fafc', 'paddingY' => 100]],
                    
                    ['type' => 'testimonials', 'content' => [
                        'layout' => 'grid',
                        'title' => 'Client Testimonials',
                        'columns' => 2,
                        'items' => [
                            ['name' => 'James Mwansa', 'role' => 'Business Owner', 'text' => 'Professional, knowledgeable, and always available. They handled our complex corporate matter with expertise and achieved an excellent outcome.', 'rating' => 5],
                            ['name' => 'Mary Lungu', 'role' => 'Private Client', 'text' => 'During a difficult family matter, they provided compassionate guidance and fought for my rights. I couldn\'t have asked for better representation.', 'rating' => 5]
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                    
                    ['type' => 'faq', 'content' => [
                        'title' => 'Frequently Asked Questions',
                        'items' => [
                            ['question' => 'How much does a consultation cost?', 'answer' => 'We offer a free initial consultation to discuss your case and determine how we can help.'],
                            ['question' => 'What areas do you serve?', 'answer' => 'We serve clients throughout Zambia with offices in Lusaka, Ndola, and Kitwe.'],
                            ['question' => 'How long will my case take?', 'answer' => 'Case duration varies depending on complexity. We\'ll provide a realistic timeline during your consultation.'],
                            ['question' => 'Do you offer payment plans?', 'answer' => 'Yes, we offer flexible payment arrangements to ensure access to quality legal representation.']
                        ]
                    ], 'style' => ['backgroundColor' => '#f8fafc', 'paddingY' => 80]],
                    
                    ['type' => 'cta-banner', 'content' => [
                        'layout' => 'split',
                        'title' => 'Need Legal Assistance?',
                        'subtitle' => 'Contact us today for a confidential consultation. We\'re here to protect your rights and fight for your interests.',
                        'buttonText' => 'Schedule Consultation',
                        'buttonLink' => '/contact',
                        'secondaryButtonText' => 'Call: +260 211 123 456',
                        'secondaryButtonLink' => 'tel:+260211123456'
                    ], 'style' => ['backgroundColor' => '#1e40af', 'textColor' => '#ffffff', 'paddingY' => 100]],
                ]],
            ]);
        }
    }

    private function enhanceGymTemplate(): void
    {
        $template = SiteTemplate::where('slug', 'fitness-gym')->first();
        if (!$template) return;

        $template->update(['theme' => ['primaryColor' => '#dc2626', 'secondaryColor' => '#991b1b', 'accentColor' => '#f59e0b', 'backgroundColor' => '#ffffff', 'textColor' => '#1f2937']]);

        $homePage = $template->pages()->where('slug', 'home')->first();
        if ($homePage) {
            $homePage->update([
                'content' => ['sections' => [
                    ['type' => 'video-hero', 'content' => [
                        'layout' => 'fullscreen',
                        'title' => 'Transform Your Body, Transform Your Life',
                        'subtitle' => 'Join Zambia\'s premier fitness center. Expert trainers, state-of-the-art equipment, and a community that motivates you to achieve your goals.',
                        'posterImage' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1920&q=80',
                        'buttonText' => 'Start Your Journey',
                        'buttonLink' => '/contact',
                        'secondaryButtonText' => 'View Memberships',
                        'secondaryButtonLink' => '#pricing'
                    ], 'style' => ['overlay' => true, 'minHeight' => 700]],
                    
                    ['type' => 'features', 'content' => [
                        'title' => 'Why Choose PowerFit',
                        'layout' => 'grid',
                        'columns' => 4,
                        'items' => [
                            ['title' => 'Modern Equipment', 'description' => 'Latest cardio and strength machines', 'icon' => 'dumbbell'],
                            ['title' => 'Expert Trainers', 'description' => 'Certified personal trainers', 'icon' => 'user'],
                            ['title' => '50+ Classes', 'description' => 'Yoga, HIIT, spinning, and more', 'icon' => 'calendar'],
                            ['title' => '24/7 Access', 'description' => 'Train on your schedule', 'icon' => 'clock']
                        ]
                    ], 'style' => ['backgroundColor' => '#fef2f2', 'paddingY' => 80]],
                    
                    ['type' => 'stats', 'content' => [
                        'layout' => 'grid',
                        'title' => 'Our Impact',
                        'animated' => true,
                        'items' => [
                            ['number' => '2000', 'suffix' => '+', 'label' => 'Active Members', 'icon' => 'users'],
                            ['number' => '15', 'suffix' => '', 'label' => 'Expert Trainers', 'icon' => 'star'],
                            ['number' => '50', 'suffix' => '+', 'label' => 'Classes Weekly', 'icon' => 'calendar'],
                            ['number' => '5', 'suffix' => '', 'label' => 'Star Rating', 'icon' => 'heart']
                        ]
                    ], 'style' => ['backgroundColor' => '#dc2626', 'textColor' => '#ffffff', 'paddingY' => 80]],
                    
                    ['type' => 'services', 'content' => [
                        'title' => 'Our Programs',
                        'subtitle' => 'Something for every fitness level',
                        'layout' => 'cards',
                        'columns' => 3,
                        'items' => [
                            ['title' => 'Personal Training', 'description' => 'One-on-one sessions with certified trainers tailored to your goals', 'icon' => 'user'],
                            ['title' => 'Group Classes', 'description' => 'High-energy classes including HIIT, yoga, spinning, and Zumba', 'icon' => 'users'],
                            ['title' => 'Strength Training', 'description' => 'Build muscle with our comprehensive weight training area', 'icon' => 'dumbbell'],
                            ['title' => 'Cardio Zone', 'description' => 'Treadmills, bikes, and ellipticals with entertainment screens', 'icon' => 'heart'],
                            ['title' => 'Nutrition Coaching', 'description' => 'Personalized meal plans to complement your training', 'icon' => 'apple'],
                            ['title' => 'Recovery Zone', 'description' => 'Sauna, steam room, and massage services', 'icon' => 'spa']
                        ]
                    ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                    
                    ['type' => 'pricing', 'content' => [
                        'title' => 'Membership Plans',
                        'subtitle' => 'Flexible options to fit your lifestyle',
                        'layout' => 'cards',
                        'plans' => [
                            ['name' => 'Basic', 'price' => 'K350', 'period' => '/month', 'features' => ['Gym access (6am-10pm)', 'Locker room access', 'Free fitness assessment'], 'buttonText' => 'Join Now'],
                            ['name' => 'Premium', 'price' => 'K650', 'period' => '/month', 'features' => ['24/7 gym access', 'All group classes', '2 PT sessions/month', 'Sauna & steam room', 'Nutrition consultation'], 'popular' => true, 'buttonText' => 'Join Now'],
                            ['name' => 'Elite', 'price' => 'K1,200', 'period' => '/month', 'features' => ['Everything in Premium', '8 PT sessions/month', 'Monthly massage', 'Priority class booking', 'Guest passes (2/month)'], 'buttonText' => 'Join Now']
                        ]
                    ], 'style' => ['backgroundColor' => '#fef2f2', 'paddingY' => 100]],
                    
                    ['type' => 'about', 'content' => [
                        'layout' => 'image-left',
                        'title' => 'Your Fitness Goals, Our Mission',
                        'description' => 'We believe everyone deserves to feel strong, healthy, and confident. Our certified trainers and state-of-the-art facility provide everything you need to succeed. Whether you\'re just starting out or training for competition, we\'re here to support your journey.',
                        'image' => 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=800&q=80',
                        'buttonText' => 'Meet Our Trainers',
                        'buttonLink' => '/team'
                    ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                    
                    ['type' => 'testimonials', 'content' => [
                        'layout' => 'carousel',
                        'title' => 'Success Stories',
                        'items' => [
                            ['name' => 'Sarah Mulenga', 'role' => 'Member since 2022', 'text' => 'Lost 20kg in 6 months! The trainers are amazing, the equipment is top-notch, and the community keeps me motivated every day.', 'rating' => 5],
                            ['name' => 'Peter Banda', 'role' => 'Member since 2021', 'text' => 'Best gym in Lusaka! The variety of classes and the supportive atmosphere make working out something I actually look forward to.', 'rating' => 5]
                        ]
                    ], 'style' => ['backgroundColor' => '#fef2f2', 'paddingY' => 80]],
                    
                    ['type' => 'cta-banner', 'content' => [
                        'layout' => 'centered',
                        'title' => 'Start Your Transformation Today',
                        'subtitle' => 'First week FREE for new members. No commitment required. See why 2000+ members chose PowerFit.',
                        'buttonText' => 'Claim Your Free Week',
                        'buttonLink' => '/contact',
                        'secondaryButtonText' => 'Take a Tour',
                        'secondaryButtonLink' => '#tour'
                    ], 'style' => ['backgroundColor' => '#dc2626', 'textColor' => '#ffffff', 'gradient' => true, 'paddingY' => 100]],
                ]],
            ]);
        }
    }
}
