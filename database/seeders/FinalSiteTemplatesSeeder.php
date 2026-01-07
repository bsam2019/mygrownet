<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use Illuminate\Database\Seeder;

class FinalSiteTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $this->createConstructionTemplate();
        $this->createLogisticsTemplate();
        $this->createAccountingTemplate();
        $this->createEventPlanningTemplate();
        $this->createAgricultureTemplate();
        $this->createCarDealershipTemplate();
        $this->createBarbershopTemplate();
    }

    private function createConstructionTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'buildpro-construction'], [
            'name' => 'BuildPro Construction', 'description' => 'Professional construction company template with project portfolio, services, and quote requests.',
            'industry' => 'construction', 'thumbnail' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&q=80',
            'is_premium' => false, 'is_active' => true, 'sort_order' => 15,
            'theme' => ['primaryColor' => '#f97316', 'secondaryColor' => '#1c1917', 'accentColor' => '#fb923c'],
            'settings' => ['navigation' => ['logoText' => 'BuildPro', 'sticky' => true, 'showCta' => true, 'ctaText' => 'Get Quote', 'ctaLink' => '/contact'], 'footer' => ['copyrightText' => '© 2024 BuildPro Construction. All rights reserved.']],
        ]);
        $template->pages()->delete();
        SiteTemplatePage::create(['site_template_id' => $template->id, 'title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'show_in_nav' => true, 'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'split-left', 'title' => 'Building Zambia\'s Future', 'subtitle' => 'Quality construction services for residential, commercial, and industrial projects. Licensed contractors, on-time delivery.', 'buttonText' => 'Our Projects', 'buttonLink' => '/projects', 'secondaryButtonText' => 'Get Quote', 'secondaryButtonLink' => '/contact', 'image' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&q=80'], 'style' => ['backgroundColor' => '#1c1917', 'minHeight' => 600]],
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [['value' => '200+', 'label' => 'Projects Completed'], ['value' => '20+', 'label' => 'Years Experience'], ['value' => '100%', 'label' => 'Client Satisfaction'], ['value' => 'K500M+', 'label' => 'Project Value']]], 'style' => ['backgroundColor' => '#f97316']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Our Services', 'items' => [['title' => 'Residential Building', 'description' => 'Custom homes, renovations, extensions'], ['title' => 'Commercial Construction', 'description' => 'Offices, shops, warehouses'], ['title' => 'Road Construction', 'description' => 'Roads, bridges, drainage'], ['title' => 'Project Management', 'description' => 'Full project oversight'], ['title' => 'Architectural Design', 'description' => 'Plans and approvals'], ['title' => 'Maintenance', 'description' => 'Building maintenance services']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'Recent Projects', 'images' => [['url' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80']]], 'style' => ['backgroundColor' => '#fafaf9']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Start Your Project Today', 'description' => 'Get a free quote and consultation.', 'buttonText' => 'Request Quote', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#f97316']],
            ]],
        ]);
    }

    private function createLogisticsTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'swiftmove-logistics'], [
            'name' => 'SwiftMove Logistics', 'description' => 'Modern logistics and transport template with fleet showcase, tracking, and delivery services.',
            'industry' => 'logistics', 'thumbnail' => 'https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?w=800&q=80',
            'is_premium' => false, 'is_active' => true, 'sort_order' => 16,
            'theme' => ['primaryColor' => '#2563eb', 'secondaryColor' => '#0f172a', 'accentColor' => '#3b82f6'],
            'settings' => ['navigation' => ['logoText' => 'SwiftMove', 'sticky' => true, 'showCta' => true, 'ctaText' => 'Get Quote', 'ctaLink' => '/contact'], 'footer' => ['copyrightText' => '© 2024 SwiftMove Logistics. All rights reserved.']],
        ]);
        $template->pages()->delete();
        SiteTemplatePage::create(['site_template_id' => $template->id, 'title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'show_in_nav' => true, 'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Fast, Reliable Delivery Across Zambia', 'subtitle' => 'Professional logistics and transport services. Same-day delivery in Lusaka. Nationwide coverage.', 'buttonText' => 'Track Shipment', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Our Services', 'secondaryButtonLink' => '/services', 'backgroundImage' => 'https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?w=1200&q=80', 'overlayColor' => 'blue', 'overlayOpacity' => 60], 'style' => ['minHeight' => 650]],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Our Services', 'items' => [['title' => 'Same-Day Delivery', 'description' => 'Within Lusaka'], ['title' => 'Nationwide Shipping', 'description' => 'All provinces covered'], ['title' => 'Warehousing', 'description' => 'Secure storage'], ['title' => 'Freight Services', 'description' => 'Heavy cargo'], ['title' => 'Express Courier', 'description' => 'Documents & parcels'], ['title' => 'E-commerce Fulfillment', 'description' => 'Online shop logistics']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'checklist', 'title' => 'Why Choose SwiftMove', 'items' => [['title' => 'Real-time Tracking', 'description' => 'Track your shipment online'], ['title' => 'Insured Deliveries', 'description' => 'Full insurance coverage'], ['title' => 'Flexible Pricing', 'description' => 'Competitive rates'], ['title' => 'Professional Drivers', 'description' => 'Trained and vetted'], ['title' => 'Modern Fleet', 'description' => 'Well-maintained vehicles'], ['title' => '24/7 Support', 'description' => 'Always available']]], 'style' => ['backgroundColor' => '#eff6ff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Need to Ship Something?', 'description' => 'Get instant quote online.', 'buttonText' => 'Get Quote', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#2563eb']],
            ]],
        ]);
    }

    private function createAccountingTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'probooks-accounting'], [
            'name' => 'ProBooks Accounting', 'description' => 'Professional accounting and bookkeeping template with services, tax filing, and consultation booking.',
            'industry' => 'accounting', 'thumbnail' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&q=80',
            'is_premium' => false, 'is_active' => true, 'sort_order' => 17,
            'theme' => ['primaryColor' => '#0891b2', 'secondaryColor' => '#0f172a', 'accentColor' => '#06b6d4'],
            'settings' => ['navigation' => ['logoText' => 'ProBooks', 'sticky' => true, 'showCta' => true, 'ctaText' => 'Free Consultation', 'ctaLink' => '/contact'], 'footer' => ['copyrightText' => '© 2024 ProBooks Accounting. All rights reserved.']],
        ]);
        $template->pages()->delete();
        SiteTemplatePage::create(['site_template_id' => $template->id, 'title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'show_in_nav' => true, 'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'split-right', 'title' => 'Expert Accounting Services for Your Business', 'subtitle' => 'Professional bookkeeping, tax filing, payroll, and financial consulting. ZRA compliant. Affordable rates.', 'buttonText' => 'Our Services', 'buttonLink' => '/services', 'secondaryButtonText' => 'Book Consultation', 'secondaryButtonLink' => '/contact', 'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&q=80'], 'style' => ['backgroundColor' => '#0f172a', 'minHeight' => 600]],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Our Services', 'items' => [['title' => 'Bookkeeping', 'description' => 'Daily transaction recording'], ['title' => 'Tax Filing', 'description' => 'ZRA returns and compliance'], ['title' => 'Payroll Management', 'description' => 'Salary processing and NAPSA'], ['title' => 'Financial Statements', 'description' => 'Monthly reports'], ['title' => 'Business Registration', 'description' => 'PACRA and ZRA'], ['title' => 'Audit Support', 'description' => 'Preparation and assistance']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Pricing Packages', 'plans' => [['name' => 'Starter', 'price' => 'K800/mo', 'features' => ['Basic bookkeeping', 'Monthly reports', 'Email support']], ['name' => 'Business', 'price' => 'K1,500/mo', 'popular' => true, 'features' => ['Full bookkeeping', 'Tax filing', 'Payroll (up to 10)', 'Phone support']], ['name' => 'Enterprise', 'price' => 'Custom', 'features' => ['Dedicated accountant', 'Unlimited support', 'Financial consulting', 'Audit support']]]], 'style' => ['backgroundColor' => '#ecfeff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Focus on Your Business, We\'ll Handle the Numbers', 'description' => 'Free consultation for new clients.', 'buttonText' => 'Book Now', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#0891b2']],
            ]],
        ]);
    }


    private function createEventPlanningTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'elegance-events'], [
            'name' => 'Elegance Events', 'description' => 'Stunning event planning template with portfolio, packages, and vendor directory for weddings and corporate events.',
            'industry' => 'events', 'thumbnail' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800&q=80',
            'is_premium' => false, 'is_active' => true, 'sort_order' => 18,
            'theme' => ['primaryColor' => '#db2777', 'secondaryColor' => '#1f2937', 'accentColor' => '#ec4899'],
            'settings' => ['navigation' => ['logoText' => 'Elegance Events', 'sticky' => true, 'showCta' => true, 'ctaText' => 'Plan Event', 'ctaLink' => '/contact'], 'footer' => ['copyrightText' => '© 2024 Elegance Events. All rights reserved.']],
        ]);
        $template->pages()->delete();
        SiteTemplatePage::create(['site_template_id' => $template->id, 'title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'show_in_nav' => true, 'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Creating Unforgettable Moments', 'subtitle' => 'Professional event planning for weddings, corporate events, and celebrations. Stress-free planning, flawless execution.', 'buttonText' => 'View Portfolio', 'buttonLink' => '/portfolio', 'secondaryButtonText' => 'Plan Your Event', 'secondaryButtonLink' => '/contact', 'backgroundImage' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=1920&q=80', 'overlayColor' => 'gradient', 'overlayGradientFrom' => '#db2777', 'overlayGradientTo' => '#be185d', 'overlayOpacity' => 60], 'style' => ['minHeight' => 700]],
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'Our Services', 'items' => [['title' => 'Weddings', 'description' => 'From intimate ceremonies to grand celebrations', 'image' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&q=80'], ['title' => 'Corporate Events', 'description' => 'Conferences, launches, team building', 'image' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=600&q=80'], ['title' => 'Private Parties', 'description' => 'Birthdays, anniversaries, celebrations', 'image' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'Our Work', 'images' => [['url' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1478146896981-b80fe463b330?w=600&q=80']]], 'style' => ['backgroundColor' => '#fdf2f8']],
                ['type' => 'testimonials', 'content' => ['layout' => 'single', 'title' => 'Happy Clients', 'items' => [['name' => 'Sarah & John', 'text' => 'Elegance Events made our wedding day perfect! Every detail was flawless. Highly recommended!', 'role' => 'Wedding Clients', 'rating' => 5]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Let\'s Plan Your Perfect Event', 'description' => 'Free consultation and quote.', 'buttonText' => 'Get Started', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#db2777']],
            ]],
        ]);
    }

    private function createAgricultureTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'greenfarm-agribusiness'], [
            'name' => 'GreenFarm Agribusiness', 'description' => 'Modern agriculture template with products, farm tours, wholesale orders, and farming tips.',
            'industry' => 'agriculture', 'thumbnail' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&q=80',
            'is_premium' => false, 'is_active' => true, 'sort_order' => 19,
            'theme' => ['primaryColor' => '#16a34a', 'secondaryColor' => '#1c1917', 'accentColor' => '#22c55e'],
            'settings' => ['navigation' => ['logoText' => 'GreenFarm', 'sticky' => true, 'showCta' => true, 'ctaText' => 'Order Now', 'ctaLink' => '/contact'], 'footer' => ['copyrightText' => '© 2024 GreenFarm Agribusiness. All rights reserved.']],
        ]);
        $template->pages()->delete();
        SiteTemplatePage::create(['site_template_id' => $template->id, 'title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'show_in_nav' => true, 'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'split-right', 'title' => 'Fresh from Our Farm to Your Table', 'subtitle' => 'Organic vegetables, free-range poultry, and quality produce. Wholesale and retail. Farm tours available.', 'buttonText' => 'Our Products', 'buttonLink' => '/products', 'secondaryButtonText' => 'Visit Farm', 'secondaryButtonLink' => '/contact', 'image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&q=80'], 'style' => ['backgroundColor' => '#1c1917', 'minHeight' => 600]],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'What We Grow', 'items' => [['title' => 'Vegetables', 'description' => 'Tomatoes, cabbage, rape, onions'], ['title' => 'Poultry', 'description' => 'Free-range chickens and eggs'], ['title' => 'Maize', 'description' => 'White and yellow maize'], ['title' => 'Fruits', 'description' => 'Bananas, oranges, mangoes'], ['title' => 'Livestock', 'description' => 'Cattle, goats, pigs'], ['title' => 'Honey', 'description' => 'Pure natural honey']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why Choose GreenFarm', 'items' => [['title' => 'Organic Farming', 'description' => 'No harmful chemicals'], ['title' => 'Fresh Daily', 'description' => 'Harvested fresh'], ['title' => 'Fair Prices', 'description' => 'Direct from farm'], ['title' => 'Bulk Orders', 'description' => 'Wholesale available'], ['title' => 'Delivery', 'description' => 'Free delivery in Lusaka'], ['title' => 'Farm Tours', 'description' => 'Educational visits']]], 'style' => ['backgroundColor' => '#f0fdf4']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Order Fresh Produce Today', 'description' => 'Wholesale and retail orders welcome.', 'buttonText' => 'Place Order', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#16a34a']],
            ]],
        ]);
    }

    private function createCarDealershipTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'premium-motors'], [
            'name' => 'Premium Motors', 'description' => 'Professional car dealership template with inventory showcase, financing options, and trade-in services.',
            'industry' => 'carsales', 'thumbnail' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800&q=80',
            'is_premium' => false, 'is_active' => true, 'sort_order' => 20,
            'theme' => ['primaryColor' => '#1e40af', 'secondaryColor' => '#0f172a', 'accentColor' => '#3b82f6'],
            'settings' => ['navigation' => ['logoText' => 'Premium Motors', 'sticky' => true, 'showCta' => true, 'ctaText' => 'View Inventory', 'ctaLink' => '/inventory'], 'footer' => ['copyrightText' => '© 2024 Premium Motors. All rights reserved.']],
        ]);
        $template->pages()->delete();
        SiteTemplatePage::create(['site_template_id' => $template->id, 'title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'show_in_nav' => true, 'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Find Your Dream Car Today', 'subtitle' => 'Quality pre-owned and new vehicles. Financing available. Trade-ins welcome. Trusted dealership since 2005.', 'buttonText' => 'Browse Cars', 'buttonLink' => '/inventory', 'secondaryButtonText' => 'Get Financing', 'secondaryButtonLink' => '/contact', 'backgroundImage' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=1200&q=80', 'overlayColor' => 'black', 'overlayOpacity' => 50], 'style' => ['minHeight' => 650]],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Featured Vehicles', 'columns' => 3, 'images' => [['url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=600&q=80', 'caption' => 'Toyota Hilux 2020 - K280,000'], ['url' => 'https://images.unsplash.com/photo-1542362567-b07e54358753?w=600&q=80', 'caption' => 'Honda Fit 2019 - K95,000'], ['url' => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80', 'caption' => 'Nissan X-Trail 2021 - K185,000']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why Buy From Us', 'items' => [['title' => 'Quality Assured', 'description' => 'Inspected and certified'], ['title' => 'Financing Available', 'description' => 'Flexible payment plans'], ['title' => 'Trade-Ins', 'description' => 'Best value for your car'], ['title' => 'Warranty', 'description' => '6-month warranty'], ['title' => 'After-Sales Service', 'description' => 'Maintenance support'], ['title' => 'Test Drive', 'description' => 'Try before you buy']]], 'style' => ['backgroundColor' => '#eff6ff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Ready to Drive Home Your New Car?', 'description' => 'Visit our showroom or browse online.', 'buttonText' => 'View Inventory', 'buttonLink' => '/inventory'], 'style' => ['backgroundColor' => '#1e40af']],
            ]],
        ]);
    }

    private function createBarbershopTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(['slug' => 'kings-cuts-barbershop'], [
            'name' => 'King\'s Cuts Barbershop', 'description' => 'Modern barbershop template with services, booking, barber profiles, and grooming products.',
            'industry' => 'barbershop', 'thumbnail' => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=800&q=80',
            'is_premium' => false, 'is_active' => true, 'sort_order' => 21,
            'theme' => ['primaryColor' => '#0f172a', 'secondaryColor' => '#18181b', 'accentColor' => '#f59e0b'],
            'settings' => ['navigation' => ['logoText' => 'King\'s Cuts', 'sticky' => true, 'showCta' => true, 'ctaText' => 'Book Now', 'ctaLink' => '/contact'], 'footer' => ['copyrightText' => '© 2024 King\'s Cuts Barbershop. All rights reserved.']],
        ]);
        $template->pages()->delete();
        SiteTemplatePage::create(['site_template_id' => $template->id, 'title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'show_in_nav' => true, 'sort_order' => 1,
            'content' => ['sections' => [
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Where Kings Get Their Cuts', 'subtitle' => 'Premium barbering services for the modern gentleman. Expert barbers, fresh fades, classic cuts.', 'buttonText' => 'Book Appointment', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Our Services', 'secondaryButtonLink' => '/services', 'backgroundImage' => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=1200&q=80', 'overlayColor' => 'black', 'overlayOpacity' => 60], 'style' => ['minHeight' => 650]],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Our Services', 'items' => [['title' => 'Haircut', 'description' => 'K50 - Classic and modern styles'], ['title' => 'Fade', 'description' => 'K60 - Low, mid, high fades'], ['title' => 'Beard Trim', 'description' => 'K30 - Shape and style'], ['title' => 'Hot Towel Shave', 'description' => 'K80 - Traditional shave'], ['title' => 'Hair Color', 'description' => 'K100 - Professional coloring'], ['title' => 'Kids Cut', 'description' => 'K40 - Patient with children']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'team', 'content' => ['layout' => 'grid', 'title' => 'Our Barbers', 'items' => [['name' => 'Master Jay', 'role' => 'Head Barber', 'bio' => '15 years experience', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80'], ['name' => 'Chanda', 'role' => 'Senior Barber', 'bio' => 'Fade specialist', 'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80']]], 'style' => ['backgroundColor' => '#fafaf9']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why King\'s Cuts', 'items' => [['title' => 'Expert Barbers', 'description' => 'Trained professionals'], ['title' => 'Walk-ins Welcome', 'description' => 'No appointment needed'], ['title' => 'Premium Products', 'description' => 'Quality grooming products'], ['title' => 'Clean Environment', 'description' => 'Sanitized tools'], ['title' => 'Free WiFi', 'description' => 'Stay connected'], ['title' => 'Loyalty Rewards', 'description' => 'Every 10th cut free']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Look Sharp, Feel Confident', 'description' => 'Book your appointment today.', 'buttonText' => 'Book Now', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#0f172a']],
            ]],
        ]);
    }
}
