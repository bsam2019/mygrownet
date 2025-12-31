<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use App\Models\GrowBuilder\SiteTemplateIndustry;
use Illuminate\Database\Seeder;

class SiteTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        // Create industries
        $this->createIndustries();
        
        // Create templates
        $this->createConsultingTemplate();
        $this->createRestaurantTemplate();
        $this->createSalonTemplate();
        $this->createLawFirmTemplate();
        $this->createGymTemplate();
    }

    private function createIndustries(): void
    {
        $industries = [
            ['name' => 'Business & Consulting', 'slug' => 'consulting', 'icon' => 'briefcase', 'sort_order' => 1],
            ['name' => 'Restaurant & Food', 'slug' => 'restaurant', 'icon' => 'utensils', 'sort_order' => 2],
            ['name' => 'Beauty & Wellness', 'slug' => 'beauty', 'icon' => 'sparkles', 'sort_order' => 3],
            ['name' => 'Legal & Professional', 'slug' => 'legal', 'icon' => 'scale', 'sort_order' => 4],
            ['name' => 'Fitness & Health', 'slug' => 'fitness', 'icon' => 'heart', 'sort_order' => 5],
            ['name' => 'Real Estate', 'slug' => 'realestate', 'icon' => 'home', 'sort_order' => 6],
            ['name' => 'Education', 'slug' => 'education', 'icon' => 'academic', 'sort_order' => 7],
            ['name' => 'E-Commerce', 'slug' => 'ecommerce', 'icon' => 'shopping', 'sort_order' => 8],
            ['name' => 'Healthcare & Medical', 'slug' => 'medical', 'icon' => 'medical', 'sort_order' => 9],
            ['name' => 'Creative & Photography', 'slug' => 'creative', 'icon' => 'camera', 'sort_order' => 10],
            ['name' => 'Printing & Branding', 'slug' => 'printing', 'icon' => 'printer', 'sort_order' => 11],
        ];

        foreach ($industries as $industry) {
            SiteTemplateIndustry::updateOrCreate(['slug' => $industry['slug']], $industry);
        }
    }

    private function createConsultingTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'consulting-pro'],
            [
                'name' => 'Consulting Pro',
                'description' => 'Elegant business consulting template with professional design, perfect for agencies and consultants.',
                'industry' => 'consulting',
                'thumbnail' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 1,
                'theme' => [
                    'primaryColor' => '#1e40af',
                    'secondaryColor' => '#0f172a',
                    'accentColor' => '#3b82f6',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'ConsultPro',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Free Consultation',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 ConsultPro. All rights reserved.',
                        'columns' => [
                            ['title' => 'Services', 'links' => [['label' => 'Strategy', 'url' => '/services'], ['label' => 'Digital', 'url' => '/services'], ['label' => 'Growth', 'url' => '/services']]],
                            ['title' => 'Company', 'links' => [['label' => 'About', 'url' => '/about'], ['label' => 'Team', 'url' => '/about'], ['label' => 'Careers', 'url' => '/contact']]],
                        ],
                    ],
                ],
            ]
        );

        $template->pages()->delete();
        
        // Home Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'split-right', 'title' => 'Transform Your Business With Expert Strategy', 'subtitle' => 'We partner with ambitious companies to unlock growth, optimize operations, and build sustainable competitive advantages.', 'buttonText' => 'Book Free Consultation', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Our Services', 'secondaryButtonLink' => '/services', 'image' => 'https://images.unsplash.com/photo-1553028826-f4804a6dba3b?w=800&q=80'], 'style' => ['backgroundColor' => '#0f172a', 'minHeight' => 600]],
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [['value' => '500+', 'label' => 'Projects Delivered'], ['value' => '98%', 'label' => 'Client Satisfaction'], ['value' => 'K75M+', 'label' => 'Revenue Generated'], ['value' => '15+', 'label' => 'Years Experience']]], 'style' => ['backgroundColor' => '#1e40af']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'How We Help You Succeed', 'subtitle' => 'Comprehensive solutions tailored to your unique challenges', 'items' => [['title' => 'Strategic Planning', 'description' => 'Data-driven strategies that align your vision with market opportunities.', 'icon' => 'chart'], ['title' => 'Digital Transformation', 'description' => 'Modernize your technology stack and customer experiences.', 'icon' => 'code'], ['title' => 'Operational Excellence', 'description' => 'Streamline processes and maximize efficiency across your organization.', 'icon' => 'cog']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'about', 'content' => ['layout' => 'image-left', 'title' => 'Why Leading Companies Choose Us', 'description' => 'For over 15 years, we\'ve helped businesses across Zambia transform challenges into opportunities. Our approach combines deep industry expertise with innovative thinking.', 'image' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80', 'features' => ['Proven track record with Fortune 500 companies', 'Dedicated team of certified professionals', 'Transparent pricing with measurable ROI']], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'testimonials', 'content' => ['layout' => 'carousel', 'title' => 'What Our Clients Say', 'items' => [['name' => 'Chanda Mwila', 'role' => 'CEO, Mwila Technologies', 'text' => 'Their strategic guidance helped us achieve 300% growth in just one year. Exceptional team!', 'rating' => 5], ['name' => 'Grace Banda', 'role' => 'Founder, Banda Ventures', 'text' => 'Professional, thorough, and genuinely invested in our success. Highly recommended.', 'rating' => 5]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Ready to Accelerate Your Growth?', 'description' => 'Book a free 30-minute strategy session with our senior consultants.', 'buttonText' => 'Schedule Free Call', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#1e40af']],
            ]],
        ]);

        // About Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'About',
            'slug' => 'about',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'About Us', 'subtitle' => 'Building tomorrow\'s leaders today', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 220]],
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Our Story', 'description' => 'Founded in 2009, ConsultPro began with a simple mission: make world-class business consulting accessible to Zambian companies. Today, we\'ve helped over 500 businesses transform their operations and achieve breakthrough results.', 'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Our Values', 'items' => [['title' => 'Excellence', 'description' => 'We pursue the highest standards in everything we do.'], ['title' => 'Integrity', 'description' => 'Honesty and transparency in every relationship.'], ['title' => 'Innovation', 'description' => 'Continuously seeking better solutions.'], ['title' => 'Impact', 'description' => 'Measuring success by the difference we make.']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'team', 'content' => ['layout' => 'social', 'title' => 'Meet Our Leadership', 'items' => [['name' => 'David Mulenga', 'role' => 'Founder & CEO', 'bio' => '20+ years in strategic consulting', 'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80'], ['name' => 'Chipo Banda', 'role' => 'Chief Operations Officer', 'bio' => 'Operations excellence expert', 'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&q=80'], ['name' => 'Emmanuel Phiri', 'role' => 'Head of Strategy', 'bio' => 'Former McKinsey consultant', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);

        // Services Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Services',
            'slug' => 'services',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Services', 'subtitle' => 'Comprehensive solutions for every business challenge', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 220]],
                ['type' => 'services', 'content' => ['layout' => 'alternating', 'title' => 'What We Offer', 'items' => [['title' => 'Strategic Advisory', 'description' => 'C-suite consulting that aligns strategy with execution. We help you identify opportunities, mitigate risks, and build sustainable competitive advantages.', 'icon' => 'briefcase', 'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=600&q=80'], ['title' => 'Digital Transformation', 'description' => 'End-to-end modernization of your technology infrastructure, processes, and customer experiences for the digital age.', 'icon' => 'code', 'image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=600&q=80'], ['title' => 'Market Expansion', 'description' => 'Research-driven strategies to enter new markets, launch products, and grow your customer base effectively.', 'icon' => 'globe', 'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Engagement Models', 'plans' => [['name' => 'Advisory', 'price' => 'K5,000/mo', 'features' => ['Monthly strategy sessions', 'Email support', 'Quarterly reviews'], 'buttonText' => 'Get Started'], ['name' => 'Partnership', 'price' => 'K15,000/mo', 'popular' => true, 'features' => ['Weekly consultations', 'Priority support', 'Dedicated consultant', 'Monthly reports'], 'buttonText' => 'Most Popular'], ['name' => 'Enterprise', 'price' => 'Custom', 'features' => ['Full-time team', '24/7 support', 'On-site presence', 'Custom solutions'], 'buttonText' => 'Contact Us']]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Get in Touch', 'subtitle' => 'Let\'s discuss how we can help your business grow', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 220]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Send Us a Message', 'description' => 'Fill out the form and our team will respond within 24 hours.', 'showForm' => true, 'email' => 'hello@consultpro.co.zm', 'phone' => '+260 97 123 4567', 'address' => 'Plot 123, Cairo Road, Lusaka, Zambia'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'map', 'content' => ['title' => 'Visit Our Office', 'address' => 'Plot 123, Cairo Road, Lusaka, Zambia', 'showAddress' => true], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);
    }

    private function createRestaurantTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'savanna-restaurant'],
            [
                'name' => 'Savanna Restaurant',
                'description' => 'Warm and inviting restaurant template with menu showcase, reservations, and gallery.',
                'industry' => 'restaurant',
                'thumbnail' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 2,
                'theme' => [
                    'primaryColor' => '#92400e',
                    'secondaryColor' => '#1c1917',
                    'accentColor' => '#d97706',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'Savanna',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Reserve Table',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Savanna Restaurant. All rights reserved.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'A Taste of Zambian Excellence', 'subtitle' => 'Experience authentic flavors crafted with passion, served in an atmosphere of warmth and elegance.', 'buttonText' => 'View Our Menu', 'buttonLink' => '/menu', 'secondaryButtonText' => 'Reserve a Table', 'secondaryButtonLink' => '/contact', 'backgroundImage' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1200&q=80', 'overlayColor' => 'black', 'overlayOpacity' => 50], 'style' => ['minHeight' => 650]],
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Our Story', 'description' => 'Nestled in the heart of Lusaka, Savanna brings together the finest local ingredients with culinary traditions passed down through generations. Every dish tells a story of our rich heritage.', 'image' => 'https://images.unsplash.com/photo-1600891964092-4316c288032e?w=800&q=80'], 'style' => ['backgroundColor' => '#fef3c7']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why Dine With Us', 'items' => [['title' => 'Farm Fresh', 'description' => 'Locally sourced ingredients from Zambian farms'], ['title' => 'Master Chefs', 'description' => 'Award-winning culinary team'], ['title' => 'Warm Ambiance', 'description' => 'Elegant setting for any occasion'], ['title' => 'Private Events', 'description' => 'Perfect venue for celebrations']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'Our Signature Dishes', 'images' => [['url' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80', 'alt' => 'Grilled steak'], ['url' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=600&q=80', 'alt' => 'Fresh salad'], ['url' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80', 'alt' => 'Pizza'], ['url' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=600&q=80', 'alt' => 'Pancakes'], ['url' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=600&q=80', 'alt' => 'Dessert'], ['url' => 'https://images.unsplash.com/photo-1482049016gy-2d1ec7ab7445?w=600&q=80', 'alt' => 'Appetizer']]], 'style' => ['backgroundColor' => '#1c1917']],
                ['type' => 'testimonials', 'content' => ['layout' => 'grid', 'title' => 'Guest Reviews', 'items' => [['name' => 'Mutale Chisanga', 'text' => 'The best dining experience in Lusaka! The nshima with village chicken is absolutely divine.', 'rating' => 5], ['name' => 'Peter Mwanza', 'text' => 'Perfect for our anniversary dinner. Romantic atmosphere and impeccable service.', 'rating' => 5], ['name' => 'Sarah Tembo', 'text' => 'Finally, a restaurant that celebrates Zambian cuisine with such elegance!', 'rating' => 5]]], 'style' => ['backgroundColor' => '#fef3c7']],
                ['type' => 'cta', 'content' => ['layout' => 'split', 'title' => 'Reserve Your Table', 'description' => 'Join us for an unforgettable dining experience. Book now to secure your spot.', 'buttonText' => 'Make Reservation', 'buttonLink' => '/contact', 'image' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=600&q=80'], 'style' => ['backgroundColor' => '#92400e']],
            ]],
        ]);

        // Menu Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Menu',
            'slug' => 'menu',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Menu', 'subtitle' => 'Crafted with love, served with pride', 'backgroundColor' => '#1c1917', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Starters', 'plans' => [['name' => 'Kapenta Fritters', 'price' => 'K85', 'features' => ['Crispy dried fish', 'Tomato relish', 'Fresh herbs']], ['name' => 'Pumpkin Soup', 'price' => 'K65', 'features' => ['Creamy local pumpkin', 'Toasted seeds', 'Crusty bread']], ['name' => 'Garden Salad', 'price' => 'K55', 'features' => ['Fresh vegetables', 'House dressing', 'Grilled halloumi']]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Main Courses', 'plans' => [['name' => 'Village Chicken', 'price' => 'K185', 'popular' => true, 'features' => ['Free-range chicken', 'Traditional nshima', 'Seasonal vegetables']], ['name' => 'Grilled Bream', 'price' => 'K165', 'features' => ['Lake Kariba bream', 'Lemon butter sauce', 'Rice pilaf']], ['name' => 'Beef Stew', 'price' => 'K175', 'features' => ['Slow-cooked beef', 'Root vegetables', 'Creamy polenta']]]], 'style' => ['backgroundColor' => '#fef3c7']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Special Dietary Requirements?', 'description' => 'We accommodate vegetarian, vegan, and gluten-free diets. Just let us know!', 'buttonText' => 'Contact Us', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#92400e']],
            ]],
        ]);

        // About Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'About',
            'slug' => 'about',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Story', 'subtitle' => 'A journey of flavor and tradition', 'backgroundColor' => '#1c1917', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'about', 'content' => ['layout' => 'image-left', 'title' => 'From Our Family to Yours', 'description' => 'Savanna was born from a dream to share the rich culinary heritage of Zambia with the world. Founded by Chef Mwamba in 2015, we\'ve grown from a small family kitchen to one of Lusaka\'s most beloved dining destinations.', 'image' => 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=800&q=80'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'team', 'content' => ['layout' => 'grid', 'title' => 'Meet Our Team', 'items' => [['name' => 'Chef Mwamba', 'role' => 'Executive Chef & Founder', 'image' => 'https://images.unsplash.com/photo-1583394293214-28ez9ce8cec?w=400&q=80'], ['name' => 'Thandiwe Phiri', 'role' => 'Head Pastry Chef', 'image' => 'https://images.unsplash.com/photo-1595273670150-bd0c3c392e46?w=400&q=80']]], 'style' => ['backgroundColor' => '#fef3c7']],
            ]],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Reservations', 'subtitle' => 'Book your table today', 'backgroundColor' => '#1c1917', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Make a Reservation', 'description' => 'For parties of 8 or more, please call us directly.', 'showForm' => true, 'email' => 'reservations@savanna.co.zm', 'phone' => '+260 97 555 1234', 'address' => 'Plot 45, Kabulonga Road, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Opening Hours', 'items' => [['title' => 'Lunch', 'description' => 'Tue-Sun: 11:30 AM - 3:00 PM'], ['title' => 'Dinner', 'description' => 'Tue-Sun: 6:00 PM - 10:30 PM'], ['title' => 'Closed', 'description' => 'Mondays'], ['title' => 'Private Events', 'description' => 'Available on request']]], 'style' => ['backgroundColor' => '#fef3c7']],
            ]],
        ]);
    }

    private function createSalonTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'luxe-salon'],
            [
                'name' => 'Luxe Salon',
                'description' => 'Sophisticated beauty salon template with service showcase, booking, and gallery.',
                'industry' => 'beauty',
                'thumbnail' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 3,
                'theme' => [
                    'primaryColor' => '#be185d',
                    'secondaryColor' => '#1f2937',
                    'accentColor' => '#ec4899',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'LUXE',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Book Now',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Luxe Salon & Spa. All rights reserved.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Where Beauty Meets Luxury', 'subtitle' => 'Indulge in premium hair, beauty, and wellness services in Lusaka\'s most elegant salon.', 'buttonText' => 'Book Appointment', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Our Services', 'secondaryButtonLink' => '/services', 'backgroundImage' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=1200&q=80', 'overlayColor' => 'gradient', 'overlayGradientFrom' => '#be185d', 'overlayGradientTo' => '#7c3aed', 'overlayOpacity' => 60], 'style' => ['minHeight' => 600]],
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'Our Services', 'subtitle' => 'Premium treatments for your complete transformation', 'items' => [['title' => 'Hair Styling', 'description' => 'Cuts, color, treatments, and styling by expert stylists.', 'image' => 'https://images.unsplash.com/photo-1562322140-8baeececf3df?w=600&q=80'], ['title' => 'Nail Art', 'description' => 'Manicures, pedicures, and stunning nail designs.', 'image' => 'https://images.unsplash.com/photo-1604654894610-df63bc536371?w=600&q=80'], ['title' => 'Spa & Wellness', 'description' => 'Massages, facials, and relaxation therapies.', 'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'about', 'content' => ['layout' => 'image-left', 'title' => 'Experience the Luxe Difference', 'description' => 'At Luxe, we believe everyone deserves to feel beautiful. Our team of internationally trained stylists and therapists use only premium products to deliver results that exceed expectations.', 'image' => 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?w=800&q=80', 'features' => ['Premium international products', 'Trained & certified professionals', 'Relaxing, luxurious environment']], 'style' => ['backgroundColor' => '#fdf2f8']],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Our Work', 'columns' => 4, 'images' => [['url' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=400&q=80'], ['url' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=400&q=80'], ['url' => 'https://images.unsplash.com/photo-1595476108010-b4d1f102b1b1?w=400&q=80'], ['url' => 'https://images.unsplash.com/photo-1519699047748-de8e457a634e?w=400&q=80']]], 'style' => ['backgroundColor' => '#1f2937']],
                ['type' => 'testimonials', 'content' => ['layout' => 'single', 'title' => 'Client Love', 'items' => [['name' => 'Natasha Mwanza', 'text' => 'Luxe is my happy place! The team always makes me feel like a queen. Best salon in Lusaka, hands down!', 'role' => 'Regular Client', 'rating' => 5]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Ready for Your Transformation?', 'description' => 'Book your appointment today and let us pamper you.', 'buttonText' => 'Book Now', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#be185d']],
            ]],
        ]);

        // Services Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Services',
            'slug' => 'services',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Services', 'subtitle' => 'Treatments designed to make you shine', 'backgroundColor' => '#1f2937', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Hair Services', 'plans' => [['name' => 'Haircut & Style', 'price' => 'From K150', 'features' => ['Consultation', 'Precision cut', 'Blow dry & style']], ['name' => 'Color & Highlights', 'price' => 'From K350', 'popular' => true, 'features' => ['Color consultation', 'Premium products', 'Conditioning treatment']], ['name' => 'Braids & Extensions', 'price' => 'From K500', 'features' => ['Various styles', 'Quality hair', 'Long-lasting results']]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Nail Services', 'plans' => [['name' => 'Classic Manicure', 'price' => 'K80', 'features' => ['Nail shaping', 'Cuticle care', 'Polish application']], ['name' => 'Gel Manicure', 'price' => 'K150', 'popular' => true, 'features' => ['Long-lasting gel', 'Chip-resistant', '2+ weeks wear']], ['name' => 'Nail Art', 'price' => 'From K200', 'features' => ['Custom designs', 'Gems & glitter', 'Unique creations']]]], 'style' => ['backgroundColor' => '#fdf2f8']],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Spa & Wellness', 'plans' => [['name' => 'Express Facial', 'price' => 'K200', 'features' => ['30 minutes', 'Deep cleanse', 'Hydration boost']], ['name' => 'Full Body Massage', 'price' => 'K350', 'popular' => true, 'features' => ['60 minutes', 'Relaxation', 'Aromatherapy']], ['name' => 'Luxe Package', 'price' => 'K800', 'features' => ['Facial + Massage', 'Mani-Pedi', 'Champagne']]]], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);

        // Gallery Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Gallery',
            'slug' => 'gallery',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Gallery', 'subtitle' => 'See our beautiful work', 'backgroundColor' => '#1f2937', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'Hair Transformations', 'images' => [['url' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1562322140-8baeececf3df?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1595476108010-b4d1f102b1b1?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1519699047748-de8e457a634e?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Book Now',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Book Your Appointment', 'subtitle' => 'Your transformation awaits', 'backgroundColor' => '#1f2937', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Get in Touch', 'description' => 'Book online or call us to schedule your appointment.', 'showForm' => true, 'email' => 'hello@luxesalon.co.zm', 'phone' => '+260 97 888 9999', 'address' => 'Shop 12, Manda Hill Mall, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Opening Hours', 'items' => [['title' => 'Monday - Friday', 'description' => '9:00 AM - 7:00 PM'], ['title' => 'Saturday', 'description' => '9:00 AM - 6:00 PM'], ['title' => 'Sunday', 'description' => '10:00 AM - 4:00 PM'], ['title' => 'Public Holidays', 'description' => 'By appointment only']]], 'style' => ['backgroundColor' => '#fdf2f8']],
            ]],
        ]);
    }

    private function createLawFirmTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'legal-edge'],
            [
                'name' => 'Legal Edge',
                'description' => 'Professional law firm template with practice areas, attorney profiles, and consultation booking.',
                'industry' => 'legal',
                'thumbnail' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 4,
                'theme' => [
                    'primaryColor' => '#1e3a5f',
                    'secondaryColor' => '#0f172a',
                    'accentColor' => '#c9a227',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'Legal Edge',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Free Consultation',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Legal Edge Advocates. All rights reserved.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'split-left', 'title' => 'Justice. Integrity. Results.', 'subtitle' => 'Zambia\'s trusted legal partners. We fight for your rights with unwavering dedication and proven expertise.', 'buttonText' => 'Free Consultation', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Our Practice Areas', 'secondaryButtonLink' => '/services', 'image' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=800&q=80'], 'style' => ['backgroundColor' => '#0f172a', 'minHeight' => 600]],
                ['type' => 'stats', 'content' => ['layout' => 'grid', 'items' => [['value' => '25+', 'label' => 'Years Experience'], ['value' => '5,000+', 'label' => 'Cases Won'], ['value' => '98%', 'label' => 'Success Rate'], ['value' => '24/7', 'label' => 'Client Support']]], 'style' => ['backgroundColor' => '#1e3a5f']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Practice Areas', 'subtitle' => 'Comprehensive legal services for individuals and businesses', 'items' => [['title' => 'Corporate Law', 'description' => 'Business formation, contracts, mergers & acquisitions, and corporate governance.', 'icon' => 'briefcase'], ['title' => 'Family Law', 'description' => 'Divorce, custody, adoption, and family dispute resolution.', 'icon' => 'users'], ['title' => 'Criminal Defense', 'description' => 'Aggressive defense for all criminal charges and investigations.', 'icon' => 'shield'], ['title' => 'Property Law', 'description' => 'Real estate transactions, land disputes, and property rights.', 'icon' => 'home'], ['title' => 'Employment Law', 'description' => 'Workplace disputes, contracts, and labor law compliance.', 'icon' => 'cog'], ['title' => 'Immigration', 'description' => 'Visas, work permits, citizenship, and immigration appeals.', 'icon' => 'globe']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Why Choose Legal Edge?', 'description' => 'For over 25 years, Legal Edge has been at the forefront of Zambian law. Our team of experienced advocates combines deep legal knowledge with a genuine commitment to our clients\' success. We don\'t just practice law—we fight for justice.', 'image' => 'https://images.unsplash.com/photo-1521791055366-0d553872125f?w=800&q=80', 'features' => ['Senior advocates with 25+ years experience', 'Proven track record of successful outcomes', 'Transparent fees with no hidden costs', 'Personalized attention to every case']], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'testimonials', 'content' => ['layout' => 'grid', 'title' => 'Client Testimonials', 'items' => [['name' => 'James Mwale', 'text' => 'Legal Edge handled my corporate dispute with professionalism and secured a favorable outcome. Highly recommended!', 'role' => 'Business Owner', 'rating' => 5], ['name' => 'Mary Banda', 'text' => 'During my divorce, they were compassionate yet fierce advocates. I couldn\'t have asked for better representation.', 'role' => 'Private Client', 'rating' => 5], ['name' => 'Chilufya Mining Ltd', 'text' => 'Our go-to firm for all corporate matters. Their expertise in mining law is unmatched in Zambia.', 'role' => 'Corporate Client', 'rating' => 5]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Need Legal Assistance?', 'description' => 'Schedule a free 30-minute consultation with one of our senior advocates.', 'buttonText' => 'Book Free Consultation', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#1e3a5f']],
            ]],
        ]);

        // Practice Areas Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Practice Areas',
            'slug' => 'services',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Practice Areas', 'subtitle' => 'Expert legal services across all major areas of law', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 220]],
                ['type' => 'services', 'content' => ['layout' => 'alternating', 'title' => 'Our Expertise', 'items' => [['title' => 'Corporate & Commercial Law', 'description' => 'From startup formation to complex M&A transactions, we guide businesses through every legal challenge. Our corporate team has advised on deals worth over K500 million.', 'icon' => 'briefcase', 'image' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=600&q=80'], ['title' => 'Litigation & Dispute Resolution', 'description' => 'When disputes arise, you need advocates who will fight for you. Our litigation team has a 98% success rate in court proceedings and arbitration.', 'icon' => 'scale', 'image' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=600&q=80'], ['title' => 'Property & Real Estate', 'description' => 'Navigate complex property transactions with confidence. We handle everything from residential purchases to large-scale commercial developments.', 'icon' => 'home', 'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'faq', 'content' => ['layout' => 'accordion', 'title' => 'Common Questions', 'items' => [['question' => 'How much does a consultation cost?', 'answer' => 'Your first consultation is completely free. We believe in understanding your case before discussing fees.'], ['question' => 'How long will my case take?', 'answer' => 'Every case is unique. During your consultation, we\'ll provide a realistic timeline based on the specifics of your situation.'], ['question' => 'Do you offer payment plans?', 'answer' => 'Yes, we offer flexible payment arrangements for qualifying clients. We believe access to justice shouldn\'t be limited by finances.']]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);

        // Team Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Our Team',
            'slug' => 'team',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Attorneys', 'subtitle' => 'Meet the advocates fighting for you', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 220]],
                ['type' => 'team', 'content' => ['layout' => 'social', 'title' => 'Senior Partners', 'items' => [['name' => 'Advocate John Mwamba, SC', 'role' => 'Managing Partner', 'bio' => '30 years experience. Former High Court Judge. Specializes in corporate and constitutional law.', 'image' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&q=80', 'linkedin' => '#', 'email' => 'jmwamba@legaledge.co.zm'], ['name' => 'Advocate Grace Tembo', 'role' => 'Senior Partner', 'bio' => '25 years experience. Leading family law practitioner. Certified mediator.', 'image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&q=80', 'linkedin' => '#', 'email' => 'gtembo@legaledge.co.zm'], ['name' => 'Advocate Peter Sakala', 'role' => 'Partner', 'bio' => '20 years experience. Criminal defense specialist. Former prosecutor.', 'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80', 'linkedin' => '#', 'email' => 'psakala@legaledge.co.zm']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Join Our Team', 'description' => 'We\'re always looking for talented legal professionals.', 'buttonText' => 'View Careers', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#1e3a5f']],
            ]],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Contact Us', 'subtitle' => 'Schedule your free consultation today', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 220]],
                ['type' => 'contact', 'content' => ['layout' => 'with-map', 'title' => 'Get in Touch', 'description' => 'Fill out the form for a free case evaluation, or call us directly.', 'showForm' => true, 'email' => 'info@legaledge.co.zm', 'phone' => '+260 21 123 4567', 'address' => 'Legal Edge Chambers, Stand 1234, Cairo Road, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Office Hours', 'items' => [['title' => 'Weekdays', 'description' => '8:00 AM - 5:00 PM'], ['title' => 'Saturday', 'description' => '9:00 AM - 1:00 PM'], ['title' => 'Emergency', 'description' => '24/7 Hotline Available'], ['title' => 'Consultations', 'description' => 'By Appointment']]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);
    }

    private function createGymTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'fitzone-gym'],
            [
                'name' => 'FitZone Gym',
                'description' => 'Energetic fitness center template with class schedules, membership plans, and trainer profiles.',
                'industry' => 'fitness',
                'thumbnail' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 5,
                'theme' => [
                    'primaryColor' => '#dc2626',
                    'secondaryColor' => '#111827',
                    'accentColor' => '#f97316',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'FITZONE',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Join Now',
                        'ctaLink' => '/pricing',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 FitZone Gym. All rights reserved.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'TRANSFORM YOUR BODY. TRANSFORM YOUR LIFE.', 'subtitle' => 'Lusaka\'s premier fitness destination. State-of-the-art equipment, expert trainers, and a community that pushes you to be your best.', 'buttonText' => 'Start Free Trial', 'buttonLink' => '/pricing', 'secondaryButtonText' => 'View Classes', 'secondaryButtonLink' => '/classes', 'backgroundImage' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1200&q=80', 'overlayColor' => 'black', 'overlayOpacity' => 60], 'style' => ['minHeight' => 650]],
                ['type' => 'stats', 'content' => ['layout' => 'icons', 'items' => [['icon' => 'users', 'value' => '5,000+', 'label' => 'Active Members'], ['icon' => 'star', 'value' => '50+', 'label' => 'Weekly Classes'], ['icon' => 'heart', 'value' => '15', 'label' => 'Expert Trainers'], ['icon' => 'chart', 'value' => '24/7', 'label' => 'Gym Access']]], 'style' => ['backgroundColor' => '#dc2626']],
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'What We Offer', 'subtitle' => 'Everything you need to reach your fitness goals', 'items' => [['title' => 'Strength Training', 'description' => 'Full range of free weights, machines, and functional training equipment.', 'image' => 'https://images.unsplash.com/photo-1581009146145-b5ef050c149a?w=600&q=80'], ['title' => 'Cardio Zone', 'description' => 'Treadmills, bikes, rowing machines, and more with personal screens.', 'image' => 'https://images.unsplash.com/photo-1576678927484-cc907957088c?w=600&q=80'], ['title' => 'Group Classes', 'description' => 'Yoga, HIIT, spinning, Zumba, and more led by certified instructors.', 'image' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'checklist', 'title' => 'Why FitZone?', 'items' => [['title' => '24/7 Access', 'description' => 'Work out on your schedule, any time day or night'], ['title' => 'Personal Training', 'description' => 'One-on-one sessions with certified trainers'], ['title' => 'Modern Equipment', 'description' => 'Latest machines from top fitness brands'], ['title' => 'Clean Facilities', 'description' => 'Sanitized equipment and spotless locker rooms'], ['title' => 'Free Parking', 'description' => 'Convenient parking for all members'], ['title' => 'Nutrition Guidance', 'description' => 'Diet plans and supplement advice']]], 'style' => ['backgroundColor' => '#f3f4f6']],
                ['type' => 'testimonials', 'content' => ['layout' => 'carousel', 'title' => 'Success Stories', 'items' => [['name' => 'Mwansa Chanda', 'text' => 'Lost 25kg in 6 months! The trainers at FitZone changed my life. Best decision I ever made.', 'role' => 'Lost 25kg', 'rating' => 5], ['name' => 'Peter Mumba', 'text' => 'Finally found a gym that feels like family. The community here keeps me motivated every day.', 'role' => 'Member since 2021', 'rating' => 5]]], 'style' => ['backgroundColor' => '#111827']],
                ['type' => 'cta', 'content' => ['layout' => 'split', 'title' => 'Ready to Start Your Journey?', 'description' => 'Join today and get your first week FREE. No commitment required.', 'buttonText' => 'Claim Free Week', 'buttonLink' => '/pricing', 'image' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=600&q=80'], 'style' => ['backgroundColor' => '#dc2626']],
            ]],
        ]);

        // Classes Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Classes',
            'slug' => 'classes',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Group Classes', 'subtitle' => 'Find your perfect workout', 'backgroundColor' => '#111827', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Our Classes', 'items' => [['title' => 'HIIT Blast', 'description' => 'High-intensity interval training. Burn maximum calories in 45 minutes.', 'icon' => 'fire'], ['title' => 'Power Yoga', 'description' => 'Build strength and flexibility with dynamic yoga flows.', 'icon' => 'sparkles'], ['title' => 'Spin Class', 'description' => 'Indoor cycling with pumping music and motivating instructors.', 'icon' => 'heart'], ['title' => 'Zumba', 'description' => 'Dance your way to fitness with Latin-inspired moves.', 'icon' => 'music'], ['title' => 'Boxing Fit', 'description' => 'Learn boxing techniques while getting an incredible workout.', 'icon' => 'fist'], ['title' => 'Core Crusher', 'description' => 'Focused ab and core strengthening for a solid foundation.', 'icon' => 'target']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Class Schedule', 'items' => [['title' => 'Morning Classes', 'description' => '5:30 AM, 6:30 AM, 7:30 AM'], ['title' => 'Afternoon Classes', 'description' => '12:00 PM, 1:00 PM'], ['title' => 'Evening Classes', 'description' => '5:00 PM, 6:00 PM, 7:00 PM'], ['title' => 'Weekend Classes', 'description' => '8:00 AM, 9:00 AM, 10:00 AM']]], 'style' => ['backgroundColor' => '#f3f4f6']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'First Class Free!', 'description' => 'Try any class with no obligation.', 'buttonText' => 'Book a Class', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#dc2626']],
            ]],
        ]);

        // Pricing Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Membership',
            'slug' => 'pricing',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Membership Plans', 'subtitle' => 'Invest in yourself', 'backgroundColor' => '#111827', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Choose Your Plan', 'plans' => [['name' => 'Basic', 'price' => 'K350/mo', 'features' => ['Gym access (6AM-10PM)', 'Locker room access', 'Free WiFi', 'Fitness assessment'], 'buttonText' => 'Get Started'], ['name' => 'Premium', 'price' => 'K550/mo', 'popular' => true, 'features' => ['24/7 gym access', 'All group classes', 'Locker room + towels', '1 PT session/month', 'Nutrition consultation'], 'buttonText' => 'Most Popular'], ['name' => 'Elite', 'price' => 'K850/mo', 'features' => ['Everything in Premium', '4 PT sessions/month', 'Guest passes (2/month)', 'Priority class booking', 'Exclusive member events'], 'buttonText' => 'Go Elite']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'All Memberships Include', 'items' => [['title' => 'No Joining Fee', 'description' => 'Start with zero upfront cost'], ['title' => 'Cancel Anytime', 'description' => 'No long-term contracts'], ['title' => 'Free Parking', 'description' => 'Convenient member parking'], ['title' => 'Mobile App', 'description' => 'Track workouts and book classes']]], 'style' => ['backgroundColor' => '#f3f4f6']],
                ['type' => 'faq', 'content' => ['layout' => 'accordion', 'title' => 'Membership FAQ', 'items' => [['question' => 'Can I freeze my membership?', 'answer' => 'Yes, you can freeze for up to 3 months per year at no extra cost.'], ['question' => 'Is there a student discount?', 'answer' => 'Yes! Students get 20% off any membership with valid ID.'], ['question' => 'Can I bring a friend?', 'answer' => 'Elite members get 2 guest passes per month. Others can purchase day passes.']]], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Get in Touch', 'subtitle' => 'We\'re here to help you start your fitness journey', 'backgroundColor' => '#111827', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Contact Us', 'description' => 'Have questions? We\'d love to hear from you.', 'showForm' => true, 'email' => 'info@fitzone.co.zm', 'phone' => '+260 97 777 8888', 'address' => 'FitZone Gym, Plot 789, Great East Road, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Opening Hours', 'items' => [['title' => 'Gym Floor', 'description' => '24/7 for Premium & Elite'], ['title' => 'Reception', 'description' => '5:00 AM - 10:00 PM'], ['title' => 'Classes', 'description' => 'See class schedule'], ['title' => 'Personal Training', 'description' => 'By appointment']]], 'style' => ['backgroundColor' => '#f3f4f6']],
            ]],
        ]);
    }
}
