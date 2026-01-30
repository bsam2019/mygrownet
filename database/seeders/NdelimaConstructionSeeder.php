<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use Illuminate\Database\Seeder;

class NdelimaConstructionSeeder extends Seeder
{
    public function run(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'ndelima-construction'],
            [
                'name' => 'Ndelima Enterprises',
                'description' => 'Professional construction template for electrical, plumbing, aluminium windows, plastering, ceiling work, painting, and complete turnkey solutions.',
                'industry' => 'construction',
                'thumbnail' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 7,
                'theme' => [
                    'primaryColor' => '#ea580c',
                    'secondaryColor' => '#dc2626',
                    'accentColor' => '#f59e0b',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'Ndelima Enterprises',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Get Quote',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Ndelima Enterprises. Your One-Stop Construction Solution.',
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
                // Hero Section
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Your One-Stop Construction Solution', 'subtitle' => 'Quality craftsmanship for electrical, plumbing, aluminium windows, plastering, ceiling work, painting, and more', 'buttonText' => 'Get Free Quote', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Our Services', 'secondaryButtonLink' => '/services', 'backgroundImage' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=1920&q=80', 'overlayColor' => 'black', 'overlayOpacity' => 50], 'style' => ['minHeight' => 650]],
                
                // Stats Section
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [
                    ['value' => '500+', 'label' => 'Projects Completed'],
                    ['value' => '100%', 'label' => 'Quality Guaranteed'],
                    ['value' => '15+', 'label' => 'Years Experience'],
                    ['value' => '24/7', 'label' => 'Support Available'],
                ]], 'style' => ['backgroundColor' => '#ea580c', 'textColor' => '#ffffff']],
                
