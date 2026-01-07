<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use Illuminate\Database\Seeder;

class AdditionalSiteTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $this->createRealEstateTemplate();
        $this->createEducationTemplate();
        $this->createEcommerceTemplate();
        $this->createMedicalTemplate();
        $this->createPhotographyTemplate();
        $this->createPrintingTemplate();
    }

    private function createRealEstateTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'prime-properties'],
            [
                'name' => 'Prime Properties',
                'description' => 'Professional real estate template with property listings, agent profiles, and search functionality.',
                'industry' => 'realestate',
                'thumbnail' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 6,
                'theme' => [
                    'primaryColor' => '#0369a1',
                    'secondaryColor' => '#0f172a',
                    'accentColor' => '#0ea5e9',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'Prime Properties',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'List Property',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Prime Properties. All rights reserved.',
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
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Find Your Dream Home in Zambia', 'subtitle' => 'Browse thousands of properties across Lusaka, Kitwe, Ndola, and beyond. Your perfect home awaits.', 'buttonText' => 'Search Properties', 'buttonLink' => '/properties', 'secondaryButtonText' => 'Sell With Us', 'secondaryButtonLink' => '/contact', 'backgroundImage' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1200&q=80', 'overlayColor' => 'gradient', 'overlayGradientFrom' => '#0369a1', 'overlayGradientTo' => '#0c4a6e', 'overlayOpacity' => 70], 'style' => ['minHeight' => 650]],
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [['value' => '2,500+', 'label' => 'Properties Listed'], ['value' => 'K850M+', 'label' => 'Property Value'], ['value' => '98%', 'label' => 'Client Satisfaction'], ['value' => '15+', 'label' => 'Years in Business']]], 'style' => ['backgroundColor' => '#0369a1']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Our Services', 'subtitle' => 'Comprehensive real estate solutions', 'items' => [['title' => 'Buy Property', 'description' => 'Find your perfect home from our extensive listings across Zambia.', 'icon' => 'home'], ['title' => 'Sell Property', 'description' => 'Get the best price with our expert marketing and negotiation.', 'icon' => 'currency'], ['title' => 'Rent Property', 'description' => 'Quality rental properties for every budget and lifestyle.', 'icon' => 'key'], ['title' => 'Property Management', 'description' => 'Full-service management for landlords and investors.', 'icon' => 'cog'], ['title' => 'Valuation', 'description' => 'Professional property valuations by certified experts.', 'icon' => 'chart'], ['title' => 'Legal Services', 'description' => 'Complete legal support for all property transactions.', 'icon' => 'document']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Featured Properties', 'columns' => 3, 'images' => [['url' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&q=80', 'caption' => '4 Bed Villa - K2.5M'], ['url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=80', 'caption' => '3 Bed Apartment - K850K'], ['url' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600&q=80', 'caption' => '5 Bed Mansion - K4.2M']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'testimonials', 'content' => ['layout' => 'grid', 'title' => 'What Our Clients Say', 'items' => [['name' => 'John Phiri', 'text' => 'Prime Properties helped us find our dream home in Kabulonga. Professional service from start to finish!', 'role' => 'Homeowner', 'rating' => 5], ['name' => 'Sarah Mwanza', 'text' => 'Sold my property in just 3 weeks at asking price. Their marketing is exceptional.', 'role' => 'Property Seller', 'rating' => 5]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Ready to Find Your Perfect Property?', 'description' => 'Let our expert agents guide you through every step of your property journey.', 'buttonText' => 'Get Started', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#0369a1']],
            ]],
        ]);


        // Properties Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Properties',
            'slug' => 'properties',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Browse Properties', 'subtitle' => 'Find your perfect home', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Available Properties', 'columns' => 3, 'images' => [['url' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&q=80', 'caption' => '4 Bed Villa, Kabulonga - K2.5M'], ['url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=80', 'caption' => '3 Bed Apartment, Woodlands - K850K'], ['url' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600&q=80', 'caption' => '5 Bed Mansion, Roma - K4.2M'], ['url' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=600&q=80', 'caption' => '2 Bed Flat, CBD - K450K'], ['url' => 'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?w=600&q=80', 'caption' => '4 Bed House, Meanwood - K1.8M'], ['url' => 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=600&q=80', 'caption' => '3 Bed Townhouse, Chelston - K1.2M']]], 'style' => ['backgroundColor' => '#ffffff']],
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
                ['type' => 'page-header', 'content' => ['title' => 'About Us', 'subtitle' => 'Zambia\'s trusted property partner', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Our Story', 'description' => 'Founded in 2009, Prime Properties has grown to become one of Zambia\'s most trusted real estate agencies. We\'ve helped thousands of families find their dream homes and investors build their portfolios.', 'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&q=80', 'features' => ['Over 2,500 successful transactions', 'Licensed and certified agents', 'Comprehensive market knowledge', 'Transparent, honest service']], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'team', 'content' => ['layout' => 'social', 'title' => 'Meet Our Team', 'items' => [['name' => 'David Mulenga', 'role' => 'Managing Director', 'bio' => '20+ years in real estate', 'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80'], ['name' => 'Grace Banda', 'role' => 'Sales Director', 'bio' => 'Top agent 5 years running', 'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&q=80'], ['name' => 'Peter Phiri', 'role' => 'Property Manager', 'bio' => 'Expert in property management', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80']]], 'style' => ['backgroundColor' => '#f8fafc']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Contact Us', 'subtitle' => 'Let\'s find your perfect property', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Get in Touch', 'description' => 'Our agents are ready to help you find your dream property.', 'showForm' => true, 'email' => 'info@primeproperties.co.zm', 'phone' => '+260 21 123 4567', 'address' => 'Plot 456, Cairo Road, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);
    }


    private function createEducationTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'bright-minds-academy'],
            [
                'name' => 'Bright Minds Academy',
                'description' => 'Modern education template for schools, colleges, and training centers with course listings and enrollment.',
                'industry' => 'education',
                'thumbnail' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 7,
                'theme' => [
                    'primaryColor' => '#7c3aed',
                    'secondaryColor' => '#1e293b',
                    'accentColor' => '#a78bfa',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'Bright Minds',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Enroll Now',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Bright Minds Academy. All rights reserved.',
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
                ['type' => 'hero', 'content' => ['layout' => 'split-right', 'title' => 'Empowering Tomorrow\'s Leaders', 'subtitle' => 'Quality education that prepares students for success in a rapidly changing world. Join Zambia\'s premier learning institution.', 'buttonText' => 'Apply Now', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Our Programs', 'secondaryButtonLink' => '/programs', 'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=800&q=80'], 'style' => ['backgroundColor' => '#1e293b', 'minHeight' => 600]],
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [['value' => '5,000+', 'label' => 'Students'], ['value' => '98%', 'label' => 'Pass Rate'], ['value' => '150+', 'label' => 'Expert Teachers'], ['value' => '25+', 'label' => 'Years Excellence']]], 'style' => ['backgroundColor' => '#7c3aed']],
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'Our Programs', 'subtitle' => 'Comprehensive education from early years to professional development', 'items' => [['title' => 'Primary Education', 'description' => 'Strong foundation in core subjects with focus on critical thinking and creativity.', 'image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600&q=80'], ['title' => 'Secondary Education', 'description' => 'Cambridge curriculum preparing students for university and beyond.', 'image' => 'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?w=600&q=80'], ['title' => 'Professional Courses', 'description' => 'Industry-relevant skills training for career advancement.', 'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why Choose Bright Minds?', 'items' => [['title' => 'Qualified Teachers', 'description' => 'Experienced educators with international certifications'], ['title' => 'Modern Facilities', 'description' => 'State-of-the-art classrooms and laboratories'], ['title' => 'Small Class Sizes', 'description' => 'Personalized attention for every student'], ['title' => 'Extracurricular', 'description' => 'Sports, arts, and leadership programs'], ['title' => 'Technology Integration', 'description' => 'Digital learning tools and resources'], ['title' => 'Career Guidance', 'description' => 'University placement and career counseling']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'testimonials', 'content' => ['layout' => 'carousel', 'title' => 'Parent & Student Testimonials', 'items' => [['name' => 'Mrs. Mwanza', 'text' => 'My daughter has thrived at Bright Minds. The teachers genuinely care about each child\'s success.', 'role' => 'Parent', 'rating' => 5], ['name' => 'Chanda Phiri', 'text' => 'The best decision I made was joining Bright Minds. I got into my dream university!', 'role' => 'Former Student', 'rating' => 5]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Enroll Your Child Today', 'description' => 'Limited spaces available for the 2025 academic year. Apply now!', 'buttonText' => 'Start Application', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#7c3aed']],
            ]],
        ]);


        // Programs Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Programs',
            'slug' => 'programs',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Programs', 'subtitle' => 'Excellence in education at every level', 'backgroundColor' => '#1e293b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Primary School', 'plans' => [['name' => 'Grade 1-3', 'price' => 'K3,500/term', 'features' => ['Core subjects', 'Art & Music', 'Physical Education', 'School meals', 'Transport available']], ['name' => 'Grade 4-7', 'price' => 'K4,200/term', 'popular' => true, 'features' => ['All Grade 1-3 benefits', 'Computer studies', 'Science lab', 'Library access', 'After-school clubs']], ['name' => 'Full Package', 'price' => 'K12,000/year', 'features' => ['Save K1,500 annually', 'All term benefits', 'Priority enrollment', 'Free uniform', 'Extra tutoring']]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Secondary School', 'plans' => [['name' => 'Grade 8-9', 'price' => 'K5,500/term', 'features' => ['Cambridge curriculum', 'Science & IT labs', 'Career guidance', 'University prep']], ['name' => 'Grade 10-12', 'price' => 'K6,500/term', 'popular' => true, 'features' => ['IGCSE preparation', 'Advanced subjects', 'Mock exams', 'University applications']], ['name' => 'Professional Courses', 'price' => 'From K2,000', 'features' => ['Accounting', 'IT & Programming', 'Business Management', 'Flexible schedules']]]], 'style' => ['backgroundColor' => '#f8fafc']],
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
                ['type' => 'page-header', 'content' => ['title' => 'About Us', 'subtitle' => '25 years of educational excellence', 'backgroundColor' => '#1e293b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'about', 'content' => ['layout' => 'image-left', 'title' => 'Our Mission', 'description' => 'At Bright Minds Academy, we believe every child deserves access to world-class education. Since 1999, we\'ve been nurturing young minds and preparing them for success in an increasingly complex world.', 'image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=800&q=80', 'features' => ['Cambridge International School', 'Fully accredited by Ministry of Education', 'Award-winning teaching staff', 'Modern campus with latest facilities']], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'team', 'content' => ['layout' => 'grid', 'title' => 'Leadership Team', 'items' => [['name' => 'Dr. Mary Mwamba', 'role' => 'Principal', 'bio' => 'PhD in Education, 30 years experience', 'image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&q=80'], ['name' => 'Mr. John Phiri', 'role' => 'Deputy Principal', 'bio' => 'Cambridge certified educator', 'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80']]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Admissions',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Admissions', 'subtitle' => 'Join our learning community', 'backgroundColor' => '#1e293b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Apply Now', 'description' => 'Start your child\'s journey to excellence. Fill out the form or visit our campus.', 'showForm' => true, 'email' => 'admissions@brightminds.co.zm', 'phone' => '+260 21 234 5678', 'address' => 'Plot 789, Great East Road, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'faq', 'content' => ['layout' => 'accordion', 'title' => 'Admission FAQs', 'items' => [['question' => 'What is the admission process?', 'answer' => 'Submit application form, attend interview, take placement test, receive admission decision within 2 weeks.'], ['question' => 'When does the school year start?', 'answer' => 'Academic year runs from January to December with 3 terms.'], ['question' => 'Do you offer scholarships?', 'answer' => 'Yes, merit-based scholarships available for exceptional students. Apply early!']]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);
    }


    private function createEcommerceTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'zambia-shop'],
            [
                'name' => 'Zambia Shop',
                'description' => 'Modern e-commerce template for online stores with product showcase, cart, and mobile money integration.',
                'industry' => 'ecommerce',
                'thumbnail' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 8,
                'theme' => [
                    'primaryColor' => '#059669',
                    'secondaryColor' => '#0f172a',
                    'accentColor' => '#10b981',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'Zambia Shop',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Shop Now',
                        'ctaLink' => '/shop',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Zambia Shop. All rights reserved.',
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
                ['type' => 'hero', 'content' => ['layout' => 'split-left', 'title' => 'Shop Smart. Shop Local.', 'subtitle' => 'Discover quality products from Zambian businesses. Fast delivery across Lusaka. Pay with MTN or Airtel Money.', 'buttonText' => 'Browse Products', 'buttonLink' => '/shop', 'secondaryButtonText' => 'Today\'s Deals', 'secondaryButtonLink' => '/shop', 'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&q=80'], 'style' => ['backgroundColor' => '#0f172a', 'minHeight' => 600]],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why Shop With Us', 'items' => [['title' => 'Free Delivery', 'description' => 'On orders over K500 within Lusaka', 'icon' => 'truck'], ['title' => 'Secure Payment', 'description' => 'MTN MoMo & Airtel Money accepted', 'icon' => 'shield'], ['title' => 'Quality Guaranteed', 'description' => '30-day return policy on all items', 'icon' => 'check'], ['title' => 'Local Support', 'description' => 'Zambian customer service team', 'icon' => 'phone']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Featured Products', 'columns' => 4, 'images' => [['url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80', 'caption' => 'Smart Watch - K450'], ['url' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&q=80', 'caption' => 'Sunglasses - K120'], ['url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80', 'caption' => 'Headphones - K350'], ['url' => 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=400&q=80', 'caption' => 'Sneakers - K550']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Shop by Category', 'items' => [['title' => 'Electronics', 'description' => 'Phones, laptops, accessories', 'icon' => 'device'], ['title' => 'Fashion', 'description' => 'Clothing, shoes, accessories', 'icon' => 'sparkles'], ['title' => 'Home & Living', 'description' => 'Furniture, decor, appliances', 'icon' => 'home'], ['title' => 'Beauty', 'description' => 'Cosmetics, skincare, fragrances', 'icon' => 'heart'], ['title' => 'Sports', 'description' => 'Fitness gear, equipment', 'icon' => 'star'], ['title' => 'Books', 'description' => 'Educational, fiction, magazines', 'icon' => 'book']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'testimonials', 'content' => ['layout' => 'grid', 'title' => 'Customer Reviews', 'items' => [['name' => 'Chanda M.', 'text' => 'Fast delivery and great prices! My go-to online shop in Zambia.', 'rating' => 5], ['name' => 'Peter K.', 'text' => 'Love the mobile money payment option. So convenient!', 'rating' => 5], ['name' => 'Grace B.', 'text' => 'Quality products and excellent customer service. Highly recommend!', 'rating' => 5]]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Get 10% Off Your First Order', 'description' => 'Sign up for our newsletter and receive exclusive deals.', 'buttonText' => 'Subscribe Now', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#059669']],
            ]],
        ]);

        // Shop Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Shop',
            'slug' => 'shop',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'All Products', 'subtitle' => 'Browse our full collection', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Latest Arrivals', 'columns' => 4, 'images' => [['url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80', 'caption' => 'Smart Watch - K450'], ['url' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&q=80', 'caption' => 'Sunglasses - K120'], ['url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80', 'caption' => 'Headphones - K350'], ['url' => 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=400&q=80', 'caption' => 'Sneakers - K550'], ['url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&q=80', 'caption' => 'Running Shoes - K480'], ['url' => 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=400&q=80', 'caption' => 'Backpack - K280'], ['url' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=400&q=80', 'caption' => 'Laptop - K4,500'], ['url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&q=80', 'caption' => 'Phone - K2,800']]], 'style' => ['backgroundColor' => '#ffffff']],
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
                ['type' => 'page-header', 'content' => ['title' => 'About Us', 'subtitle' => 'Your trusted online marketplace', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Our Story', 'description' => 'Zambia Shop was founded to make online shopping accessible to every Zambian. We partner with local businesses to bring you quality products at competitive prices, delivered right to your door.', 'image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&q=80', 'features' => ['Over 10,000 products available', 'Same-day delivery in Lusaka', 'Secure mobile money payments', '100% authentic products']], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'stats', 'content' => ['layout' => 'grid', 'items' => [['value' => '50,000+', 'label' => 'Happy Customers'], ['value' => '10,000+', 'label' => 'Products'], ['value' => '500+', 'label' => 'Daily Orders'], ['value' => '4.8/5', 'label' => 'Customer Rating']]], 'style' => ['backgroundColor' => '#059669']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Customer Support', 'subtitle' => 'We\'re here to help', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Get in Touch', 'description' => 'Have questions about your order? Our support team is ready to assist.', 'showForm' => true, 'email' => 'support@zambiashop.co.zm', 'phone' => '+260 97 999 0000', 'whatsapp' => '+260 97 999 0000', 'address' => 'Plot 321, Lumumba Road, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Support Hours', 'items' => [['title' => 'Phone Support', 'description' => 'Mon-Sat: 8AM - 8PM'], ['title' => 'WhatsApp', 'description' => '24/7 automated responses'], ['title' => 'Email', 'description' => 'Response within 24 hours'], ['title' => 'Live Chat', 'description' => 'Mon-Fri: 9AM - 5PM']]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);
    }


    private function createMedicalTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'careplus-clinic'],
            [
                'name' => 'CarePlus Clinic',
                'description' => 'Professional healthcare template for clinics, hospitals, and medical practices with appointment booking.',
                'industry' => 'medical',
                'thumbnail' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 9,
                'theme' => [
                    'primaryColor' => '#0891b2',
                    'secondaryColor' => '#0f172a',
                    'accentColor' => '#06b6d4',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'CarePlus',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Book Appointment',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 CarePlus Medical Clinic. All rights reserved.',
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
                ['type' => 'hero', 'content' => ['layout' => 'split-right', 'title' => 'Your Health, Our Priority', 'subtitle' => 'Comprehensive medical care from experienced doctors. Modern facilities, compassionate service, and affordable rates.', 'buttonText' => 'Book Appointment', 'buttonLink' => '/contact', 'secondaryButtonText' => 'Our Services', 'secondaryButtonLink' => '/services', 'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800&q=80'], 'style' => ['backgroundColor' => '#0f172a', 'minHeight' => 600]],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why Choose CarePlus', 'items' => [['title' => '24/7 Emergency', 'description' => 'Round-the-clock emergency services', 'icon' => 'clock'], ['title' => 'Expert Doctors', 'description' => 'Qualified specialists in all fields', 'icon' => 'star'], ['title' => 'Modern Equipment', 'description' => 'Latest diagnostic technology', 'icon' => 'cog'], ['title' => 'Insurance Accepted', 'description' => 'All major medical schemes', 'icon' => 'shield']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'Our Services', 'subtitle' => 'Comprehensive healthcare under one roof', 'items' => [['title' => 'General Practice', 'description' => 'Routine check-ups, vaccinations, and primary care for all ages.', 'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=600&q=80'], ['title' => 'Specialist Care', 'description' => 'Cardiology, pediatrics, gynecology, and more specialist services.', 'image' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=600&q=80'], ['title' => 'Laboratory', 'description' => 'Full diagnostic lab with fast, accurate results.', 'image' => 'https://images.unsplash.com/photo-1582719471384-894fbb16e074?w=600&q=80']]], 'style' => ['backgroundColor' => '#f0f9ff']],
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [['value' => '15+', 'label' => 'Years Serving'], ['value' => '50,000+', 'label' => 'Patients Treated'], ['value' => '25+', 'label' => 'Medical Specialists'], ['value' => '24/7', 'label' => 'Emergency Care']]], 'style' => ['backgroundColor' => '#0891b2']],
                ['type' => 'team', 'content' => ['layout' => 'social', 'title' => 'Meet Our Doctors', 'items' => [['name' => 'Dr. John Mwamba', 'role' => 'General Practitioner', 'bio' => 'MBBS, 20 years experience', 'image' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400&q=80'], ['name' => 'Dr. Grace Phiri', 'role' => 'Pediatrician', 'bio' => 'MBBS, DCH, Child health specialist', 'image' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400&q=80'], ['name' => 'Dr. Peter Banda', 'role' => 'Cardiologist', 'bio' => 'MBBS, MD, Heart specialist', 'image' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=400&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'testimonials', 'content' => ['layout' => 'carousel', 'title' => 'Patient Testimonials', 'items' => [['name' => 'Mary Tembo', 'text' => 'The doctors at CarePlus saved my life. Professional, caring, and thorough. I trust them completely.', 'role' => 'Patient', 'rating' => 5], ['name' => 'James Mwale', 'text' => 'Best medical care in Lusaka. Clean facilities, friendly staff, and reasonable prices.', 'role' => 'Patient', 'rating' => 5]]], 'style' => ['backgroundColor' => '#f0f9ff']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Need Medical Attention?', 'description' => 'Book an appointment online or call our 24/7 emergency line.', 'buttonText' => 'Book Now', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#0891b2']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Medical Services', 'subtitle' => 'Comprehensive healthcare solutions', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'services', 'content' => ['layout' => 'alternating', 'title' => 'Our Departments', 'items' => [['title' => 'General Medicine', 'description' => 'Consultations, health screenings, chronic disease management, vaccinations, and preventive care for adults and children.', 'icon' => 'heart', 'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=600&q=80'], ['title' => 'Specialist Clinics', 'description' => 'Cardiology, dermatology, ENT, gynecology, orthopedics, and more. Expert care from qualified specialists.', 'icon' => 'star', 'image' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=600&q=80'], ['title' => 'Diagnostic Services', 'description' => 'X-ray, ultrasound, ECG, blood tests, and comprehensive laboratory services with fast turnaround.', 'icon' => 'chart', 'image' => 'https://images.unsplash.com/photo-1582719471384-894fbb16e074?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Consultation Fees', 'plans' => [['name' => 'General Consultation', 'price' => 'K150', 'features' => ['Doctor consultation', 'Basic examination', 'Prescription', 'Follow-up advice']], ['name' => 'Specialist Consultation', 'price' => 'K300', 'popular' => true, 'features' => ['Specialist doctor', 'Detailed examination', 'Treatment plan', 'Lab tests (if needed)']], ['name' => 'Health Package', 'price' => 'K800', 'features' => ['Full body check-up', 'Blood tests', 'ECG', 'Doctor consultation', 'Health report']]]], 'style' => ['backgroundColor' => '#f0f9ff']],
            ]],
        ]);

        // Doctors Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Our Doctors',
            'slug' => 'doctors',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Medical Team', 'subtitle' => 'Experienced doctors dedicated to your health', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'team', 'content' => ['layout' => 'social', 'title' => 'General Practitioners', 'items' => [['name' => 'Dr. John Mwamba', 'role' => 'General Practitioner', 'bio' => 'MBBS (UNZA), 20 years experience in family medicine', 'image' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400&q=80'], ['name' => 'Dr. Sarah Banda', 'role' => 'General Practitioner', 'bio' => 'MBBS, MPH, Preventive medicine specialist', 'image' => 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?w=400&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'team', 'content' => ['layout' => 'social', 'title' => 'Specialists', 'items' => [['name' => 'Dr. Grace Phiri', 'role' => 'Pediatrician', 'bio' => 'MBBS, DCH, Child health and development expert', 'image' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400&q=80'], ['name' => 'Dr. Peter Banda', 'role' => 'Cardiologist', 'bio' => 'MBBS, MD (Cardiology), Heart disease specialist', 'image' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=400&q=80'], ['name' => 'Dr. Chanda Mulenga', 'role' => 'Gynecologist', 'bio' => 'MBBS, MRCOG, Women\'s health specialist', 'image' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=400&q=80']]], 'style' => ['backgroundColor' => '#f0f9ff']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Book Appointment', 'subtitle' => 'Schedule your visit today', 'backgroundColor' => '#0f172a', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'with-map', 'title' => 'Get in Touch', 'description' => 'Book online or call us. Emergency? Dial our 24/7 hotline.', 'showForm' => true, 'email' => 'info@careplus.co.zm', 'phone' => '+260 21 345 6789', 'emergency' => '+260 97 111 2222', 'address' => 'Plot 567, Independence Avenue, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Clinic Hours', 'items' => [['title' => 'Weekdays', 'description' => '7:00 AM - 8:00 PM'], ['title' => 'Saturday', 'description' => '8:00 AM - 6:00 PM'], ['title' => 'Sunday', 'description' => '9:00 AM - 2:00 PM'], ['title' => 'Emergency', 'description' => '24/7 Available']]], 'style' => ['backgroundColor' => '#f0f9ff']],
            ]],
        ]);
    }


    private function createPhotographyTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'lens-studio'],
            [
                'name' => 'Lens Studio',
                'description' => 'Stunning photography portfolio template with gallery, packages, and booking for photographers and videographers.',
                'industry' => 'creative',
                'thumbnail' => 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 10,
                'theme' => [
                    'primaryColor' => '#18181b',
                    'secondaryColor' => '#09090b',
                    'accentColor' => '#f59e0b',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'LENS',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Book Session',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Lens Studio. All rights reserved.',
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
                ['type' => 'hero', 'content' => ['layout' => 'centered', 'title' => 'Capturing Moments That Last Forever', 'subtitle' => 'Professional photography and videography for weddings, events, portraits, and commercial projects across Zambia.', 'buttonText' => 'View Portfolio', 'buttonLink' => '/portfolio', 'secondaryButtonText' => 'Book Now', 'secondaryButtonLink' => '/contact', 'backgroundImage' => 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?w=1200&q=80', 'overlayColor' => 'black', 'overlayOpacity' => 50], 'style' => ['minHeight' => 700]],
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'Our Services', 'subtitle' => 'Professional photography for every occasion', 'items' => [['title' => 'Weddings', 'description' => 'Complete wedding coverage from preparation to reception. Cinematic photos and videos.', 'image' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&q=80'], ['title' => 'Portraits', 'description' => 'Professional headshots, family portraits, and personal branding photography.', 'image' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=600&q=80'], ['title' => 'Events', 'description' => 'Corporate events, parties, conferences, and special occasions captured beautifully.', 'image' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=600&q=80'], ['title' => 'Commercial', 'description' => 'Product photography, real estate, and business promotional content.', 'image' => 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'Recent Work', 'images' => [['url' => 'https://images.unsplash.com/photo-1606216794074-735e91aa2c92?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1529636798458-92182e662485?w=600&q=80']]], 'style' => ['backgroundColor' => '#18181b']],
                ['type' => 'features', 'content' => ['layout' => 'checklist', 'title' => 'Why Choose Lens Studio', 'items' => [['title' => 'Professional Equipment', 'description' => 'Latest cameras, lenses, and lighting gear'], ['title' => 'Experienced Team', 'description' => '10+ years capturing Zambian stories'], ['title' => 'Fast Delivery', 'description' => 'Edited photos within 7 days'], ['title' => 'Flexible Packages', 'description' => 'Custom packages to fit your budget'], ['title' => 'Online Gallery', 'description' => 'Private online gallery for easy sharing'], ['title' => 'Print Services', 'description' => 'High-quality prints and albums available']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'testimonials', 'content' => ['layout' => 'single', 'title' => 'Client Love', 'items' => [['name' => 'Chanda & Peter', 'text' => 'Lens Studio captured our wedding day perfectly! The photos are absolutely stunning and we\'ll treasure them forever. Professional, creative, and so easy to work with.', 'role' => 'Wedding Clients', 'rating' => 5]]], 'style' => ['backgroundColor' => '#f9fafb']],
                ['type' => 'cta', 'content' => ['layout' => 'split', 'title' => 'Ready to Create Magic?', 'description' => 'Let\'s discuss your photography needs and create something beautiful together.', 'buttonText' => 'Book Your Session', 'buttonLink' => '/contact', 'image' => 'https://images.unsplash.com/photo-1554048612-b6a482bc67e5?w=600&q=80'], 'style' => ['backgroundColor' => '#18181b']],
            ]],
        ]);

        // Portfolio Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Portfolio',
            'slug' => 'portfolio',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Portfolio', 'subtitle' => 'A glimpse of our work', 'backgroundColor' => '#09090b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'gallery', 'content' => ['layout' => 'masonry', 'title' => 'Weddings', 'images' => [['url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1606216794074-735e91aa2c92?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1529636798458-92182e662485?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Portraits', 'columns' => 4, 'images' => [['url' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=400&q=80'], ['url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80'], ['url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&q=80'], ['url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80']]], 'style' => ['backgroundColor' => '#f9fafb']],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Events', 'columns' => 3, 'images' => [['url' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1505236858219-8359eb29e329?w=600&q=80'], ['url' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);

        // Packages Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Packages',
            'slug' => 'packages',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Photography Packages', 'subtitle' => 'Flexible options for every budget', 'backgroundColor' => '#09090b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Wedding Packages', 'plans' => [['name' => 'Essential', 'price' => 'K5,000', 'features' => ['6 hours coverage', '1 photographer', '300+ edited photos', 'Online gallery', 'USB with all photos']], ['name' => 'Premium', 'price' => 'K8,500', 'popular' => true, 'features' => ['Full day coverage', '2 photographers', '500+ edited photos', 'Engagement shoot', 'Online gallery', 'USB + 20 prints']], ['name' => 'Luxury', 'price' => 'K15,000', 'features' => ['Full day + video', '2 photographers + videographer', '800+ photos + 5min film', 'Engagement shoot', 'Premium album', 'All digital files']]]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Portrait Sessions', 'plans' => [['name' => 'Individual', 'price' => 'K800', 'features' => ['1 hour session', '1 person', '20 edited photos', 'Online gallery', '3 outfit changes']], ['name' => 'Family', 'price' => 'K1,500', 'popular' => true, 'features' => ['1.5 hour session', 'Up to 6 people', '40 edited photos', 'Online gallery', 'Location of choice']], ['name' => 'Corporate', 'price' => 'K3,000', 'features' => ['Half day', 'Up to 20 people', 'Professional headshots', 'Same-day delivery', 'Commercial license']]]], 'style' => ['backgroundColor' => '#f9fafb']],
                ['type' => 'faq', 'content' => ['layout' => 'accordion', 'title' => 'Package FAQs', 'items' => [['question' => 'Can I customize a package?', 'answer' => 'Absolutely! We can create a custom package tailored to your specific needs and budget.'], ['question' => 'How long until I receive my photos?', 'answer' => 'Wedding photos are delivered within 2-3 weeks. Portrait sessions within 7 days.'], ['question' => 'Do you travel outside Lusaka?', 'answer' => 'Yes! We cover all of Zambia. Travel fees apply for locations outside Lusaka.']]], 'style' => ['backgroundColor' => '#ffffff']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Let\'s Work Together', 'subtitle' => 'Book your photography session', 'backgroundColor' => '#09090b', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Get in Touch', 'description' => 'Tell us about your project and we\'ll create something amazing together.', 'showForm' => true, 'email' => 'hello@lensstudio.co.zm', 'phone' => '+260 97 555 7777', 'whatsapp' => '+260 97 555 7777', 'address' => 'Studio 5, Manda Hill, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Studio Hours', 'items' => [['title' => 'Monday - Friday', 'description' => '9:00 AM - 6:00 PM'], ['title' => 'Saturday', 'description' => '10:00 AM - 4:00 PM'], ['title' => 'Sunday', 'description' => 'By appointment only'], ['title' => 'Shoots', 'description' => 'Flexible scheduling available']]], 'style' => ['backgroundColor' => '#f9fafb']],
            ]],
        ]);
    }

    private function createPrintingTemplate(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'printpro-zambia'],
            [
                'name' => 'PrintPro Zambia',
                'description' => 'Professional printing and branding template for print shops, signage companies, and corporate branding services.',
                'industry' => 'printing',
                'thumbnail' => 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 11,
                'theme' => [
                    'primaryColor' => '#ea580c',
                    'secondaryColor' => '#0c4a6e',
                    'accentColor' => '#fb923c',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'PrintPro',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Get Quote',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 PrintPro Zambia. All rights reserved.',
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
                ['type' => 'hero', 'content' => ['layout' => 'split-left', 'title' => 'Your One-Stop Printing & Branding Solution', 'subtitle' => 'From business cards to vehicle branding, we deliver quality printing services that make your brand stand out. Fast turnaround, competitive prices, professional results.', 'buttonText' => 'View Services', 'buttonLink' => '/services', 'secondaryButtonText' => 'Request Quote', 'secondaryButtonLink' => '/contact', 'image' => 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=800&q=80'], 'style' => ['backgroundColor' => '#0c4a6e', 'minHeight' => 600]],
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [['value' => '10,000+', 'label' => 'Projects Completed'], ['value' => '24hr', 'label' => 'Express Service'], ['value' => '500+', 'label' => 'Happy Clients'], ['value' => '15+', 'label' => 'Years Experience']]], 'style' => ['backgroundColor' => '#ea580c']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Our Services', 'subtitle' => 'Professional printing and branding solutions', 'items' => [['title' => 'Corporate Branding', 'description' => 'Complete brand identity packages including logos, business cards, letterheads, and branded materials.', 'icon' => 'briefcase'], ['title' => 'T-Shirt Printing', 'description' => 'Custom t-shirt printing for events, teams, promotions. Screen printing and heat transfer available.', 'icon' => 'star'], ['title' => 'Large Format Printing', 'description' => 'Banners, billboards, vehicle wraps, wall graphics, and outdoor signage in any size.', 'icon' => 'chart'], ['title' => 'Graphic Designing', 'description' => 'Professional design services for all your marketing materials and brand assets.', 'icon' => 'sparkles'], ['title' => 'Gift Bags & Packaging', 'description' => 'Custom branded bags, boxes, and packaging materials for retail and corporate gifts.', 'icon' => 'gift'], ['title' => 'Business Stationery', 'description' => 'Receipt books, invoice books, quotation books, delivery notes, and custom forms.', 'icon' => 'document']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'gallery', 'content' => ['layout' => 'grid', 'title' => 'Our Work', 'subtitle' => 'Quality printing that speaks for itself', 'columns' => 4, 'images' => [['url' => 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=400&q=80', 'caption' => 'Business Cards'], ['url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=400&q=80', 'caption' => 'T-Shirt Printing'], ['url' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=400&q=80', 'caption' => 'Banners'], ['url' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=400&q=80', 'caption' => 'Corporate Branding'], ['url' => 'https://images.unsplash.com/photo-1586075010923-2dd4570fb338?w=400&q=80', 'caption' => 'Vehicle Branding'], ['url' => 'https://images.unsplash.com/photo-1634128221889-82ed6efebfc3?w=400&q=80', 'caption' => 'Signage'], ['url' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=400&q=80', 'caption' => 'Gift Bags'], ['url' => 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=400&q=80', 'caption' => 'Stationery']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Why Choose PrintPro?', 'items' => [['title' => 'Fast Turnaround', 'description' => '24-hour express service available'], ['title' => 'Quality Guaranteed', 'description' => 'Premium materials and latest equipment'], ['title' => 'Competitive Prices', 'description' => 'Best rates in Zambia'], ['title' => 'Free Delivery', 'description' => 'Within Lusaka on orders over K500'], ['title' => 'Design Support', 'description' => 'Free design assistance included'], ['title' => 'Bulk Discounts', 'description' => 'Special rates for large orders']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'testimonials', 'content' => ['layout' => 'grid', 'title' => 'Client Testimonials', 'items' => [['name' => 'Mwamba Trading', 'text' => 'PrintPro handled our complete rebranding. Professional service, great quality, and delivered on time!', 'role' => 'Corporate Client', 'rating' => 5], ['name' => 'Grace Phiri', 'text' => 'Best printing shop in Lusaka! Fast, affordable, and the quality is always excellent.', 'role' => 'Small Business Owner', 'rating' => 5], ['name' => 'Zambia Youth FC', 'text' => 'They printed 200 team jerseys for us. Perfect quality and finished ahead of schedule!', 'role' => 'Sports Team', 'rating' => 5]]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Ready to Print?', 'description' => 'Get a free quote today. Fast turnaround, quality guaranteed.', 'buttonText' => 'Request Quote', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#ea580c']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Our Services', 'subtitle' => 'Complete printing and branding solutions', 'backgroundColor' => '#0c4a6e', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'services', 'content' => ['layout' => 'alternating', 'title' => 'What We Offer', 'items' => [['title' => 'Corporate Branding', 'description' => 'Complete brand identity solutions including logo design, business cards, letterheads, envelopes, folders, and all corporate stationery. We help businesses create a professional, consistent brand image across all touchpoints.', 'icon' => 'briefcase', 'image' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=600&q=80'], ['title' => 'T-Shirt & Apparel Printing', 'description' => 'Custom t-shirt printing using screen printing and heat transfer methods. Perfect for events, teams, promotions, and corporate uniforms. We print on t-shirts, polo shirts, hoodies, caps, and more.', 'icon' => 'star', 'image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=600&q=80'], ['title' => 'Large Format Printing', 'description' => 'Professional large format printing for banners, billboards, vehicle wraps, wall graphics, window graphics, and outdoor signage. Weather-resistant materials for long-lasting results.', 'icon' => 'chart', 'image' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=600&q=80'], ['title' => 'Business Stationery', 'description' => 'Custom printed receipt books, invoice books, quotation books, delivery notes, and all business forms. Available in duplicate, triplicate, or more copies with numbering.', 'icon' => 'document', 'image' => 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=600&q=80']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'pricing', 'content' => ['layout' => 'cards', 'title' => 'Popular Products', 'plans' => [['name' => 'Business Cards', 'price' => 'From K250', 'features' => ['500 cards', 'Full color', 'Matt or gloss finish', '2-3 days delivery', 'Free design tweaks']], ['name' => 'T-Shirts', 'price' => 'From K80/pc', 'popular' => true, 'features' => ['Minimum 20 pieces', 'Full color printing', 'Quality cotton', 'Bulk discounts', 'Free artwork setup']], ['name' => 'Banners', 'price' => 'From K150/sqm', 'features' => ['Any size', 'Weather resistant', 'Eyelets included', 'Same day available', 'Free design']]]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);

        // Products Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Products',
            'slug' => 'products',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Products', 'subtitle' => 'Browse our full range', 'backgroundColor' => '#0c4a6e', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Corporate Stationery', 'items' => [['title' => 'Business Cards', 'description' => 'From K250 for 500 cards'], ['title' => 'Letterheads', 'description' => 'From K180 for 100 sheets'], ['title' => 'Envelopes', 'description' => 'From K200 for 100 pieces'], ['title' => 'Folders', 'description' => 'From K15 each'], ['title' => 'Compliment Slips', 'description' => 'From K150 for 100'], ['title' => 'ID Cards', 'description' => 'From K25 each']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Business Forms', 'items' => [['title' => 'Receipt Books', 'description' => 'From K120 per book'], ['title' => 'Invoice Books', 'description' => 'From K120 per book'], ['title' => 'Quotation Books', 'description' => 'From K120 per book'], ['title' => 'Delivery Notes', 'description' => 'From K120 per book'], ['title' => 'Job Cards', 'description' => 'From K150 per book'], ['title' => 'Custom Forms', 'description' => 'Contact for quote']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Promotional Items', 'items' => [['title' => 'T-Shirts', 'description' => 'From K80 per piece'], ['title' => 'Polo Shirts', 'description' => 'From K120 per piece'], ['title' => 'Caps', 'description' => 'From K60 each'], ['title' => 'Mugs', 'description' => 'From K45 each'], ['title' => 'Pens', 'description' => 'From K15 each'], ['title' => 'USB Drives', 'description' => 'From K80 each']]], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Signage & Banners', 'items' => [['title' => 'Pull-up Banners', 'description' => 'From K450 each'], ['title' => 'Teardrop Flags', 'description' => 'From K550 each'], ['title' => 'PVC Banners', 'description' => 'From K150 per sqm'], ['title' => 'Vehicle Branding', 'description' => 'From K2,500'], ['title' => 'Shop Signs', 'description' => 'Contact for quote'], ['title' => 'Billboard Printing', 'description' => 'Contact for quote']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Need a Custom Quote?', 'description' => 'Contact us with your requirements and we\'ll provide a detailed quote.', 'buttonText' => 'Get Quote', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#ea580c']],
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
                ['type' => 'page-header', 'content' => ['title' => 'Get in Touch', 'subtitle' => 'Request a quote or visit our shop', 'backgroundColor' => '#0c4a6e', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 200]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Request a Quote', 'description' => 'Fill out the form with your requirements or call us directly for immediate assistance.', 'showForm' => true, 'email' => 'info@printpro.co.zm', 'phone' => '+260 21 123 4567', 'whatsapp' => '+260 97 888 9999', 'address' => 'Plot 234, Lumumba Road, Industrial Area, Lusaka'], 'style' => ['backgroundColor' => '#ffffff']],
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Visit Our Shop', 'items' => [['title' => 'Opening Hours', 'description' => 'Mon-Fri: 8AM-5PM, Sat: 8AM-1PM'], ['title' => 'Express Service', 'description' => '24-hour turnaround available'], ['title' => 'Free Parking', 'description' => 'Ample parking space'], ['title' => 'Free Delivery', 'description' => 'Within Lusaka on orders K500+']]], 'style' => ['backgroundColor' => '#f8fafc']],
                ['type' => 'faq', 'content' => ['layout' => 'accordion', 'title' => 'Common Questions', 'items' => [['question' => 'What is your minimum order quantity?', 'answer' => 'Most products have no minimum. T-shirts require minimum 20 pieces for custom printing.'], ['question' => 'How long does printing take?', 'answer' => 'Standard turnaround is 2-3 days. Express 24-hour service available for urgent orders.'], ['question' => 'Do you offer design services?', 'answer' => 'Yes! We offer free basic design assistance. Full graphic design services available at K200-K500 depending on complexity.'], ['question' => 'What payment methods do you accept?', 'answer' => 'Cash, bank transfer, MTN MoMo, and Airtel Money. 50% deposit required for large orders.']]], 'style' => ['backgroundColor' => '#ffffff']],
            ]],
        ]);
    }
}
