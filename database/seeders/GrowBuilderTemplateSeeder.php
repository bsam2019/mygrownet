<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use Illuminate\Database\Seeder;

class GrowBuilderTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Business Consulting',
                'slug' => 'business-consulting',
                'description' => 'Professional template for consulting firms and advisory services. Features service listings, team profiles, and contact forms.',
                'industry' => 'consulting',
                'thumbnail' => null,
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 1,
                'pages' => [
                    ['title' => 'Home', 'slug' => 'index', 'is_homepage' => true, 'show_in_nav' => true],
                    ['title' => 'Services', 'slug' => 'services', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'About Us', 'slug' => 'about', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Contact', 'slug' => 'contact', 'is_homepage' => false, 'show_in_nav' => true],
                ],
            ],
            [
                'name' => 'Restaurant & Cafe',
                'slug' => 'restaurant-cafe',
                'description' => 'Appetizing template for restaurants, cafes, and food businesses. Includes menu display, online ordering, and reservation system.',
                'industry' => 'restaurant',
                'thumbnail' => null,
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 2,
                'pages' => [
                    ['title' => 'Home', 'slug' => 'index', 'is_homepage' => true, 'show_in_nav' => true],
                    ['title' => 'Menu', 'slug' => 'menu', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Reservations', 'slug' => 'reservations', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Contact', 'slug' => 'contact', 'is_homepage' => false, 'show_in_nav' => true],
                ],
            ],
            [
                'name' => 'Hair Salon & Spa',
                'slug' => 'hair-salon-spa',
                'description' => 'Elegant template for salons, spas, and beauty services. Features service menu, booking system, and gallery.',
                'industry' => 'salon',
                'thumbnail' => null,
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 3,
                'pages' => [
                    ['title' => 'Home', 'slug' => 'index', 'is_homepage' => true, 'show_in_nav' => true],
                    ['title' => 'Services', 'slug' => 'services', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Gallery', 'slug' => 'gallery', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Book Now', 'slug' => 'booking', 'is_homepage' => false, 'show_in_nav' => true],
                ],
            ],
            [
                'name' => 'Law Firm',
                'slug' => 'law-firm',
                'description' => 'Professional template for law firms and legal services. Features practice areas, attorney profiles, and consultation booking.',
                'industry' => 'legal',
                'thumbnail' => null,
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 4,
                'pages' => [
                    ['title' => 'Home', 'slug' => 'index', 'is_homepage' => true, 'show_in_nav' => true],
                    ['title' => 'Practice Areas', 'slug' => 'practice-areas', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Our Team', 'slug' => 'team', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Contact', 'slug' => 'contact', 'is_homepage' => false, 'show_in_nav' => true],
                ],
            ],
            [
                'name' => 'Fitness & Gym',
                'slug' => 'fitness-gym',
                'description' => 'Dynamic template for gyms, fitness centers, and personal trainers. Includes class schedules, membership plans, and trainer profiles.',
                'industry' => 'fitness',
                'thumbnail' => null,
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 5,
                'pages' => [
                    ['title' => 'Home', 'slug' => 'index', 'is_homepage' => true, 'show_in_nav' => true],
                    ['title' => 'Classes', 'slug' => 'classes', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Membership', 'slug' => 'membership', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Contact', 'slug' => 'contact', 'is_homepage' => false, 'show_in_nav' => true],
                ],
            ],
            [
                'name' => 'E-Commerce Store',
                'slug' => 'ecommerce-store',
                'description' => 'Complete online store template with product catalog, shopping cart, and payment integration. Perfect for selling physical products.',
                'industry' => 'retail',
                'thumbnail' => null,
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 6,
                'pages' => [
                    ['title' => 'Home', 'slug' => 'index', 'is_homepage' => true, 'show_in_nav' => true],
                    ['title' => 'Shop', 'slug' => 'shop', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'About', 'slug' => 'about', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Contact', 'slug' => 'contact', 'is_homepage' => false, 'show_in_nav' => true],
                ],
            ],
            [
                'name' => 'Real Estate Agency',
                'slug' => 'real-estate-agency',
                'description' => 'Professional template for real estate agents and property listings. Features property search, virtual tours, and agent profiles.',
                'industry' => 'real-estate',
                'thumbnail' => null,
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 7,
                'pages' => [
                    ['title' => 'Home', 'slug' => 'index', 'is_homepage' => true, 'show_in_nav' => true],
                    ['title' => 'Properties', 'slug' => 'properties', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Agents', 'slug' => 'agents', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Contact', 'slug' => 'contact', 'is_homepage' => false, 'show_in_nav' => true],
                ],
            ],
            [
                'name' => 'Medical Clinic',
                'slug' => 'medical-clinic',
                'description' => 'Clean, trustworthy template for medical clinics and healthcare providers. Features doctor profiles, services, and appointment booking.',
                'industry' => 'healthcare',
                'thumbnail' => null,
                'is_premium' => true,
                'is_active' => true,
                'sort_order' => 8,
                'pages' => [
                    ['title' => 'Home', 'slug' => 'index', 'is_homepage' => true, 'show_in_nav' => true],
                    ['title' => 'Services', 'slug' => 'services', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Our Doctors', 'slug' => 'doctors', 'is_homepage' => false, 'show_in_nav' => true],
                    ['title' => 'Appointments', 'slug' => 'appointments', 'is_homepage' => false, 'show_in_nav' => true],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            $pages = $templateData['pages'];
            unset($templateData['pages']);

            $template = SiteTemplate::create($templateData);

            foreach ($pages as $index => $pageData) {
                $pageData['site_template_id'] = $template->id;
                $pageData['sort_order'] = $index + 1;
                $pageData['content'] = $this->getDefaultPageContent($pageData['slug']);
                SiteTemplatePage::create($pageData);
            }
        }
    }

    private function getDefaultPageContent(string $slug): array
    {
        // Return minimal default content structure
        $contentMap = [
            'index' => [
                [
                    'type' => 'hero',
                    'content' => [
                        'heading' => 'Welcome to Our Business',
                        'subheading' => 'Providing quality services since day one',
                        'buttonText' => 'Learn More',
                        'buttonLink' => '#services',
                    ],
                ],
                [
                    'type' => 'features',
                    'content' => [
                        'heading' => 'What We Offer',
                        'items' => [
                            ['title' => 'Quality Service', 'description' => 'We provide top-notch service'],
                            ['title' => 'Expert Team', 'description' => 'Our team is highly skilled'],
                            ['title' => 'Customer Focus', 'description' => 'Your satisfaction matters'],
                        ],
                    ],
                ],
            ],
            'services' => [
                [
                    'type' => 'heading',
                    'content' => ['heading' => 'Our Services', 'subheading' => 'What we can do for you'],
                ],
                [
                    'type' => 'service-list',
                    'content' => [
                        'items' => [
                            ['name' => 'Service One', 'description' => 'Description of service one'],
                            ['name' => 'Service Two', 'description' => 'Description of service two'],
                        ],
                    ],
                ],
            ],
            'about' => [
                [
                    'type' => 'heading',
                    'content' => ['heading' => 'About Us', 'subheading' => 'Our story'],
                ],
                [
                    'type' => 'text',
                    'content' => ['text' => 'We are a dedicated team committed to providing excellent service.'],
                ],
            ],
            'contact' => [
                [
                    'type' => 'heading',
                    'content' => ['heading' => 'Contact Us', 'subheading' => 'Get in touch'],
                ],
                [
                    'type' => 'contact-form',
                    'content' => ['fields' => ['name', 'email', 'message']],
                ],
            ],
        ];

        return $contentMap[$slug] ?? [
            ['type' => 'heading', 'content' => ['heading' => ucfirst($slug)]],
        ];
    }
}