                // About Section
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Building Excellence Since Day One', 'description' => 'Ndelima Enterprises is your trusted partner for all construction needs. From electrical wiring to thatching, we provide comprehensive turnkey solutions with one point of contact. Our experienced team delivers quality workmanship, timely completion, and competitive pricing on every project.', 'image' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80', 'features' => ['Licensed & Insured', 'Experienced Professionals', 'Quality Materials', 'Competitive Pricing']], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Services Section - Main Services (No Thatching)
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'Our Services', 'subtitle' => 'Professional construction solutions', 'items' => [
                    ['title' => 'Electrical Wiring', 'description' => 'Professional electrical installations, repairs, and maintenance for residential and commercial properties', 'icon' => 'light-bulb', 'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600&q=80'],
                    ['title' => 'Plumbing Services', 'description' => 'Complete plumbing solutions including installations, repairs, and bathroom fittings', 'icon' => 'cog', 'image' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=600&q=80'],
                    ['title' => 'Aluminium Windows', 'description' => 'Custom aluminium window installations for modern, durable, and energy-efficient solutions', 'icon' => 'cube', 'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=600&q=80'],
                    ['title' => 'Plastering & Ceiling', 'description' => 'Expert plastering and ceiling work for smooth, professional finishes', 'icon' => 'briefcase', 'image' => 'https://images.unsplash.com/photo-1581858726788-75bc0f6a952d?w=600&q=80'],
                    ['title' => 'Painting Services', 'description' => 'Interior and exterior painting with premium finishes and attention to detail', 'icon' => 'sparkles', 'image' => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?w=600&q=80'],
                    ['title' => 'Turnkey Solutions', 'description' => 'Complete project management from start to finish - one point of contact for all your needs', 'icon' => 'check', 'image' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
                
                // Why Choose Us Section
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why Choose Ndelima Enterprises', 'subtitle' => 'Your trusted construction partner', 'items' => [
                    ['title' => 'One-Stop Solution', 'description' => 'Handle everything from wiring to windows with a single point of contact'],
                    ['title' => 'Quality Focus', 'description' => 'Expertise in each service ensures top-notch results every time'],
                    ['title' => 'Convenience', 'description' => 'Coordinated project management saves you time and hassle'],
                    ['title' => 'Competitive Pricing', 'description' => 'Fair, transparent pricing with no hidden costs'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Testimonials
                ['type' => 'testimonials', 'content' => ['layout' => 'grid', 'title' => 'What Our Clients Say', 'items' => [
                    ['name' => 'John Banda', 'text' => 'Ndelima handled our entire house renovation. From electrical to plumbing, everything was done professionally and on time!', 'rating' => 5, 'role' => 'Homeowner'],
                    ['name' => 'Grace Mwanza', 'text' => 'The aluminium windows they installed are beautiful and energy-efficient. Highly recommend their work!', 'rating' => 5, 'role' => 'Property Owner'],
                    ['name' => 'David Phiri', 'text' => 'Professional team, quality work, and great prices. They are now our go-to for all construction needs.', 'rating' => 5, 'role' => 'Business Owner'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
                
                // CTA Section
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Ready to Start Your Project?', 'description' => 'Get a free quote today and experience the Ndelima difference. Quality work, competitive prices, one point of contact.', 'buttonText' => 'Get Free Quote', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#ea580c']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Our Services', 'subtitle' => 'Complete Construction Solutions', 'backgroundImage' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1920&q=80', 'backgroundColor' => '#ea580c', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 350]],
                
                // Detailed Services
                ['type' => 'services', 'content' => ['layout' => 'alternating', 'title' => 'What We Offer', 'items' => [
                    ['title' => 'Electrical Wiring & Installations', 'description' => 'Complete electrical services for residential, commercial, and industrial properties. We handle new installations, rewiring, repairs, lighting, power distribution, and electrical maintenance. All work is done to code by licensed electricians.', 'icon' => 'light-bulb', 'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600&q=80'],
                    ['title' => 'Plumbing & Bathroom Fittings', 'description' => 'Professional plumbing services including pipe installations, repairs, water heater installations, bathroom and kitchen fittings, drainage systems, and emergency plumbing. We use quality materials and provide warranties on all work.', 'icon' => 'cog', 'image' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=600&q=80'],
                    ['title' => 'Aluminium Windows & Doors', 'description' => 'Custom-made aluminium windows and doors for modern homes and offices. Energy-efficient, durable, and stylish. We handle measurements, manufacturing, and professional installation. Available in various colors and styles.', 'icon' => 'cube', 'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=600&q=80'],
                    ['title' => 'Plastering & Ceiling Work', 'description' => 'Expert plastering for walls and ceilings, including smooth finishes, textured finishes, and repairs. We also install suspended ceilings, gypsum ceilings, and decorative ceiling designs for residential and commercial spaces.', 'icon' => 'briefcase', 'image' => 'https://images.unsplash.com/photo-1581858726788-75bc0f6a952d?w=600&q=80'],
                    ['title' => 'Painting Services', 'description' => 'Professional interior and exterior painting services for residential and commercial properties. We use premium paints and finishes, provide color consultation, and ensure meticulous preparation and application for long-lasting, beautiful results.', 'icon' => 'sparkles', 'image' => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?w=600&q=80'],
                    ['title' => 'Turnkey Construction Projects', 'description' => 'Complete project management from design to completion. We coordinate all trades, manage timelines, and ensure quality control. One point of contact for your entire construction project - residential, commercial, or industrial.', 'icon' => 'check', 'image' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Service Process
                ['type' => 'features', 'content' => ['layout' => 'steps', 'title' => 'Our Process', 'subtitle' => 'How we work with you', 'items' => [
                    ['title' => 'Consultation', 'description' => 'We discuss your needs, assess the site, and provide expert recommendations'],
                    ['title' => 'Quote', 'description' => 'Receive a detailed, transparent quote with no hidden costs'],
                    ['title' => 'Planning', 'description' => 'We plan the project timeline, materials, and coordinate all necessary trades'],
                    ['title' => 'Execution', 'description' => 'Our skilled team completes the work to the highest standards'],
                    ['title' => 'Quality Check', 'description' => 'Thorough inspection to ensure everything meets our quality standards'],
                    ['title' => 'Handover', 'description' => 'Project completion with warranty and maintenance guidance'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);

        // Projects/Gallery Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Projects',
            'slug' => 'projects',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Work', 'subtitle' => 'See What We\'ve Built', 'backgroundImage' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=1920&q=80', 'backgroundColor' => '#ea580c', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 350]],
                
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'Completed Projects', 'subtitle' => 'Quality work that speaks for itself', 'images' => [
                    ['url' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600&q=80', 'alt' => 'Electrical Work'],
                    ['url' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=600&q=80', 'alt' => 'Plumbing Installation'],
                    ['url' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=600&q=80', 'alt' => 'Aluminium Windows'],
                    ['url' => 'https://images.unsplash.com/photo-1581858726788-75bc0f6a952d?w=600&q=80', 'alt' => 'Ceiling Work'],
                    ['url' => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?w=600&q=80', 'alt' => 'Painting Services'],
                    ['url' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80', 'alt' => 'Complete Project'],
                ]], 'style' => ['backgroundColor' => '#1f2937']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Get In Touch', 'subtitle' => 'Let\'s Build Something Great Together', 'backgroundColor' => '#ea580c', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 300]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Request a Quote', 'description' => 'Fill out the form and we\'ll get back to you within 24 hours with a detailed quote.', 'showForm' => true, 'email' => 'info@ndelima.co.zm', 'phone' => '+260 97 123 4567', 'address' => 'Lusaka, Zambia'], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);
    }
}
