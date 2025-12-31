<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use Illuminate\Database\Seeder;

class IndustryTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $this->createEcommerceTemplate();
        $this->createAgencyTemplate();
        $this->createHotelTemplate();
        $this->createRealEstateTemplate();
        $this->createMedicalTemplate();
    }

    private function createEcommerceTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'ecommerce-store'], [
            'name' => 'E-commerce Store',
            'description' => 'Complete online store with products, pricing, and shopping features',
            'industry' => 'ecommerce',
            'thumbnail' => 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=800&q=80',
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 10,
            'theme' => ['primaryColor' => '#7c3aed', 'secondaryColor' => '#a78bfa', 'accentColor' => '#ec4899', 'backgroundColor' => '#ffffff', 'textColor' => '#1f2937'],
            'settings' => ['navigation' => ['logoText' => 'ShopZM', 'sticky' => true, 'showCta' => true, 'ctaText' => 'Shop Now', 'ctaLink' => '/products'], 'footer' => ['copyrightText' => '© 2024 ShopZM. All rights reserved.', 'showSocial' => true]],
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
                // Hero with strong value proposition
                ['type' => 'hero', 'content' => [
                    'layout' => 'split-right',
                    'title' => 'Shop Quality Products at Unbeatable Prices',
                    'subtitle' => 'Zambia\'s trusted online store. Fast delivery across all provinces. Secure payment via Mobile Money. 30-day money-back guarantee on all purchases.',
                    'buttonText' => 'Shop Now',
                    'buttonLink' => '/products',
                    'secondaryButtonText' => 'View Deals',
                    'secondaryButtonLink' => '#deals',
                    'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200&q=80'
                ], 'style' => ['backgroundColor' => '#7c3aed', 'textColor' => '#ffffff', 'minHeight' => 650]],
                
                // Trust badges / Logo cloud
                ['type' => 'logo-cloud', 'content' => [
                    'title' => 'Trusted Brands We Carry',
                    'subtitle' => 'Authentic products from world-renowned brands',
                    'layout' => 'marquee',
                    'grayscale' => false,
                    'items' => [
                        ['name' => 'Samsung', 'image' => ''],
                        ['name' => 'Apple', 'image' => ''],
                        ['name' => 'Sony', 'image' => ''],
                        ['name' => 'LG', 'image' => ''],
                        ['name' => 'Nike', 'image' => ''],
                        ['name' => 'Adidas', 'image' => '']
                    ]
                ], 'style' => ['backgroundColor' => '#f9fafb', 'paddingY' => 60]],
                
                // Featured products
                ['type' => 'products', 'content' => [
                    'title' => 'Featured Products',
                    'subtitle' => 'Handpicked items our customers love',
                    'layout' => 'grid',
                    'columns' => 4,
                    'showPrice' => true,
                    'showRating' => true,
                    'items' => [
                        ['name' => 'Wireless Bluetooth Headphones', 'price' => 'K450', 'originalPrice' => 'K599', 'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80', 'description' => 'Premium sound quality with 30hr battery', 'rating' => 4.8, 'badge' => 'Best Seller'],
                        ['name' => 'Smart Fitness Watch', 'price' => 'K850', 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80', 'description' => 'Track fitness, heart rate & sleep', 'rating' => 4.9, 'badge' => 'New'],
                        ['name' => 'Premium Laptop Bag', 'price' => 'K250', 'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&q=80', 'description' => 'Fits 15.6" laptops, water-resistant', 'rating' => 4.7],
                        ['name' => 'Portable Power Bank', 'price' => 'K180', 'image' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=400&q=80', 'description' => '20000mAh fast charging', 'rating' => 4.6]
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                
                // Why shop with us - Features
                ['type' => 'features', 'content' => [
                    'title' => 'Why Shop With Us',
                    'subtitle' => 'The ShopZM difference that keeps customers coming back',
                    'layout' => 'grid',
                    'columns' => 4,
                    'items' => [
                        ['title' => 'Free Delivery', 'description' => 'Free shipping on orders over K500 anywhere in Zambia', 'icon' => 'truck'],
                        ['title' => 'Secure Payment', 'description' => 'Pay safely with MTN MoMo, Airtel Money, or card', 'icon' => 'shield'],
                        ['title' => '30-Day Returns', 'description' => 'Not satisfied? Return within 30 days for full refund', 'icon' => 'refresh'],
                        ['title' => '24/7 Support', 'description' => 'WhatsApp support available around the clock', 'icon' => 'chat']
                    ]
                ], 'style' => ['backgroundColor' => '#faf5ff', 'paddingY' => 80]],
                
                // Stats / Social proof
                ['type' => 'stats', 'content' => [
                    'layout' => 'horizontal',
                    'title' => 'Trusted by Thousands',
                    'animated' => true,
                    'items' => [
                        ['number' => '10', 'suffix' => 'K+', 'label' => 'Products Available', 'icon' => 'shopping'],
                        ['number' => '50', 'suffix' => 'K+', 'label' => 'Happy Customers', 'icon' => 'users'],
                        ['number' => '24', 'suffix' => 'hr', 'label' => 'Lusaka Delivery', 'icon' => 'clock'],
                        ['number' => '4.8', 'suffix' => '/5', 'label' => 'Customer Rating', 'icon' => 'star']
                    ]
                ], 'style' => ['backgroundColor' => '#7c3aed', 'textColor' => '#ffffff', 'paddingY' => 60]],
                
                // Categories
                ['type' => 'services', 'content' => [
                    'title' => 'Shop by Category',
                    'subtitle' => 'Find exactly what you\'re looking for',
                    'layout' => 'cards',
                    'columns' => 3,
                    'items' => [
                        ['title' => 'Electronics', 'description' => 'Phones, laptops, TVs, and gadgets', 'icon' => 'device', 'link' => '/products?category=electronics'],
                        ['title' => 'Fashion', 'description' => 'Clothing, shoes, and accessories', 'icon' => 'shirt', 'link' => '/products?category=fashion'],
                        ['title' => 'Home & Living', 'description' => 'Furniture, decor, and appliances', 'icon' => 'home', 'link' => '/products?category=home'],
                        ['title' => 'Beauty & Health', 'description' => 'Skincare, makeup, and wellness', 'icon' => 'heart', 'link' => '/products?category=beauty'],
                        ['title' => 'Sports & Outdoors', 'description' => 'Fitness gear and outdoor equipment', 'icon' => 'activity', 'link' => '/products?category=sports'],
                        ['title' => 'Kids & Baby', 'description' => 'Toys, clothing, and essentials', 'icon' => 'baby', 'link' => '/products?category=kids']
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                
                // Testimonials
                ['type' => 'testimonials', 'content' => [
                    'layout' => 'grid',
                    'title' => 'What Our Customers Say',
                    'subtitle' => 'Real reviews from real customers',
                    'columns' => 3,
                    'items' => [
                        ['name' => 'Mary Lungu', 'role' => 'Lusaka', 'text' => 'Fast delivery, great prices, and excellent customer service. I order everything from ShopZM now! The quality is always top-notch.', 'rating' => 5, 'image' => ''],
                        ['name' => 'Peter Banda', 'role' => 'Ndola', 'text' => 'Was skeptical about online shopping but ShopZM changed my mind. Product arrived exactly as described and even faster than expected.', 'rating' => 5, 'image' => ''],
                        ['name' => 'Grace Mwale', 'role' => 'Kitwe', 'text' => 'The Mobile Money payment option is so convenient! No need for cards. And their WhatsApp support is incredibly helpful.', 'rating' => 5, 'image' => '']
                    ]
                ], 'style' => ['backgroundColor' => '#f9fafb', 'paddingY' => 80]],
                
                // Newsletter CTA
                ['type' => 'cta-banner', 'content' => [
                    'layout' => 'centered',
                    'title' => 'Get 10% Off Your First Order',
                    'subtitle' => 'Subscribe to our newsletter for exclusive deals, new arrivals, and special offers delivered to your inbox.',
                    'buttonText' => 'Subscribe Now',
                    'buttonLink' => '#subscribe',
                    'showInput' => true,
                    'inputPlaceholder' => 'Enter your email'
                ], 'style' => ['backgroundColor' => '#7c3aed', 'textColor' => '#ffffff', 'gradient' => true, 'paddingY' => 80]],
            ]],
        ]);
    }

    private function createAgencyTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'creative-agency'], [
            'name' => 'Creative Agency',
            'description' => 'Portfolio and team showcase for creative professionals',
            'industry' => 'creative',
            'thumbnail' => 'https://images.unsplash.com/photo-1542744094-3a31f272c490?w=800&q=80',
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 11,
            'theme' => ['primaryColor' => '#18181b', 'secondaryColor' => '#52525b', 'accentColor' => '#f59e0b', 'backgroundColor' => '#ffffff', 'textColor' => '#09090b'],
            'settings' => ['navigation' => ['logoText' => 'STUDIO', 'sticky' => true, 'transparent' => true], 'footer' => ['copyrightText' => '© 2024 Creative Studio. All rights reserved.']],
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
                // Bold hero
                ['type' => 'hero', 'content' => [
                    'layout' => 'centered',
                    'title' => 'We Create Experiences That Matter',
                    'subtitle' => 'Award-winning design studio specializing in branding, digital experiences, and creative campaigns for forward-thinking companies across Africa.',
                    'buttonText' => 'View Our Work',
                    'buttonLink' => '/portfolio',
                    'secondaryButtonText' => 'Get in Touch',
                    'secondaryButtonLink' => '/contact',
                    'image' => 'https://images.unsplash.com/photo-1558655146-d09347e92766?w=1920&q=80'
                ], 'style' => ['backgroundColor' => '#18181b', 'textColor' => '#ffffff', 'minHeight' => 750, 'overlay' => true]],
                
                // Services
                ['type' => 'services', 'content' => [
                    'title' => 'What We Do',
                    'subtitle' => 'Full-service creative solutions',
                    'layout' => 'alternating',
                    'items' => [
                        ['title' => 'Brand Identity', 'description' => 'We craft distinctive brand identities that capture your essence and resonate with your audience. From logo design to complete brand systems.', 'icon' => 'palette', 'image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&q=80'],
                        ['title' => 'Web Design & Development', 'description' => 'Beautiful, functional websites that convert visitors into customers. We blend stunning design with seamless user experience.', 'icon' => 'code', 'image' => 'https://images.unsplash.com/photo-1547658719-da2b51169166?w=600&q=80'],
                        ['title' => 'Digital Marketing', 'description' => 'Strategic campaigns that amplify your brand and drive measurable results. Social media, content, and performance marketing.', 'icon' => 'megaphone', 'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=80']
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                
                // Stats
                ['type' => 'stats', 'content' => [
                    'layout' => 'grid',
                    'animated' => true,
                    'items' => [
                        ['number' => '200', 'suffix' => '+', 'label' => 'Projects Completed', 'icon' => 'briefcase'],
                        ['number' => '50', 'suffix' => '+', 'label' => 'Happy Clients', 'icon' => 'users'],
                        ['number' => '15', 'suffix' => '', 'label' => 'Awards Won', 'icon' => 'trophy'],
                        ['number' => '8', 'suffix' => '', 'label' => 'Years Experience', 'icon' => 'calendar']
                    ]
                ], 'style' => ['backgroundColor' => '#f9fafb', 'accentColor' => '#f59e0b', 'paddingY' => 80]],
                
                // Portfolio gallery
                ['type' => 'gallery', 'content' => [
                    'title' => 'Selected Work',
                    'subtitle' => 'A showcase of projects we\'re proud of',
                    'layout' => 'masonry',
                    'columns' => 3,
                    'images' => [
                        ['src' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&q=80', 'alt' => 'Brand Identity Project', 'caption' => 'Zambia Airways Rebrand'],
                        ['src' => 'https://images.unsplash.com/photo-1547658719-da2b51169166?w=600&q=80', 'alt' => 'Web Design', 'caption' => 'E-commerce Platform'],
                        ['src' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=80', 'alt' => 'Marketing Campaign', 'caption' => 'Digital Campaign'],
                        ['src' => 'https://images.unsplash.com/photo-1558655146-d09347e92766?w=600&q=80', 'alt' => 'Creative Direction', 'caption' => 'Product Launch'],
                        ['src' => 'https://images.unsplash.com/photo-1542744094-3a31f272c490?w=600&q=80', 'alt' => 'Brand Strategy', 'caption' => 'Startup Branding'],
                        ['src' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=600&q=80', 'alt' => 'UI/UX Design', 'caption' => 'Mobile App Design']
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                
                // Process
                ['type' => 'timeline', 'content' => [
                    'layout' => 'horizontal',
                    'title' => 'Our Process',
                    'subtitle' => 'How we bring your vision to life',
                    'items' => [
                        ['year' => '01', 'title' => 'Discovery', 'description' => 'We dive deep into your brand, audience, and goals', 'icon' => 'search'],
                        ['year' => '02', 'title' => 'Strategy', 'description' => 'Develop a roadmap for creative success', 'icon' => 'map'],
                        ['year' => '03', 'title' => 'Design', 'description' => 'Craft beautiful, purposeful creative work', 'icon' => 'palette'],
                        ['year' => '04', 'title' => 'Deliver', 'description' => 'Launch and measure results', 'icon' => 'rocket']
                    ]
                ], 'style' => ['backgroundColor' => '#18181b', 'textColor' => '#ffffff', 'lineColor' => '#f59e0b', 'paddingY' => 100]],
                
                // Team
                ['type' => 'team', 'content' => [
                    'title' => 'Meet the Team',
                    'subtitle' => 'The creative minds behind our work',
                    'layout' => 'grid',
                    'columns' => 4,
                    'showSocial' => true,
                    'items' => [
                        ['name' => 'Mwila Chanda', 'role' => 'Founder & Creative Director', 'bio' => 'Award-winning designer with 15+ years experience', 'image' => '', 'social' => ['linkedin' => '#', 'twitter' => '#']],
                        ['name' => 'Bwalya Mutale', 'role' => 'Art Director', 'bio' => 'Visual storyteller and brand strategist', 'image' => '', 'social' => ['linkedin' => '#', 'dribbble' => '#']],
                        ['name' => 'Chilufya Banda', 'role' => 'Lead Developer', 'bio' => 'Full-stack developer and tech enthusiast', 'image' => '', 'social' => ['linkedin' => '#', 'github' => '#']],
                        ['name' => 'Natasha Mwanza', 'role' => 'Marketing Lead', 'bio' => 'Digital marketing expert and growth hacker', 'image' => '', 'social' => ['linkedin' => '#', 'twitter' => '#']]
                    ]
                ], 'style' => ['backgroundColor' => '#f9fafb', 'paddingY' => 100]],
                
                // Testimonials
                ['type' => 'testimonials', 'content' => [
                    'layout' => 'carousel',
                    'title' => 'Client Love',
                    'items' => [
                        ['name' => 'Joseph Lungu', 'role' => 'CEO, Tech Innovations', 'text' => 'They completely transformed our brand. The attention to detail and creative vision exceeded all expectations. Our new identity has helped us stand out in a crowded market.', 'rating' => 5, 'image' => ''],
                        ['name' => 'Sarah Mwale', 'role' => 'Marketing Director, Zambia Airways', 'text' => 'Working with Creative Studio was a game-changer. They understood our vision and delivered a brand identity that truly represents who we are.', 'rating' => 5, 'image' => '']
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                
                // CTA
                ['type' => 'cta-banner', 'content' => [
                    'layout' => 'split',
                    'title' => 'Let\'s Create Something Amazing Together',
                    'subtitle' => 'Have a project in mind? We\'d love to hear about it. Let\'s discuss how we can help bring your vision to life.',
                    'buttonText' => 'Start a Project',
                    'buttonLink' => '/contact',
                    'secondaryButtonText' => 'View Portfolio',
                    'secondaryButtonLink' => '/portfolio'
                ], 'style' => ['backgroundColor' => '#18181b', 'textColor' => '#ffffff', 'paddingY' => 100]],
            ]],
        ]);
    }

    private function createHotelTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'hotel-resort'], [
            'name' => 'Hotel & Resort',
            'description' => 'Luxury hospitality template with booking features',
            'industry' => 'hospitality',
            'thumbnail' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&q=80',
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 12,
            'theme' => ['primaryColor' => '#92400e', 'secondaryColor' => '#78350f', 'accentColor' => '#d97706', 'backgroundColor' => '#fffbeb', 'textColor' => '#1c1917'],
            'settings' => ['navigation' => ['logoText' => 'Zambezi Lodge', 'sticky' => true, 'showCta' => true, 'ctaText' => 'Book Now', 'ctaLink' => '/contact'], 'footer' => ['copyrightText' => '© 2024 Zambezi Lodge']],
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
                    'layout' => 'fullscreen',
                    'title' => 'Experience Luxury Like Never Before',
                    'subtitle' => 'Escape to paradise at Zambia\'s premier riverside resort. Where the Zambezi meets world-class hospitality.',
                    'posterImage' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1920&q=80',
                    'buttonText' => 'Book Your Stay',
                    'buttonLink' => '/contact',
                    'secondaryButtonText' => 'Virtual Tour',
                    'secondaryButtonLink' => '#tour'
                ], 'style' => ['overlay' => true, 'minHeight' => 750]],
                
                ['type' => 'about', 'content' => [
                    'layout' => 'image-right',
                    'title' => 'A Sanctuary of Serenity',
                    'description' => 'Nestled along the banks of the mighty Zambezi River, our lodge offers an unparalleled blend of luxury and nature. Wake up to stunning sunrises, enjoy world-class cuisine, and create memories that last a lifetime. Whether you\'re seeking adventure or relaxation, Zambezi Lodge is your perfect escape.',
                    'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&q=80',
                    'buttonText' => 'Our Story',
                    'buttonLink' => '/about'
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                
                ['type' => 'features', 'content' => [
                    'title' => 'World-Class Amenities',
                    'subtitle' => 'Everything you need for the perfect getaway',
                    'layout' => 'grid',
                    'columns' => 4,
                    'items' => [
                        ['title' => 'Infinity Pool', 'description' => 'Overlooking the Zambezi with stunning sunset views', 'icon' => 'water'],
                        ['title' => 'Fine Dining', 'description' => 'Award-winning restaurant with local and international cuisine', 'icon' => 'restaurant'],
                        ['title' => 'Spa & Wellness', 'description' => 'Full-service spa with traditional African treatments', 'icon' => 'spa'],
                        ['title' => 'Safari Tours', 'description' => 'Guided game drives and river cruises', 'icon' => 'compass'],
                        ['title' => 'Conference Center', 'description' => 'Modern facilities for business events', 'icon' => 'briefcase'],
                        ['title' => 'Private Beach', 'description' => 'Exclusive riverside beach access', 'icon' => 'sun'],
                        ['title' => 'Fitness Center', 'description' => 'State-of-the-art gym equipment', 'icon' => 'dumbbell'],
                        ['title' => 'Kids Club', 'description' => 'Supervised activities for young guests', 'icon' => 'baby']
                    ]
                ], 'style' => ['backgroundColor' => '#fffbeb', 'paddingY' => 100]],
                
                ['type' => 'gallery', 'content' => [
                    'title' => 'Explore Our Resort',
                    'subtitle' => 'Take a visual journey through Zambezi Lodge',
                    'layout' => 'grid',
                    'columns' => 3,
                    'images' => [
                        ['src' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=600&q=80', 'alt' => 'Resort Pool'],
                        ['src' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=600&q=80', 'alt' => 'Luxury Suite'],
                        ['src' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&q=80', 'alt' => 'Restaurant'],
                        ['src' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=600&q=80', 'alt' => 'Spa'],
                        ['src' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=600&q=80', 'alt' => 'Beach'],
                        ['src' => 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=600&q=80', 'alt' => 'Sunset View']
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                
                ['type' => 'pricing', 'content' => [
                    'title' => 'Accommodation',
                    'subtitle' => 'Choose your perfect room',
                    'layout' => 'cards',
                    'plans' => [
                        ['name' => 'Deluxe Room', 'price' => 'K1,200', 'period' => '/night', 'description' => 'Comfortable elegance with garden views', 'features' => ['King-size bed', 'Garden view', 'Free WiFi', 'Breakfast included', 'Mini bar', 'Air conditioning'], 'buttonText' => 'Book Now', 'buttonLink' => '/contact'],
                        ['name' => 'River Suite', 'price' => 'K2,500', 'period' => '/night', 'description' => 'Spacious luxury with river views', 'features' => ['Separate living area', 'Private balcony', 'River view', 'Butler service', 'Premium mini bar', 'Jacuzzi tub'], 'popular' => true, 'buttonText' => 'Book Now', 'buttonLink' => '/contact'],
                        ['name' => 'Presidential Villa', 'price' => 'K5,000', 'period' => '/night', 'description' => 'Ultimate luxury and privacy', 'features' => ['Private pool', 'Personal chef', 'Dedicated butler', 'Private garden', 'Spa treatments', 'Airport transfer'], 'buttonText' => 'Book Now', 'buttonLink' => '/contact']
                    ]
                ], 'style' => ['backgroundColor' => '#fffbeb', 'paddingY' => 100]],
                
                ['type' => 'testimonials', 'content' => [
                    'layout' => 'carousel',
                    'title' => 'Guest Experiences',
                    'items' => [
                        ['name' => 'Sarah & James Thompson', 'role' => 'Honeymoon Guests', 'text' => 'Our honeymoon at Zambezi Lodge was absolutely magical. The staff went above and beyond to make our stay special. The sunset river cruise was unforgettable!', 'rating' => 5],
                        ['name' => 'Michael Chen', 'role' => 'Business Traveler', 'text' => 'Perfect blend of business and leisure. The conference facilities are excellent, and the spa helped me unwind after long meetings.', 'rating' => 5]
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                
                ['type' => 'cta-banner', 'content' => [
                    'layout' => 'with-image',
                    'title' => 'Book Your Dream Getaway',
                    'subtitle' => 'Limited availability for peak season. Reserve your dates now and receive a complimentary spa treatment.',
                    'buttonText' => 'Check Availability',
                    'buttonLink' => '/contact',
                    'image' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800&q=80'
                ], 'style' => ['backgroundColor' => '#92400e', 'textColor' => '#ffffff', 'paddingY' => 100]],
            ]],
        ]);
    }

    private function createRealEstateTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'real-estate'], [
            'name' => 'Real Estate Agency',
            'description' => 'Property listings and agent profiles',
            'industry' => 'realestate',
            'thumbnail' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&q=80',
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 13,
            'theme' => ['primaryColor' => '#1e40af', 'secondaryColor' => '#475569', 'accentColor' => '#059669', 'backgroundColor' => '#f8fafc', 'textColor' => '#0f172a'],
            'settings' => ['navigation' => ['logoText' => 'Prime Properties', 'sticky' => true, 'showCta' => true, 'ctaText' => 'List Property', 'ctaLink' => '/contact'], 'footer' => ['copyrightText' => '© 2024 Prime Properties Zambia']],
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
                ['type' => 'hero', 'content' => [
                    'layout' => 'split-right',
                    'title' => 'Find Your Dream Home in Zambia',
                    'subtitle' => 'Zambia\'s most trusted real estate agency. Whether you\'re buying, selling, or renting, our expert agents are here to guide you every step of the way.',
                    'buttonText' => 'Browse Properties',
                    'buttonLink' => '/properties',
                    'secondaryButtonText' => 'Sell Your Property',
                    'secondaryButtonLink' => '/contact',
                    'image' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&q=80'
                ], 'style' => ['backgroundColor' => '#1e293b', 'textColor' => '#ffffff', 'minHeight' => 650]],
                
                ['type' => 'stats', 'content' => [
                    'layout' => 'horizontal',
                    'animated' => true,
                    'items' => [
                        ['number' => '500', 'suffix' => '+', 'label' => 'Properties Sold', 'icon' => 'home'],
                        ['number' => '1000', 'suffix' => '+', 'label' => 'Happy Families', 'icon' => 'users'],
                        ['number' => '15', 'suffix' => '+', 'label' => 'Years Experience', 'icon' => 'calendar'],
                        ['number' => 'K2', 'suffix' => 'B+', 'label' => 'Property Value Sold', 'icon' => 'chart']
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'accentColor' => '#1e40af', 'paddingY' => 60]],
                
                ['type' => 'services', 'content' => [
                    'title' => 'Our Services',
                    'subtitle' => 'Comprehensive real estate solutions',
                    'layout' => 'cards',
                    'columns' => 3,
                    'items' => [
                        ['title' => 'Buy a Home', 'description' => 'Browse our extensive listings of houses, apartments, and land. Find your perfect property with our expert guidance.', 'icon' => 'home', 'link' => '/properties?type=sale'],
                        ['title' => 'Sell Your Property', 'description' => 'Get the best value for your property. Our marketing expertise ensures maximum exposure to qualified buyers.', 'icon' => 'tag', 'link' => '/contact'],
                        ['title' => 'Rent a Property', 'description' => 'Quality rental properties across Zambia. From apartments to family homes, find your next rental.', 'icon' => 'key', 'link' => '/properties?type=rent'],
                        ['title' => 'Property Management', 'description' => 'Let us manage your investment property. Tenant screening, maintenance, and rent collection.', 'icon' => 'settings', 'link' => '/services'],
                        ['title' => 'Property Valuation', 'description' => 'Get an accurate market valuation of your property from our certified valuers.', 'icon' => 'calculator', 'link' => '/contact'],
                        ['title' => 'Investment Advisory', 'description' => 'Expert advice on real estate investment opportunities in Zambia\'s growing market.', 'icon' => 'trending', 'link' => '/contact']
                    ]
                ], 'style' => ['backgroundColor' => '#f8fafc', 'paddingY' => 100]],
                
                ['type' => 'products', 'content' => [
                    'title' => 'Featured Properties',
                    'subtitle' => 'Handpicked listings from our portfolio',
                    'layout' => 'grid',
                    'columns' => 3,
                    'items' => [
                        ['name' => '4-Bedroom Executive House', 'price' => 'K2,500,000', 'description' => 'Kabulonga, Lusaka • 4 Beds • 3 Baths • 450m²', 'image' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&q=80', 'badge' => 'For Sale'],
                        ['name' => 'Modern 2-Bed Apartment', 'price' => 'K8,500/month', 'description' => 'Woodlands, Lusaka • 2 Beds • 2 Baths • 120m²', 'image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&q=80', 'badge' => 'For Rent'],
                        ['name' => 'Commercial Plot', 'price' => 'K1,800,000', 'description' => 'Great East Road • 2,000m² • Commercial Zone', 'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80', 'badge' => 'Investment'],
                        ['name' => '3-Bedroom Townhouse', 'price' => 'K1,200,000', 'description' => 'Roma, Lusaka • 3 Beds • 2 Baths • 200m²', 'image' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=600&q=80', 'badge' => 'New Listing'],
                        ['name' => 'Luxury Penthouse', 'price' => 'K15,000/month', 'description' => 'Longacres, Lusaka • 3 Beds • 3 Baths • 280m²', 'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=600&q=80', 'badge' => 'Premium'],
                        ['name' => 'Farm Land', 'price' => 'K3,500,000', 'description' => 'Chisamba • 50 Hectares • Agricultural', 'image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&q=80', 'badge' => 'Investment']
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                
                ['type' => 'team', 'content' => [
                    'title' => 'Meet Our Expert Agents',
                    'subtitle' => 'Dedicated professionals ready to help you',
                    'layout' => 'grid',
                    'columns' => 4,
                    'items' => [
                        ['name' => 'David Tembo', 'role' => 'Senior Agent', 'bio' => '10+ years experience in residential sales', 'image' => ''],
                        ['name' => 'Chanda Mwale', 'role' => 'Property Consultant', 'bio' => 'Specialist in luxury properties', 'image' => ''],
                        ['name' => 'Grace Banda', 'role' => 'Rental Specialist', 'bio' => 'Expert in property management', 'image' => ''],
                        ['name' => 'Peter Phiri', 'role' => 'Commercial Agent', 'bio' => 'Commercial and investment properties', 'image' => '']
                    ]
                ], 'style' => ['backgroundColor' => '#f8fafc', 'paddingY' => 100]],
                
                ['type' => 'testimonials', 'content' => [
                    'layout' => 'grid',
                    'title' => 'What Our Clients Say',
                    'columns' => 2,
                    'items' => [
                        ['name' => 'James & Mary Mwansa', 'role' => 'First-Time Buyers', 'text' => 'Prime Properties made buying our first home a breeze. David was patient, knowledgeable, and found us the perfect house within our budget. Highly recommended!', 'rating' => 5],
                        ['name' => 'Corporate Holdings Ltd', 'role' => 'Commercial Client', 'text' => 'Professional service from start to finish. They helped us find the ideal office space and handled all the paperwork efficiently. A trusted partner for our real estate needs.', 'rating' => 5]
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                
                ['type' => 'cta-banner', 'content' => [
                    'layout' => 'centered',
                    'title' => 'Ready to Find Your Dream Property?',
                    'subtitle' => 'Whether you\'re buying, selling, or renting, our expert team is here to help. Get a free consultation today.',
                    'buttonText' => 'Contact Us Today',
                    'buttonLink' => '/contact',
                    'secondaryButtonText' => 'Browse All Properties',
                    'secondaryButtonLink' => '/properties'
                ], 'style' => ['backgroundColor' => '#1e40af', 'textColor' => '#ffffff', 'gradient' => true, 'paddingY' => 100]],
            ]],
        ]);
    }

    private function createMedicalTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'medical-clinic'], [
            'name' => 'Medical Clinic',
            'description' => 'Healthcare services with team and booking',
            'industry' => 'healthcare',
            'thumbnail' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800&q=80',
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 14,
            'theme' => ['primaryColor' => '#0891b2', 'secondaryColor' => '#64748b', 'accentColor' => '#059669', 'backgroundColor' => '#ffffff', 'textColor' => '#1f2937'],
            'settings' => ['navigation' => ['logoText' => 'HealthCare Plus', 'sticky' => true, 'showCta' => true, 'ctaText' => 'Book Appointment', 'ctaLink' => '/contact'], 'footer' => ['copyrightText' => '© 2024 HealthCare Plus Clinic']],
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
                ['type' => 'hero', 'content' => [
                    'layout' => 'split-left',
                    'title' => 'Your Health, Our Priority',
                    'subtitle' => 'Comprehensive medical care from experienced professionals. Modern facilities, compassionate care, and a commitment to your wellbeing. Serving Lusaka and surrounding areas.',
                    'buttonText' => 'Book Appointment',
                    'buttonLink' => '/contact',
                    'secondaryButtonText' => 'Our Services',
                    'secondaryButtonLink' => '#services',
                    'image' => 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=1200&q=80'
                ], 'style' => ['backgroundColor' => '#0891b2', 'textColor' => '#ffffff', 'minHeight' => 650]],
                
                ['type' => 'features', 'content' => [
                    'title' => 'Why Choose HealthCare Plus',
                    'subtitle' => 'Quality healthcare you can trust',
                    'layout' => 'grid',
                    'columns' => 4,
                    'items' => [
                        ['title' => 'Experienced Doctors', 'description' => 'Board-certified physicians with years of experience', 'icon' => 'user-md'],
                        ['title' => 'Modern Equipment', 'description' => 'State-of-the-art diagnostic and treatment technology', 'icon' => 'microscope'],
                        ['title' => '24/7 Emergency', 'description' => 'Round-the-clock emergency care when you need it', 'icon' => 'ambulance'],
                        ['title' => 'Affordable Care', 'description' => 'Quality healthcare at competitive prices', 'icon' => 'heart']
                    ]
                ], 'style' => ['backgroundColor' => '#f0fdfa', 'paddingY' => 80]],
                
                ['type' => 'services', 'content' => [
                    'title' => 'Our Medical Services',
                    'subtitle' => 'Comprehensive care for you and your family',
                    'layout' => 'grid',
                    'columns' => 3,
                    'items' => [
                        ['title' => 'General Practice', 'description' => 'Routine checkups, vaccinations, and preventive care for all ages', 'icon' => 'stethoscope', 'link' => '/services'],
                        ['title' => 'Pediatrics', 'description' => 'Specialized care for infants, children, and adolescents', 'icon' => 'baby', 'link' => '/services'],
                        ['title' => 'Women\'s Health', 'description' => 'Obstetrics, gynecology, and reproductive health services', 'icon' => 'female', 'link' => '/services'],
                        ['title' => 'Cardiology', 'description' => 'Heart health assessments, ECG, and cardiac care', 'icon' => 'heart-pulse', 'link' => '/services'],
                        ['title' => 'Laboratory Services', 'description' => 'Comprehensive blood tests and diagnostic services', 'icon' => 'flask', 'link' => '/services'],
                        ['title' => 'Pharmacy', 'description' => 'On-site pharmacy with prescription and OTC medications', 'icon' => 'pills', 'link' => '/services'],
                        ['title' => 'Dental Care', 'description' => 'Complete dental services from checkups to procedures', 'icon' => 'tooth', 'link' => '/services'],
                        ['title' => 'Mental Health', 'description' => 'Counseling and psychiatric services in a supportive environment', 'icon' => 'brain', 'link' => '/services'],
                        ['title' => 'Physiotherapy', 'description' => 'Rehabilitation and physical therapy services', 'icon' => 'activity', 'link' => '/services']
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                
                ['type' => 'stats', 'content' => [
                    'layout' => 'grid',
                    'title' => 'Trusted by Thousands',
                    'animated' => true,
                    'items' => [
                        ['number' => '25', 'suffix' => 'K+', 'label' => 'Patients Treated', 'icon' => 'users'],
                        ['number' => '20', 'suffix' => '+', 'label' => 'Medical Professionals', 'icon' => 'user-md'],
                        ['number' => '15', 'suffix' => '+', 'label' => 'Years of Service', 'icon' => 'calendar'],
                        ['number' => '24/7', 'suffix' => '', 'label' => 'Emergency Care', 'icon' => 'clock']
                    ]
                ], 'style' => ['backgroundColor' => '#0891b2', 'textColor' => '#ffffff', 'paddingY' => 80]],
                
                ['type' => 'team', 'content' => [
                    'title' => 'Meet Our Medical Team',
                    'subtitle' => 'Experienced professionals dedicated to your health',
                    'layout' => 'grid',
                    'columns' => 4,
                    'items' => [
                        ['name' => 'Dr. Grace Banda', 'role' => 'Medical Director', 'bio' => 'MBBS, MD - 20 years experience in internal medicine', 'image' => ''],
                        ['name' => 'Dr. James Mwansa', 'role' => 'Cardiologist', 'bio' => 'MBBS, MRCP - Specialist in cardiovascular health', 'image' => ''],
                        ['name' => 'Dr. Sarah Phiri', 'role' => 'Pediatrician', 'bio' => 'MBBS, DCH - Expert in child healthcare', 'image' => ''],
                        ['name' => 'Dr. Peter Tembo', 'role' => 'General Surgeon', 'bio' => 'MBBS, MS - Experienced in minimally invasive surgery', 'image' => '']
                    ]
                ], 'style' => ['backgroundColor' => '#f0fdfa', 'paddingY' => 100]],
                
                ['type' => 'about', 'content' => [
                    'layout' => 'image-left',
                    'title' => 'Insurance & Payment',
                    'description' => 'We accept all major health insurance providers including ZSIC, Madison, Professional Insurance, and NICO. We also offer flexible payment plans for those without insurance. Your health shouldn\'t wait because of financial concerns.',
                    'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=800&q=80',
                    'buttonText' => 'View Accepted Insurance',
                    'buttonLink' => '/insurance'
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 100]],
                
                ['type' => 'testimonials', 'content' => [
                    'layout' => 'carousel',
                    'title' => 'Patient Testimonials',
                    'items' => [
                        ['name' => 'Mary Lungu', 'role' => 'Patient', 'text' => 'The care I received at HealthCare Plus was exceptional. Dr. Banda took the time to explain everything and made me feel comfortable. The staff is friendly and professional.', 'rating' => 5],
                        ['name' => 'John Mwale', 'role' => 'Patient', 'text' => 'After my surgery, the follow-up care was outstanding. The physiotherapy team helped me recover faster than expected. Highly recommend this clinic!', 'rating' => 5]
                    ]
                ], 'style' => ['backgroundColor' => '#f0fdfa', 'paddingY' => 80]],
                
                ['type' => 'faq', 'content' => [
                    'title' => 'Frequently Asked Questions',
                    'items' => [
                        ['question' => 'Do I need an appointment?', 'answer' => 'While walk-ins are welcome, we recommend booking an appointment to minimize wait times. Emergency cases are always seen immediately.'],
                        ['question' => 'What insurance do you accept?', 'answer' => 'We accept ZSIC, Madison, Professional Insurance, NICO, and most major health insurance providers. Contact us to verify your coverage.'],
                        ['question' => 'What are your operating hours?', 'answer' => 'Our clinic is open Monday-Friday 8am-6pm, Saturday 8am-2pm. Our emergency department operates 24/7.'],
                        ['question' => 'Do you offer home visits?', 'answer' => 'Yes, we offer home visit services for patients who are unable to come to the clinic. Additional fees apply.']
                    ]
                ], 'style' => ['backgroundColor' => '#ffffff', 'paddingY' => 80]],
                
                ['type' => 'cta-banner', 'content' => [
                    'layout' => 'centered',
                    'title' => 'Your Health Can\'t Wait',
                    'subtitle' => 'Book your appointment today and take the first step towards better health. Same-day appointments available.',
                    'buttonText' => 'Book Appointment Now',
                    'buttonLink' => '/contact',
                    'secondaryButtonText' => 'Call: +260 211 123 456',
                    'secondaryButtonLink' => 'tel:+260211123456'
                ], 'style' => ['backgroundColor' => '#0891b2', 'textColor' => '#ffffff', 'gradient' => true, 'paddingY' => 100]],
            ]],
        ]);
    }
}
