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
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'TechFlow',
                        'sticky' => true,
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
        
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'split-right', 'title' => 'Ship Products 10x Faster with AI', 'subtitle' => 'The all-in-one platform for modern development teams. Build, deploy, and scale applications with intelligent automation.', 'buttonText' => 'Start Free Trial', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Book a Demo', 'secondaryButtonLink' => '/contact', 'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80'], 'style' => ['backgroundColor' => '#0f172a', 'minHeight' => 650]],
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [['value' => '10K+', 'label' => 'Companies'], ['value' => '99.99%', 'label' => 'Uptime SLA'], ['value' => '2.5B+', 'label' => 'Requests/Day'], ['value' => '<100ms', 'label' => 'Avg Response']]], 'style' => ['backgroundColor' => '#6366f1']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Everything You Need to Ship Faster', 'subtitle' => 'Built for modern development workflows', 'items' => [['title' => 'Git Integration', 'description' => 'Connect GitHub, GitLab, or Bitbucket. Auto-deploy on every push with preview environments.', 'icon' => 'code'], ['title' => 'Global Edge Network', 'description' => 'Deploy to 200+ locations worldwide. Sub-50ms response times with automatic CDN.', 'icon' => 'server'], ['title' => 'Managed Databases', 'description' => 'PostgreSQL, MySQL, Redis, and MongoDB. Automatic backups and scaling.', 'icon' => 'database']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'about', 'content' => ['layout' => 'image-left', 'title' => 'Stop Wasting Time on DevOps', 'description' => 'Traditional deployment pipelines are slow, complex, and error-prone. TechFlow automates your entire workflow - from code commit to production deployment - in minutes, not hours.', 'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80', 'features' => ['Deploy in under 2 minutes', 'Automatic SSL certificates', 'Zero-downtime deployments']], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'testimonials', 'content' => ['layout' => 'carousel', 'title' => 'What Developers Say', 'items' => [['name' => 'Sarah Chen', 'role' => 'CTO, StartupCo', 'text' => 'TechFlow reduced our deployment time from hours to minutes. Game changer!', 'rating' => 5], ['name' => 'John Smith', 'role' => 'Lead Developer', 'text' => 'Best developer experience I\'ve ever had. Everything just works.', 'rating' => 5]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Start Shipping Faster Today', 'description' => 'Join 10,000+ developers who trust TechFlow. No credit card required.', 'buttonText' => 'Start Free Trial', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#6366f1']],
            ]],
        ]);
    }


    private function createLuxuryRestaurantTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'luxury-restaurant-pro'],
            [
                'name' => 'Luxury Restaurant Pro',
                'description' => 'Elegant restaurant website with parallax scrolling, image galleries, and sophisticated animations. Perfect for fine dining establishments.',
                'industry' => 'restaurant',
                'thumbnail' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&q=80',
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 2,
                'theme' => [
                    'primaryColor' => '#d97706',
                    'secondaryColor' => '#1c1917',
                    'accentColor' => '#fbbf24',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'La Maison',
                        'sticky' => true,
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
        
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'An Unforgettable Culinary Experience', 'subtitle' => 'Michelin-starred cuisine in the heart of Lusaka. Reservations recommended.', 'buttonText' => 'Reserve Your Table', 'buttonLink' => '/contact', 'secondaryButtonText' => 'View Menu', 'secondaryButtonLink' => '/menu', 'backgroundImage' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1200&q=80', 'overlayColor' => 'black', 'overlayOpacity' => 60], 'style' => ['minHeight' => 700]],
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Where Tradition Meets Innovation', 'description' => 'For over 20 years, La Maison has been crafting exceptional dining experiences. Our award-winning chef combines classical French techniques with modern innovation, using only the finest locally-sourced ingredients.', 'image' => 'https://images.unsplash.com/photo-1600565193348-f74bd3c7ccdf?w=800&q=80', 'features' => ['Michelin-starred chef', 'Farm-to-table ingredients', 'Award-winning wine list']], 'style' => ['backgroundColor' => '#1c1917']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Our Signature Dishes', 'subtitle' => 'Crafted with passion, served with elegance', 'items' => [['title' => 'Grilled Bream', 'description' => 'Fresh Lake Kariba bream with lemon butter sauce', 'icon' => 'sparkles'], ['title' => 'Beef Wellington', 'description' => 'Tender beef wrapped in puff pastry with mushroom duxelles', 'icon' => 'sparkles'], ['title' => 'Chocolate Soufflé', 'description' => 'Light and airy chocolate dessert with vanilla ice cream', 'icon' => 'sparkles']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'A Feast for the Eyes', 'images' => [['url' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=600&q=80']]], 'style' => ['backgroundColor' => '#0a0a0a']],
                ['type' => 'testimonials', 'content' => ['layout' => 'carousel', 'title' => 'What Our Guests Say', 'items' => [['name' => 'Sarah Johnson', 'role' => 'Food Critic', 'text' => 'An absolutely extraordinary dining experience. Every dish was a masterpiece.', 'rating' => 5], ['name' => 'Michael Chen', 'role' => 'Regular Guest', 'text' => 'The attention to detail is impeccable. Best restaurant in Lusaka.', 'rating' => 5]]], 'style' => ['backgroundColor' => '#1c1917']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Reserve Your Table Today', 'description' => 'Experience culinary excellence. Limited seating available.', 'buttonText' => 'Make Reservation', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#d97706']],
            ]],
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
        
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'We Create Digital Experiences That Matter', 'subtitle' => 'Award-winning design studio crafting brands, websites, and digital products for forward-thinking companies.', 'buttonText' => 'View Our Work', 'buttonLink' => '/portfolio', 'secondaryButtonText' => 'Start a Project', 'secondaryButtonLink' => '/contact', 'backgroundImage' => 'https://images.unsplash.com/photo-1558655146-9f40138edfeb?w=1200&q=80', 'overlayColor' => 'gradient', 'overlayOpacity' => 40], 'style' => ['minHeight' => 700]],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'What We Do', 'subtitle' => 'Full-service creative solutions', 'items' => [['title' => 'Brand Identity', 'description' => 'Crafting memorable brands that stand out and resonate with your audience.', 'icon' => 'sparkles'], ['title' => 'Web Design', 'description' => 'Beautiful, responsive websites that convert visitors into customers.', 'icon' => 'code'], ['title' => 'App Development', 'description' => 'Native and cross-platform apps that users love to use.', 'icon' => 'mobile'], ['title' => 'Motion Design', 'description' => 'Engaging animations and video content that tells your story.', 'icon' => 'video'], ['title' => 'Digital Marketing', 'description' => 'Data-driven campaigns that grow your business.', 'icon' => 'chart'], ['title' => 'Photography', 'description' => 'Professional photography that captures your brand essence.', 'icon' => 'camera']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'Selected Work', 'subtitle' => 'Projects we\'re proud of', 'images' => [['url' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&q=80'], ['url' => 'https://images.unsplash.com/photo-1558655146-d09347e92766?w=800&q=80'], ['url' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=800&q=80'], ['url' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=800&q=80']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'stats', 'content' => ['layout' => 'row', 'title' => 'By the Numbers', 'items' => [['value' => '200+', 'label' => 'Projects Delivered'], ['value' => '50+', 'label' => 'Happy Clients'], ['value' => '15', 'label' => 'Awards Won'], ['value' => '10+', 'label' => 'Years Experience']]], 'style' => ['backgroundColor' => '#0f172a']],
                ['type' => 'testimonials', 'content' => ['layout' => 'single', 'title' => 'Client Love', 'items' => [['name' => 'Jane Doe', 'role' => 'CEO, TechCorp', 'text' => 'STUDIO transformed our brand completely. Their creativity and professionalism are unmatched.', 'rating' => 5]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Let\'s Create Something Amazing', 'description' => 'Ready to bring your vision to life? Let\'s talk.', 'buttonText' => 'Start Your Project', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#ec4899']],
            ]],
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
        
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Transform Your Body, Transform Your Life', 'subtitle' => 'Join Lusaka\'s premier fitness studio. Expert trainers, state-of-the-art equipment, results guaranteed.', 'buttonText' => 'Start Free Trial', 'buttonLink' => '/contact', 'secondaryButtonText' => 'View Classes', 'secondaryButtonLink' => '/classes', 'backgroundImage' => 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=1920&q=80', 'overlayColor' => 'black', 'overlayOpacity' => 50], 'style' => ['minHeight' => 750]],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Why Choose Apex Fitness', 'items' => [['title' => 'Expert Trainers', 'description' => 'Certified professionals dedicated to your success.', 'icon' => 'users'], ['title' => 'Premium Equipment', 'description' => 'Latest fitness technology and equipment.', 'icon' => 'dumbbell'], ['title' => 'Flexible Schedule', 'description' => 'Classes from 5 AM to 10 PM, 7 days a week.', 'icon' => 'calendar'], ['title' => 'Nutrition Coaching', 'description' => 'Personalized meal plans and nutrition guidance.', 'icon' => 'heart'], ['title' => 'Results Driven', 'description' => 'Track your progress with our app and analytics.', 'icon' => 'chart'], ['title' => 'Safe Environment', 'description' => 'Clean, sanitized, and COVID-safe facility.', 'icon' => 'shield']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Our Classes', 'subtitle' => 'Find your perfect workout', 'items' => [['title' => 'HIIT Training', 'description' => 'High-intensity interval training for maximum calorie burn.'], ['title' => 'Strength Training', 'description' => 'Build muscle and increase strength with guided workouts.'], ['title' => 'Yoga & Pilates', 'description' => 'Improve flexibility, balance, and mindfulness.'], ['title' => 'Boxing', 'description' => 'Full-body workout combining cardio and strength.'], ['title' => 'Spin Classes', 'description' => 'High-energy cycling workouts with motivating music.'], ['title' => 'Personal Training', 'description' => 'One-on-one sessions tailored to your goals.']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'testimonials', 'content' => ['layout' => 'carousel', 'title' => 'Success Stories', 'items' => [['name' => 'John Banda', 'role' => 'Member since 2023', 'text' => 'Lost 20kg in 6 months! The trainers are amazing and the community is so supportive.', 'rating' => 5], ['name' => 'Grace Mwansa', 'role' => 'Member since 2022', 'text' => 'Best gym in Lusaka. Clean, modern equipment and the classes are incredible.', 'rating' => 5]]], 'style' => ['backgroundColor' => '#0f172a']],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Membership Plans', 'subtitle' => 'Choose the plan that fits your lifestyle', 'plans' => [['name' => 'Basic', 'price' => 'K300/mo', 'features' => ['Gym access', 'Locker room', 'Free WiFi', 'Mobile app']], ['name' => 'Premium', 'price' => 'K500/mo', 'popular' => true, 'features' => ['Everything in Basic', 'All group classes', 'Nutrition coaching', 'Progress tracking', 'Guest passes (2/mo)']], ['name' => 'Elite', 'price' => 'K800/mo', 'features' => ['Everything in Premium', 'Personal training (4/mo)', 'Priority booking', 'Massage therapy', 'Unlimited guest passes']]]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Ready to Start Your Fitness Journey?', 'description' => 'Join today and get your first week free!', 'buttonText' => 'Claim Free Trial', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#ef4444']],
            ]],
        ]);
    }


    private function createEcommerceStoreTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'ecommerce-store-pro'],
            [
                'name' => 'E-commerce Store Pro',
                'description' => 'Modern online store with product showcases, hover effects, and seamless shopping experience. Perfect for retail businesses.',
                'industry' => 'ecommerce',
                'thumbnail' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&q=80',
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 5,
                'theme' => [
                    'primaryColor' => '#0891b2',
                    'secondaryColor' => '#0f172a',
                    'accentColor' => '#f59e0b',
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
        
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'split-right', 'title' => 'New Collection Just Dropped', 'subtitle' => 'Discover the latest trends in fashion. Free shipping on orders over K500.', 'buttonText' => 'Shop Collection', 'buttonLink' => '/products', 'secondaryButtonText' => 'View Lookbook', 'secondaryButtonLink' => '/lookbook', 'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=800&q=80'], 'style' => ['backgroundColor' => '#f8fafc', 'minHeight' => 650]],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Shop by Category', 'items' => [['title' => 'Women\'s Fashion', 'description' => 'Dresses, tops, and accessories', 'icon' => 'sparkles'], ['title' => 'Men\'s Fashion', 'description' => 'Shirts, pants, and shoes', 'icon' => 'sparkles'], ['title' => 'Accessories', 'description' => 'Bags, jewelry, and watches', 'icon' => 'sparkles'], ['title' => 'Footwear', 'description' => 'Sneakers, heels, and sandals', 'icon' => 'sparkles']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Trending Now', 'subtitle' => 'Our most popular items this week', 'images' => [['url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&q=80', 'alt' => 'Classic White Tee'], ['url' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=400&q=80', 'alt' => 'Denim Jacket'], ['url' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&q=80', 'alt' => 'Leather Bag'], ['url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&q=80', 'alt' => 'Sneakers'], ['url' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=400&q=80', 'alt' => 'Summer Dress'], ['url' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=400&q=80', 'alt' => 'Sunglasses']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why Shop With Us', 'items' => [['title' => 'Free Shipping', 'description' => 'On orders over K500', 'icon' => 'truck'], ['title' => 'Secure Payment', 'description' => 'Safe & encrypted checkout', 'icon' => 'shield'], ['title' => 'Easy Returns', 'description' => '30-day return policy', 'icon' => 'refresh'], ['title' => '24/7 Support', 'description' => 'Always here to help', 'icon' => 'support']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'testimonials', 'content' => ['layout' => 'grid', 'title' => 'Customer Reviews', 'items' => [['name' => 'Alice Mwale', 'text' => 'Great quality products and fast delivery. Highly recommend!', 'rating' => 5], ['name' => 'Peter Banda', 'text' => 'Love the variety and prices. My go-to online store.', 'rating' => 5], ['name' => 'Sarah Phiri', 'text' => 'Excellent customer service and beautiful items.', 'rating' => 5]]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Get 10% Off Your First Order', 'description' => 'Subscribe to our newsletter for exclusive deals and new arrivals.', 'buttonText' => 'Subscribe Now', 'buttonLink' => '#newsletter'], 'style' => ['backgroundColor' => '#0891b2']],
            ]],
        ]);
    }
}
