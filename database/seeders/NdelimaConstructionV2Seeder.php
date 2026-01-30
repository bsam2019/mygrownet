<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use Illuminate\Database\Seeder;

class NdelimaConstructionV2Seeder extends Seeder
{
    public function run(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'ndelima-construction-v2'],
            [
                'name' => 'Ndelima Enterprises v2',
                'description' => 'Enhanced construction template with 5-slide hero slideshow, Ken Burns zoom effects, elegant navigation controls, and modern animations for electrical, plumbing, aluminium windows, plastering, ceiling work, thatching, and turnkey solutions.',
                'industry' => 'construction',
                'thumbnail' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 8,
                'theme' => [
                    'primaryColor' => '#dc2626',
                    'secondaryColor' => '#ea580c',
                    'accentColor' => '#fbbf24',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'Ndelima Enterprises',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Request Quote',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Ndelima Enterprises. Building Dreams, Creating Futures.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page with Enhanced Slideshow and Different Layout
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                // Hero Slideshow Section - 5 Slides with Different Messaging
                ['type' => 'hero', 'content' => ['layout' => 'slideshow', 'autoPlay' => true, 'slideInterval' => 6000, 'slides' => [
                    ['title' => 'Building Dreams, Creating Futures', 'subtitle' => 'Your trusted partner for complete construction solutions across Zambia', 'buttonText' => 'Explore Our Work', 'buttonLink' => '/gallery', 'backgroundImage' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=1920&q=80'],
                    ['title' => 'Power Your Property', 'subtitle' => 'Licensed electricians delivering safe, reliable electrical installations', 'buttonText' => 'Electrical Services', 'buttonLink' => '/services', 'backgroundImage' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=1920&q=80'],
                    ['title' => 'Flow Without Worry', 'subtitle' => 'Expert plumbing solutions from pipes to premium bathroom fittings', 'buttonText' => 'Plumbing Services', 'buttonLink' => '/services', 'backgroundImage' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=1920&q=80'],
                    ['title' => 'Windows That Wow', 'subtitle' => 'Custom aluminium windows combining style, security, and energy efficiency', 'buttonText' => 'View Options', 'buttonLink' => '/services', 'backgroundImage' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=1920&q=80'],
                    ['title' => 'Authentic African Charm', 'subtitle' => 'Traditional thatching that brings natural beauty to your outdoor spaces', 'buttonText' => 'Thatching Gallery', 'buttonLink' => '/gallery', 'backgroundImage' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1920&q=80'],
                ]], 'style' => ['minHeight' => 700]],
                
                // About Section - Different from v1
                ['type' => 'about', 'content' => ['layout' => 'image-left', 'title' => 'Why Ndelima Stands Apart', 'description' => 'For over 15 years, Ndelima Enterprises has been transforming houses into homes and visions into reality. We\'re not just contractors—we\'re your construction partners, committed to excellence in every wire, pipe, window, and roof. Our integrated approach means you get consistent quality across all trades, backed by warranties you can trust.', 'image' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80', 'features' => ['500+ Completed Projects', 'Fully Licensed & Insured', 'Warranty on All Work', '24/7 Emergency Support']], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Services Section - Grid Layout with Icons
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Complete Construction Services', 'subtitle' => 'Everything you need under one roof', 'columns' => 3, 'items' => [
                    ['title' => 'Electrical Solutions', 'description' => 'Wiring, installations, repairs, and maintenance by certified electricians', 'icon' => 'light-bulb'],
                    ['title' => 'Plumbing Expertise', 'description' => 'From pipes to premium fittings, we handle all your water needs', 'icon' => 'cog'],
                    ['title' => 'Aluminium Windows', 'description' => 'Custom-designed windows that enhance beauty and efficiency', 'icon' => 'cube'],
                    ['title' => 'Plastering & Ceilings', 'description' => 'Smooth finishes and stunning ceiling designs', 'icon' => 'briefcase'],
                    ['title' => 'Traditional Thatching', 'description' => 'Authentic African roofing for gazebos and outdoor spaces', 'icon' => 'sparkles'],
                    ['title' => 'Project Management', 'description' => 'End-to-end coordination for stress-free construction', 'icon' => 'check'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
                
                // Stats Section - Different Position
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [
                    ['value' => '15+', 'label' => 'Years in Business'],
                    ['value' => '500+', 'label' => 'Happy Clients'],
                    ['value' => '50+', 'label' => 'Skilled Craftsmen'],
                    ['value' => '100%', 'label' => 'Quality Guarantee'],
                ]], 'style' => ['backgroundColor' => '#dc2626', 'textColor' => '#ffffff']],
                
                // Featured Projects Section
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Recent Projects', 'subtitle' => 'See the quality we deliver', 'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600&q=80', 'alt' => 'Modern Electrical Installation'],
                    ['url' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=600&q=80', 'alt' => 'Luxury Bathroom Plumbing'],
                    ['url' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=600&q=80', 'alt' => 'Contemporary Aluminium Windows'],
                    ['url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=80', 'alt' => 'Beautiful Thatched Gazebo'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Testimonials
                ['type' => 'testimonials', 'content' => ['layout' => 'carousel', 'title' => 'Client Success Stories', 'items' => [
                    ['name' => 'John Banda', 'text' => 'Ndelima transformed our entire home. Their attention to detail and professionalism exceeded all expectations. Highly recommended!', 'rating' => 5, 'role' => 'Homeowner, Lusaka'],
                    ['name' => 'Grace Mwanza', 'text' => 'The aluminium windows are stunning and have significantly reduced our energy costs. Worth every ngwee!', 'rating' => 5, 'role' => 'Property Developer'],
                    ['name' => 'David Phiri', 'text' => 'From electrical to plumbing, they handled everything seamlessly. True one-stop construction solution!', 'rating' => 5, 'role' => 'Business Owner'],
                    ['name' => 'Sarah Tembo', 'text' => 'The thatched gazebo they built is the centerpiece of our garden. Beautiful craftsmanship!', 'rating' => 5, 'role' => 'Residential Client'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
                
                // CTA Section
                ['type' => 'cta', 'content' => ['layout' => 'centered', 'title' => 'Ready to Build Your Dream?', 'description' => 'Get a free consultation and detailed quote. Let\'s discuss how we can bring your vision to life.', 'buttonText' => 'Request Free Quote', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Call Us Now', 'secondaryButtonLink' => 'tel:+260971234567'], 'style' => ['backgroundColor' => '#dc2626']],
            ]],
        ]);

        // About Us Page - NEW
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'About Us',
            'slug' => 'about',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'About Ndelima Enterprises', 'subtitle' => 'Building Dreams Since 2009', 'backgroundImage' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1920&q=80', 'backgroundColor' => '#dc2626', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 400]],
                
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Our Story', 'description' => 'Founded in 2009, Ndelima Enterprises began with a simple mission: to provide honest, quality construction services to the people of Zambia. What started as a small electrical contracting business has grown into a full-service construction company, trusted by hundreds of homeowners and businesses across the country. Our success is built on three pillars: quality craftsmanship, transparent pricing, and unwavering commitment to our clients.', 'image' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&q=80', 'features' => ['Family-Owned Business', 'Zambian Roots', 'Community Focused', 'Ethical Practices']], 'style' => ['backgroundColor' => '#ffffff']],
                
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Our Values', 'subtitle' => 'What drives us every day', 'items' => [
                    ['title' => 'Quality First', 'description' => 'We never compromise on materials or workmanship. Your satisfaction is our reputation.'],
                    ['title' => 'Transparency', 'description' => 'Clear quotes, honest timelines, and open communication throughout your project.'],
                    ['title' => 'Innovation', 'description' => 'We stay current with the latest construction techniques and materials.'],
                    ['title' => 'Reliability', 'description' => 'When we commit to a deadline, we deliver. Your time matters to us.'],
                    ['title' => 'Safety', 'description' => 'All our work meets or exceeds safety standards. We protect our team and your property.'],
                    ['title' => 'Community', 'description' => 'We invest in local talent and give back to the communities we serve.'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
                
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [
                    ['value' => '15+', 'label' => 'Years Experience'],
                    ['value' => '500+', 'label' => 'Projects Completed'],
                    ['value' => '50+', 'label' => 'Team Members'],
                    ['value' => '98%', 'label' => 'Client Satisfaction'],
                ]], 'style' => ['backgroundColor' => '#dc2626', 'textColor' => '#ffffff']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Our Services', 'subtitle' => 'Comprehensive Construction Solutions', 'backgroundImage' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1920&q=80', 'backgroundColor' => '#dc2626', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 350]],
                
                // Detailed Services with Images
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'What We Do Best', 'subtitle' => 'Expert services across all construction trades', 'items' => [
                    ['title' => 'Electrical Services', 'description' => 'Complete electrical solutions including new installations, rewiring, repairs, lighting design, power distribution, and 24/7 emergency services. All work by licensed electricians.', 'icon' => 'light-bulb', 'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600&q=80'],
                    ['title' => 'Plumbing Solutions', 'description' => 'Professional plumbing from pipes to premium fittings. Water heaters, drainage systems, bathroom and kitchen installations, repairs, and emergency plumbing services.', 'icon' => 'cog', 'image' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=600&q=80'],
                    ['title' => 'Aluminium Windows & Doors', 'description' => 'Custom-designed aluminium windows and doors. Energy-efficient, secure, and stylish. Full service from measurement to installation with warranty.', 'icon' => 'cube', 'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=600&q=80'],
                    ['title' => 'Plastering & Ceiling Work', 'description' => 'Expert plastering for walls and ceilings. Smooth finishes, textured designs, suspended ceilings, gypsum installations, and decorative ceiling solutions.', 'icon' => 'briefcase', 'image' => 'https://images.unsplash.com/photo-1581858726788-75bc0f6a952d?w=600&q=80'],
                    ['title' => 'Thatching Services', 'description' => 'Traditional and modern thatching for roofs, gazebos, lapa structures, and outdoor entertainment areas. Quality materials with waterproofing treatments.', 'icon' => 'sparkles', 'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=80'],
                    ['title' => 'Turnkey Projects', 'description' => 'Complete project management from concept to completion. We coordinate all trades, manage timelines, and ensure quality. One point of contact for everything.', 'icon' => 'check', 'image' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Service Process
                ['type' => 'features', 'content' => ['layout' => 'steps', 'title' => 'How We Work', 'subtitle' => 'Our proven 6-step process', 'items' => [
                    ['title' => '1. Free Consultation', 'description' => 'We visit your site, discuss your needs, and provide expert recommendations'],
                    ['title' => '2. Detailed Quote', 'description' => 'Receive a transparent, itemized quote with no hidden costs'],
                    ['title' => '3. Project Planning', 'description' => 'We create a detailed timeline and coordinate all necessary trades'],
                    ['title' => '4. Quality Execution', 'description' => 'Our skilled team delivers exceptional workmanship'],
                    ['title' => '5. Regular Updates', 'description' => 'Stay informed with progress reports and site visits'],
                    ['title' => '6. Final Handover', 'description' => 'Complete inspection, warranty documentation, and maintenance guidance'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);

        // Gallery Page - NEW (separate from Projects)
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Gallery',
            'slug' => 'gallery',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Project Gallery', 'subtitle' => 'Our Work Speaks for Itself', 'backgroundImage' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=1920&q=80', 'backgroundColor' => '#dc2626', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 350]],
                
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'Completed Projects', 'subtitle' => 'Quality craftsmanship in every detail', 'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600&q=80', 'alt' => 'Modern Electrical Installation'],
                    ['url' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=600&q=80', 'alt' => 'Luxury Bathroom Plumbing'],
                    ['url' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=600&q=80', 'alt' => 'Contemporary Aluminium Windows'],
                    ['url' => 'https://images.unsplash.com/photo-1581858726788-75bc0f6a952d?w=600&q=80', 'alt' => 'Elegant Ceiling Design'],
                    ['url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=80', 'alt' => 'Traditional Thatched Gazebo'],
                    ['url' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80', 'alt' => 'Complete Home Renovation'],
                    ['url' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80', 'alt' => 'Commercial Construction'],
                    ['url' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=600&q=80', 'alt' => 'Residential Project'],
                ]], 'style' => ['backgroundColor' => '#1f2937']],
                
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Want Your Project Featured Here?', 'description' => 'Let\'s create something amazing together. Contact us for a free consultation.', 'buttonText' => 'Start Your Project', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#dc2626']],
            ]],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 5,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Get In Touch', 'subtitle' => 'Let\'s Discuss Your Project', 'backgroundColor' => '#dc2626', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 300]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Request Your Free Quote', 'description' => 'Fill out the form below and we\'ll get back to you within 24 hours with a detailed, no-obligation quote.', 'showForm' => true, 'email' => 'info@ndelima.co.zm', 'phone' => '+260 97 123 4567', 'address' => 'Plot 123, Industrial Area, Lusaka, Zambia', 'hours' => 'Mon-Fri: 7:30 AM - 5:30 PM, Sat: 8:00 AM - 1:00 PM'], 'style' => ['backgroundColor' => '#ffffff']],
                
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why Choose Us', 'subtitle' => 'What makes Ndelima different', 'items' => [
                    ['title' => 'Free Consultations', 'description' => 'No-obligation site visits and expert advice'],
                    ['title' => 'Transparent Pricing', 'description' => 'Detailed quotes with no hidden costs'],
                    ['title' => 'Quality Guarantee', 'description' => 'Warranty on all workmanship and materials'],
                    ['title' => '24/7 Emergency', 'description' => 'Round-the-clock support for urgent issues'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);
    }
}
