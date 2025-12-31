<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use App\Models\GrowBuilder\SiteTemplateIndustry;
use Illuminate\Database\Seeder;

class MoreSiteTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        // Create additional industries
        $this->createAdditionalIndustries();
        
        // Create templates
        $this->createHotelTemplate();
        $this->createAutoRepairTemplate();
        $this->createChurchTemplate();
        $this->createConstructionTemplate();
        $this->createLogisticsTemplate();
        $this->createAccountingTemplate();
        $this->createEventPlanningTemplate();
        $this->createAgricultureTemplate();
        $this->createCarDealershipTemplate();
        $this->createBarbershopTemplate();
    }

    private function createAdditionalIndustries(): void
    {
        $industries = [
            ['name' => 'Hospitality & Hotels', 'slug' => 'hospitality', 'icon' => 'building', 'sort_order' => 12],
            ['name' => 'Automotive & Repair', 'slug' => 'automotive', 'icon' => 'car', 'sort_order' => 13],
            ['name' => 'Church & Non-Profit', 'slug' => 'nonprofit', 'icon' => 'heart', 'sort_order' => 14],
            ['name' => 'Construction & Building', 'slug' => 'construction', 'icon' => 'hammer', 'sort_order' => 15],
            ['name' => 'Logistics & Transport', 'slug' => 'logistics', 'icon' => 'truck', 'sort_order' => 16],
            ['name' => 'Accounting & Finance', 'slug' => 'accounting', 'icon' => 'calculator', 'sort_order' => 17],
            ['name' => 'Events & Entertainment', 'slug' => 'events', 'icon' => 'calendar', 'sort_order' => 18],
            ['name' => 'Agriculture & Farming', 'slug' => 'agriculture', 'icon' => 'leaf', 'sort_order' => 19],
            ['name' => 'Automotive Sales', 'slug' => 'carsales', 'icon' => 'car', 'sort_order' => 20],
            ['name' => 'Barbershop & Grooming', 'slug' => 'barbershop', 'icon' => 'scissors', 'sort_order' => 21],
        ];

        foreach ($industries as $industry) {
            SiteTemplateIndustry::updateOrCreate(['slug' => $industry['slug']], $industry);
        }
    }


    private function createHotelTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'victoria-lodge'],
            [
                'name' => 'Victoria Lodge',
                'description' => 'Elegant hotel and accommodation template with room bookings, amenities showcase, and guest services.',
                'industry' => 'hospitality',
                'thumbnail' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 12,
                'theme' => [
                    'primaryColor' => '#0f766e',
                    'secondaryColor' => '#0f172a',
                    'accentColor' => '#14b8a6',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'Victoria Lodge',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Book Now',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Victoria Lodge. All rights reserved.',
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
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Experience Zambian Hospitality at Its Finest', 'subtitle' => 'Luxury accommodation in the heart of Livingstone. Minutes from Victoria Falls. Your perfect getaway awaits.', 'buttonText' => 'Book Your Stay', 'buttonLink' => '/rooms', 'secondaryButtonText' => 'View Rooms', 'secondaryButtonLink' => '/rooms', 'backgroundImage' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=80', 'overlayColor' => 'black', 'overlayOpacity' => 40], 'style' => ['minHeight' => 700]],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Our Amenities', 'items' => [['title' => 'Free WiFi', 'description' => 'High-speed internet throughout', 'icon' => 'wifi'], ['title' => 'Swimming Pool', 'description' => 'Outdoor pool with bar service', 'icon' => 'water'], ['title' => 'Restaurant', 'description' => 'Fine dining with local cuisine', 'icon' => 'utensils'], ['title' => 'Airport Shuttle', 'description' => 'Complimentary pickup/drop-off', 'icon' => 'car'], ['title' => 'Conference Hall', 'description' => 'Fully equipped meeting rooms', 'icon' => 'briefcase'], ['title' => '24/7 Reception', 'description' => 'Round-the-clock service', 'icon' => 'clock']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Our Rooms', 'columns' => 3, 'images' => [['url' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=600&q=80', 'caption' => 'Deluxe Room - K850/night'], ['url' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600&q=80', 'caption' => 'Executive Suite - K1,500/night'], ['url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=600&q=80', 'caption' => 'Presidential Suite - K2,800/night']]], 'style' => ['backgroundColor' => '#f0fdfa']],
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Welcome to Victoria Lodge', 'description' => 'Nestled in the tourist capital of Zambia, Victoria Lodge offers the perfect blend of comfort, luxury, and authentic Zambian hospitality. Whether you\'re here for the Falls, safari, or business, we make your stay unforgettable.', 'image' => 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=800&q=80', 'features' => ['5-star rated on TripAdvisor', '50 luxurious rooms and suites', 'Award-winning restaurant', 'Prime location near Victoria Falls']], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'testimonials', 'content' => ['layout' => 'carousel', 'title' => 'Guest Reviews', 'items' => [['name' => 'Sarah Johnson', 'text' => 'Absolutely stunning! The staff went above and beyond. Best hotel experience in Zambia.', 'role' => 'UK Tourist', 'rating' => 5], ['name' => 'David Mwale', 'text' => 'Perfect for business trips. Great conference facilities and excellent service.', 'role' => 'Corporate Guest', 'rating' => 5]]], 'style' => ['backgroundColor' => '#f0fdfa']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Book Direct & Save 15%', 'description' => 'Best rates guaranteed when you book directly with us.', 'buttonText' => 'Check Availability', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#0f766e']],
            ]],
        ]);

        // Rooms Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Rooms',
            'slug' => 'rooms',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Rooms & Suites', 'subtitle' => 'Luxury accommodation for every traveler', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Room Types', 'plans' => [['name' => 'Standard Room', 'price' => 'K650/night', 'features' => ['Queen bed', 'Air conditioning', 'Free WiFi', 'Breakfast included', 'Garden view']], ['name' => 'Deluxe Room', 'price' => 'K850/night', 'popular' => true, 'features' => ['King bed', 'Mini bar', 'Balcony', 'Premium toiletries', 'Pool view']], ['name' => 'Executive Suite', 'price' => 'K1,500/night', 'features' => ['Separate living area', 'Jacuzzi', 'Butler service', 'Complimentary drinks', 'Falls view']]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'All Rooms Include', 'items' => [['title' => 'Free WiFi', 'description' => 'High-speed internet'], ['title' => 'Air Conditioning', 'description' => 'Climate control'], ['title' => 'Flat Screen TV', 'description' => 'DSTV channels'], ['title' => 'Safe', 'description' => 'In-room safe'], ['title' => 'Tea/Coffee', 'description' => 'Making facilities'], ['title' => 'Daily Housekeeping', 'description' => 'Twice daily service']]], 'style' => ['backgroundColor' => '#f0fdfa']],
            ]],
        ]);

        // Facilities Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Facilities',
            'slug' => 'facilities',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Hotel Facilities', 'subtitle' => 'Everything you need for a perfect stay', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'Our Facilities', 'items' => [['title' => 'Restaurant & Bar', 'description' => 'Fine dining with international and local cuisine. Full bar with signature cocktails.', 'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600&q=80'], ['title' => 'Swimming Pool', 'description' => 'Large outdoor pool with sun loungers and poolside bar service.', 'image' => 'https://images.unsplash.com/photo-1575429198097-0414ec08e8cd?w=600&q=80'], ['title' => 'Conference Center', 'description' => 'Modern meeting rooms with AV equipment for up to 200 guests.', 'image' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=600&q=80'], ['title' => 'Spa & Wellness', 'description' => 'Massage services, sauna, and fitness center.', 'image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Book Your Stay', 'subtitle' => 'We look forward to welcoming you', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Make a Reservation', 'description' => 'Book online or call us directly for special requests.', 'showForm' => true, 'email' => 'reservations@victorialodge.co.zm', 'phone' => '+260 21 332 1234', 'whatsapp' => '+260 97 777 8888', 'address' => 'Mosi-oa-Tunya Road, Livingstone, Zambia'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Getting Here', 'items' => [['title' => 'From Airport', 'description' => '15 minutes drive - Free shuttle'], ['title' => 'From Victoria Falls', 'description' => '10 minutes drive'], ['title' => 'From Town Center', 'description' => '5 minutes walk'], ['title' => 'Parking', 'description' => 'Free secure parking']]], 'style' => ['backgroundColor' => '#f0fdfa']],
            ]],
        ]);
    }


    private function createAutoRepairTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'autofix-garage'],
            [
                'name' => 'AutoFix Garage',
                'description' => 'Professional auto repair template with service packages, booking system, and parts shop.',
                'industry' => 'automotive',
                'thumbnail' => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 13,
                'theme' => [
                    'primaryColor' => '#dc2626',
                    'secondaryColor' => '#18181b',
                    'accentColor' => '#f87171',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'AutoFix',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Book Service',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 AutoFix Garage. All rights reserved.',
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
                ['type' => 'hero', 'content' => ['layout' => 'split-left', 'title' => 'Expert Auto Repair You Can Trust', 'subtitle' => 'Professional car servicing, repairs, and maintenance in Lusaka. Certified mechanics, genuine parts, fair prices.', 'buttonText' => 'Book Service', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Our Services', 'secondaryButtonLink' => '/services', 'image' => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=800&q=80'], 'style' => ['backgroundColor' => '#18181b', 'minHeight' => 600]],
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [['value' => '15+', 'label' => 'Years Experience'], ['value' => '10,000+', 'label' => 'Cars Serviced'], ['value' => '98%', 'label' => 'Customer Satisfaction'], ['value' => 'Same Day', 'label' => 'Service Available']]], 'style' => ['backgroundColor' => '#dc2626']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Our Services', 'subtitle' => 'Complete auto care under one roof', 'items' => [['title' => 'General Service', 'description' => 'Oil change, filters, fluid top-ups, and full inspection.', 'icon' => 'cog'], ['title' => 'Engine Repair', 'description' => 'Diagnostics, repairs, and engine rebuilds by experts.', 'icon' => 'wrench'], ['title' => 'Brake Service', 'description' => 'Brake pads, discs, fluid replacement, and ABS repair.', 'icon' => 'shield'], ['title' => 'Electrical', 'description' => 'Battery, alternator, starter motor, and wiring repairs.', 'icon' => 'bolt'], ['title' => 'Suspension', 'description' => 'Shocks, springs, bushings, and alignment services.', 'icon' => 'car'], ['title' => 'AC Service', 'description' => 'Air conditioning repair, regas, and maintenance.', 'icon' => 'snowflake']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'checklist', 'title' => 'Why Choose AutoFix?', 'items' => [['title' => 'Certified Mechanics', 'description' => 'Trained and experienced technicians'], ['title' => 'Genuine Parts', 'description' => 'Original and quality aftermarket parts'], ['title' => 'Fair Pricing', 'description' => 'Transparent quotes, no hidden fees'], ['title' => 'Warranty', 'description' => '6-month warranty on all repairs'], ['title' => 'Free Diagnostics', 'description' => 'Complimentary vehicle inspection'], ['title' => 'Courtesy Car', 'description' => 'Available for major repairs']]], 'style' => ['backgroundColor' => '#f9fafb']],
                ['type' => 'testimonials', 'content' => ['layout' => 'grid', 'title' => 'Customer Reviews', 'items' => [['name' => 'John Mwale', 'text' => 'Honest mechanics who don\'t overcharge. Fixed my engine problem quickly and properly.', 'role' => 'Toyota Owner', 'rating' => 5], ['name' => 'Grace Banda', 'text' => 'Best garage in Lusaka! Professional service and reasonable prices. Highly recommend!', 'role' => 'Honda Owner', 'rating' => 5]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Need a Service or Repair?', 'description' => 'Book your appointment today. Same-day service available.', 'buttonText' => 'Book Now', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#dc2626']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Our Services', 'subtitle' => 'Professional auto care for all makes and models', 'backgroundColor' => '#18181b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Service Packages', 'plans' => [['name' => 'Basic Service', 'price' => 'From K450', 'features' => ['Oil & filter change', 'Fluid level check', 'Tire pressure check', 'Visual inspection', 'Wash & vacuum']], ['name' => 'Full Service', 'price' => 'From K850', 'popular' => true, 'features' => ['Everything in Basic', 'Air filter replacement', 'Spark plugs check', 'Brake inspection', 'Battery test', 'Detailed report']], ['name' => 'Major Service', 'price' => 'From K1,500', 'features' => ['Everything in Full', 'Transmission service', 'Coolant flush', 'Brake fluid change', 'Suspension check', '12-month warranty']]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Specialized Services', 'items' => [['title' => 'Engine Diagnostics', 'description' => 'From K200'], ['title' => 'Brake Repair', 'description' => 'From K350'], ['title' => 'AC Regas', 'description' => 'From K400'], ['title' => 'Wheel Alignment', 'description' => 'From K250'], ['title' => 'Battery Replacement', 'description' => 'From K600'], ['title' => 'Clutch Repair', 'description' => 'From K1,200']]], 'style' => ['backgroundColor' => '#f9fafb']],
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
                ['type' => 'page-header', 'content' => ['title' => 'About Us', 'subtitle' => 'Your trusted auto repair partner', 'backgroundColor' => '#18181b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Our Story', 'description' => 'Founded in 2009, AutoFix has grown to become one of Lusaka\'s most trusted auto repair shops. Our team of certified mechanics brings decades of combined experience to every job, big or small.', 'image' => 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?w=800&q=80', 'features' => ['ASE certified mechanics', 'State-of-the-art equipment', 'All makes and models', 'Transparent pricing']], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'team', 'content' => ['layout' => 'grid', 'title' => 'Our Team', 'items' => [['name' => 'Peter Mwamba', 'role' => 'Master Mechanic', 'bio' => '20+ years experience', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80'], ['name' => 'David Phiri', 'role' => 'Electrical Specialist', 'bio' => 'Certified auto electrician', 'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80']]], 'style' => ['backgroundColor' => '#f9fafb']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Book Your Service', 'subtitle' => 'Get your car back on the road', 'backgroundColor' => '#18181b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Schedule Appointment', 'description' => 'Fill out the form or call us. We\'ll get back to you within 1 hour.', 'showForm' => true, 'email' => 'info@autofix.co.zm', 'phone' => '+260 21 123 4567', 'whatsapp' => '+260 97 888 7777', 'address' => 'Plot 456, Lumumba Road, Industrial Area, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Opening Hours', 'items' => [['title' => 'Monday - Friday', 'description' => '7:30 AM - 5:30 PM'], ['title' => 'Saturday', 'description' => '8:00 AM - 2:00 PM'], ['title' => 'Sunday', 'description' => 'Closed'], ['title' => 'Emergency', 'description' => 'Call for breakdown service']]], 'style' => ['backgroundColor' => '#f9fafb']],
            ]],
        ]);
    }


    private function createChurchTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'grace-community-church'],
            [
                'name' => 'Grace Community Church',
                'description' => 'Inspiring church and non-profit template with sermons, events, donations, and community programs.',
                'industry' => 'nonprofit',
                'thumbnail' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 14,
                'theme' => [
                    'primaryColor' => '#7c3aed',
                    'secondaryColor' => '#1e293b',
                    'accentColor' => '#a78bfa',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'Grace Church',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Give',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Grace Community Church. All rights reserved.',
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
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Welcome Home', 'subtitle' => 'A place where faith, hope, and love come together. Join us every Sunday as we worship and grow together.', 'buttonText' => 'Join Us Sunday', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Watch Online', 'secondaryButtonLink' => '/sermons', 'backgroundImage' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=1200&q=80', 'overlayColor' => 'gradient', 'overlayGradientFrom' => '#7c3aed', 'overlayGradientTo' => '#4c1d95', 'overlayOpacity' => 70], 'style' => ['minHeight' => 650]],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Service Times', 'items' => [['title' => 'Sunday Service', 'description' => '9:00 AM & 11:00 AM'], ['title' => 'Wednesday Bible Study', 'description' => '6:00 PM'], ['title' => 'Friday Youth Service', 'description' => '7:00 PM'], ['title' => 'Saturday Prayer', 'description' => '6:00 AM']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Our Mission', 'description' => 'Grace Community Church exists to glorify God by making disciples who love Jesus, love people, and serve the world. We are a diverse, multi-generational family united in Christ.', 'image' => 'https://images.unsplash.com/photo-1507692049790-de58290a4334?w=800&q=80', 'features' => ['Bible-based teaching', 'Vibrant worship', 'Strong community', 'Outreach programs']], 'style' => ['backgroundColor' => '#faf5ff']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Get Involved', 'items' => [['title' => 'Worship Team', 'description' => 'Join our music and creative arts ministry'], ['title' => 'Children\'s Ministry', 'description' => 'Serve the next generation'], ['title' => 'Community Outreach', 'description' => 'Feed the hungry, help the needy'], ['title' => 'Small Groups', 'description' => 'Connect in fellowship and study']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'testimonials', 'content' => ['layout' => 'single', 'title' => 'Life Changed', 'items' => [['name' => 'Chanda Mwale', 'text' => 'This church family has been a blessing to me and my family. The teaching is solid, the worship is powerful, and the love is genuine.', 'role' => 'Member since 2020', 'rating' => 5]]], 'style' => ['backgroundColor' => '#faf5ff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Support Our Mission', 'description' => 'Your generosity helps us reach more people with the love of Christ.', 'buttonText' => 'Give Online', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#7c3aed']],
            ]],
        ]);

        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'About',
            'slug' => 'about',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'About Us', 'subtitle' => 'Our story, beliefs, and leadership', 'backgroundColor' => '#1e293b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'about', 'content' => ['layout' => 'image-left', 'title' => 'Our Story', 'description' => 'Founded in 1995, Grace Community Church started with 20 believers meeting in a home. Today, we are a thriving community of over 2,000 members, but our mission remains the same: to know Christ and make Him known.', 'image' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&q=80'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'team', 'content' => ['layout' => 'social', 'title' => 'Our Leadership', 'items' => [['name' => 'Pastor John Mwamba', 'role' => 'Senior Pastor', 'bio' => 'Leading with vision and compassion', 'image' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&q=80'], ['name' => 'Pastor Grace Phiri', 'role' => 'Associate Pastor', 'bio' => 'Passionate about discipleship', 'image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&q=80']]], 'style' => ['backgroundColor' => '#faf5ff']],
            ]],
        ]);

        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Sermons',
            'slug' => 'sermons',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Sermons', 'subtitle' => 'Watch and listen to past messages', 'backgroundColor' => '#1e293b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Recent Messages', 'columns' => 3, 'images' => [['url' => 'https://images.unsplash.com/photo-1507692049790-de58290a4334?w=600&q=80', 'caption' => 'Faith That Moves Mountains'], ['url' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=600&q=80', 'caption' => 'Living in Purpose'], ['url' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=600&q=80', 'caption' => 'The Power of Prayer']]], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);

        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Visit Us', 'subtitle' => 'We\'d love to meet you', 'backgroundColor' => '#1e293b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'with-map', 'title' => 'Get in Touch', 'description' => 'Have questions? Need prayer? We\'re here for you.', 'showForm' => true, 'email' => 'info@gracechurch.co.zm', 'phone' => '+260 21 234 5678', 'address' => 'Plot 789, Church Road, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);
    }


    // Placeholder methods for remaining templates - to be implemented
    private function createConstructionTemplate(): void { /* TODO */ }
    private function createLogisticsTemplate(): void { /* TODO */ }
    private function createAccountingTemplate(): void { /* TODO */ }
    private function createEventPlanningTemplate(): void { /* TODO */ }
    private function createAgricultureTemplate(): void { /* TODO */ }
    private function createCarDealershipTemplate(): void { /* TODO */ }
    private function createBarbershopTemplate(): void { /* TODO */ }
}
