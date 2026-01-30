<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use Illuminate\Database\Seeder;

class DHCreativeStudioSeeder extends Seeder
{
    public function run(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'dh-creative-studio'],
            [
                'name' => '2H Creative Studio',
                'description' => 'Professional creative studio template featuring graphic design, printing, photography, video services, and Tiyamike Celebration band music project. Complete with hero slideshow and modern animations.',
                'industry' => 'creative',
                'thumbnail' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 6,
                'theme' => [
                    'primaryColor' => '#1e3a8a',
                    'secondaryColor' => '#1e40af',
                    'accentColor' => '#f97316',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => '2H Creative',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Get Started',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 2H Creative Studio. At Your Service For The Best.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page with Hero Slideshow
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                // Hero Slideshow Section - Fixed to use correct type
                ['type' => 'hero', 'content' => ['layout' => 'slideshow', 'autoPlay' => true, 'slideInterval' => 5000, 'slides' => [
                    ['title' => '2H Creative Studio', 'subtitle' => 'At Your Service For The Best', 'buttonText' => 'Explore Services', 'buttonLink' => '#services', 'backgroundImage' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=1920&q=80'],
                    ['title' => 'Graphic Design Excellence', 'subtitle' => 'Bringing Your Vision to Life', 'buttonText' => 'View Portfolio', 'buttonLink' => '#portfolio', 'backgroundImage' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=1920&q=80'],
                    ['title' => 'Professional Photography', 'subtitle' => 'Capture Every Moment', 'buttonText' => 'Book Session', 'buttonLink' => '/contact', 'backgroundImage' => 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=1920&q=80'],
                    ['title' => 'Quality Printing Services', 'subtitle' => 'From Concept to Print', 'buttonText' => 'Get Quote', 'buttonLink' => '/contact', 'backgroundImage' => 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=1920&q=80'],
                ]], 'style' => ['minHeight' => 700]],
                
                // About Section
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Your Creative Partner', 'description' => 'We are a full-service creative studio dedicated to bringing your vision to life. From stunning graphic designs to professional photography and video production, we deliver excellence in every project. Our experienced team combines creativity with technical expertise to ensure your brand stands out.', 'image' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=800&q=80', 'features' => ['Professional Team', 'Modern Equipment', 'Fast Turnaround', 'Affordable Pricing']], 'style' => ['backgroundColor' => '#f8fafc']],
                
                // Tiyamike Celebration - Band Music Project Section
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Tiyamike Celebration', 'subtitle' => 'Let\'s Share The Dream', 'buttonText' => 'Book Our Band', 'buttonLink' => '/contact', 'backgroundImage' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1920&q=80', 'overlayColor' => 'black', 'overlayOpacity' => 60], 'style' => ['minHeight' => 500, 'backgroundColor' => '#000000']],
                
                // Band Services Section
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Tiyamike Celebration Band', 'subtitle' => 'Professional live music for every occasion', 'columns' => 3, 'items' => [
                    ['title' => 'Live Performances', 'description' => 'Energetic live band performances for weddings, parties, and corporate events', 'icon' => 'sparkles'],
                    ['title' => 'Gospel Music', 'description' => 'Uplifting gospel performances for church events and celebrations', 'icon' => 'users'],
                    ['title' => 'Event Entertainment', 'description' => 'Complete entertainment packages with professional sound and lighting', 'icon' => 'star'],
                    ['title' => 'Music Production', 'description' => 'Studio recording, mixing, and mastering services', 'icon' => 'film'],
                    ['title' => 'Custom Arrangements', 'description' => 'Personalized song arrangements for your special moments', 'icon' => 'light-bulb'],
                    ['title' => 'Band Coaching', 'description' => 'Training and mentorship for aspiring musicians and bands', 'icon' => 'chart-bar'],
                ]], 'style' => ['backgroundColor' => '#111827', 'textColor' => '#ffffff']],
                
                // Services Section - Graphic Design
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Graphic Design Services', 'subtitle' => 'Creative designs that make an impact', 'items' => [
                    ['title' => 'Logo & Brand Identity', 'description' => 'Professional logo design and complete brand identity packages', 'icon' => 'sparkles', 'image' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=600&q=80'],
                    ['title' => 'Marketing Materials', 'description' => 'Flyers, posters, banners, business cards, letterheads, brochures', 'icon' => 'document', 'image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&q=80'],
                    ['title' => 'Social Media Graphics', 'description' => 'Eye-catching graphics and digital ads for all platforms', 'icon' => 'photo', 'image' => 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?w=600&q=80'],
                    ['title' => 'Event Materials', 'description' => 'Invitation cards, church programs, certificates, ID cards', 'icon' => 'calendar', 'image' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=600&q=80'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Services Section - Printing
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Printing Services', 'subtitle' => 'High-quality printing for all your needs', 'items' => [
                    ['title' => 'Full-Color Printing', 'description' => 'Vibrant, professional printing for all materials', 'icon' => 'printer'],
                    ['title' => 'Large Format', 'description' => 'Banners, roll-ups, and large-scale prints', 'icon' => 'photo'],
                    ['title' => 'Merchandise', 'description' => 'T-shirts, stickers, labels, calendars, notebooks', 'icon' => 'shopping-bag'],
                    ['title' => 'Document Services', 'description' => 'Photocopying and document printing', 'icon' => 'document-duplicate'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
                
                // Services Section - Studio & Photography
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Studio & Photography Services', 'subtitle' => 'Professional photography for every occasion', 'items' => [
                    ['title' => 'Professional Photography', 'description' => 'Studio sessions, portraits, and commercial photography', 'icon' => 'camera'],
                    ['title' => 'Passport & ID Photos', 'description' => 'Quick, professional passport and ID photographs', 'icon' => 'identification'],
                    ['title' => 'Event Coverage', 'description' => 'Weddings, church events, conferences, and celebrations', 'icon' => 'users'],
                    ['title' => 'Photo Services', 'description' => 'Photo editing, retouching, framing, and lamination', 'icon' => 'photograph'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Services Section - Video & Digital
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Video & Digital Services', 'subtitle' => 'Bring your story to life', 'items' => [
                    ['title' => 'Video Production', 'description' => 'Professional video shooting and editing services', 'icon' => 'video-camera'],
                    ['title' => 'Promotional Videos', 'description' => 'Adverts, promotional content, and music videos', 'icon' => 'film'],
                    ['title' => 'Event Highlights', 'description' => 'Event coverage and highlight reels', 'icon' => 'star'],
                    ['title' => 'Social Media Clips', 'description' => 'Short, engaging videos for social platforms', 'icon' => 'device-mobile'],
                    ['title' => 'Brand Consultation', 'description' => 'Brand strategy and rebranding services', 'icon' => 'light-bulb'],
                    ['title' => 'Digital Marketing', 'description' => 'Social media management, website graphics, digital ads', 'icon' => 'chart-bar'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
                
                // Portfolio Gallery
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'Our Work', 'subtitle' => 'See what we\'ve created', 'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=600&q=80', 'alt' => 'Graphic Design'],
                    ['url' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=600&q=80', 'alt' => 'Studio Work'],
                    ['url' => 'https://images.unsplash.com/photo-1542744094-3a31f272c490?w=600&q=80', 'alt' => 'Photography'],
                    ['url' => 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=600&q=80', 'alt' => 'Camera Work'],
                    ['url' => 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=600&q=80', 'alt' => 'Printing'],
                    ['url' => 'https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=600&q=80', 'alt' => 'Creative Design'],
                ]], 'style' => ['backgroundColor' => '#111827']],
                
                // CTA Section
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Ready to Start Your Project?', 'description' => 'Let\'s create something amazing together. Contact us today for a free consultation.', 'buttonText' => 'Get In Touch', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#f97316']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Our Services', 'subtitle' => 'Comprehensive Creative Solutions', 'backgroundColor' => '#1e3a8a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 300]],
                ['type' => 'services', 'content' => ['layout' => 'alternating', 'title' => 'What We Offer', 'items' => [
                    ['title' => 'Graphic Design', 'description' => 'Professional design services including logos, branding, marketing materials, and digital graphics. Our creative team brings your ideas to life with stunning visuals.', 'icon' => 'sparkles', 'image' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=600&q=80'],
                    ['title' => 'Printing Services', 'description' => 'High-quality printing for all your needs. From business cards to large format banners, we deliver crisp, vibrant prints every time.', 'icon' => 'printer', 'image' => 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=600&q=80'],
                    ['title' => 'Photography & Video', 'description' => 'Capture your moments with professional photography and videography. Studio sessions, events, and commercial projects.', 'icon' => 'camera', 'image' => 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=600&q=80'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Get In Touch', 'subtitle' => 'Let\'s Work Together', 'backgroundColor' => '#1e3a8a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 300]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Contact Us', 'description' => 'We\'re here to help bring your vision to life.', 'showForm' => true, 'email' => 'hello@2hcreative.co.zm', 'phone' => '+260 97 123 4567', 'address' => 'Lusaka, Zambia'], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);

        // Tiyamike Celebration - Band Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Tiyamike Band',
            'slug' => 'tiyamike-band',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => [
                // Hero Section with Band Image
                ['type' => 'page-header', 'content' => ['title' => 'Tiyamike Celebration', 'subtitle' => 'Let\'s Share The Dream', 'backgroundImage' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1920&q=80', 'backgroundColor' => '#000000', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 400]],
                
                // About the Band
                ['type' => 'about', 'content' => ['layout' => 'image-left', 'title' => 'About Tiyamike Celebration', 'description' => 'Tiyamike Celebration is a dynamic band music project bringing joy and energy to every event. With a passion for gospel music and live performances, we create unforgettable experiences that inspire and uplift. Our talented musicians blend traditional and contemporary sounds to deliver performances that resonate with audiences of all ages.', 'image' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800&q=80', 'features' => ['Professional Musicians', 'Gospel & Contemporary', 'Full Sound System', 'Event Coordination']], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Services
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'What We Offer', 'subtitle' => 'Complete music solutions for your events', 'items' => [
                    ['title' => 'Wedding Entertainment', 'description' => 'Make your special day unforgettable with live music that sets the perfect atmosphere', 'image' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&q=80', 'icon' => 'sparkles'],
                    ['title' => 'Church Events', 'description' => 'Powerful gospel performances that inspire worship and celebration', 'image' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=600&q=80', 'icon' => 'users'],
                    ['title' => 'Corporate Functions', 'description' => 'Professional entertainment for conferences, launches, and company celebrations', 'image' => 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=600&q=80', 'icon' => 'briefcase'],
                    ['title' => 'Private Parties', 'description' => 'Energetic performances for birthdays, anniversaries, and celebrations', 'image' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=600&q=80', 'icon' => 'star'],
                    ['title' => 'Music Production', 'description' => 'Professional recording, mixing, and mastering in our studio', 'image' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=600&q=80', 'icon' => 'film'],
                    ['title' => 'Band Training', 'description' => 'Mentorship and coaching for aspiring musicians and worship teams', 'image' => 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=600&q=80', 'icon' => 'light-bulb'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
                
                // Testimonials
                ['type' => 'testimonials', 'content' => ['layout' => 'grid', 'title' => 'What People Say', 'items' => [
                    ['name' => 'Grace & John Mwanza', 'text' => 'Tiyamike Celebration made our wedding absolutely magical! Their energy and talent created the perfect atmosphere.', 'rating' => 5, 'role' => 'Wedding Clients'],
                    ['name' => 'Pastor David Phiri', 'text' => 'The band\'s gospel performances bring such joy and inspiration to our church. Truly anointed musicians!', 'rating' => 5, 'role' => 'Church Leader'],
                    ['name' => 'Sarah Banda', 'text' => 'Professional, talented, and a joy to work with. They made our corporate event unforgettable!', 'rating' => 5, 'role' => 'Event Organizer'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                // CTA
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Book Tiyamike Celebration', 'description' => 'Let\'s make your event unforgettable with live music that inspires and entertains.', 'buttonText' => 'Contact Us', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#f59e0b']],
            ]],
        ]);
    }
}

