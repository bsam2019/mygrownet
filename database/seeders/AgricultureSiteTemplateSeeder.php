<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgricultureSiteTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Insert the agriculture site template
        DB::table('site_templates')->updateOrInsert(
            ['slug' => 'organic-farm'],
            [
                'name' => 'Organic Farm',
                'slug' => 'organic-farm',
                'description' => 'Professional template for organic farming, agricultural products, and eco-friendly businesses',
                'industry' => 'agriculture',
                'thumbnail' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=400&q=80',
                'theme' => json_encode([
                    'primaryColor' => '#059669',
                    'secondaryColor' => '#10b981',
                    'accentColor' => '#f59e0b',
                    'backgroundColor' => '#ffffff',
                    'textColor' => '#1f2937',
                ]),
                'settings' => json_encode([
                    'headingFont' => 'Inter',
                    'bodyFont' => 'Inter',
                    'borderRadius' => 8,
                    'navigation' => [
                        'logoText' => 'Organic Farm',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Order Now',
                        'ctaLink' => '/shop',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2026 Organic Farm. All rights reserved.',
                        'columns' => [
                            ['title' => 'Products', 'links' => [['label' => 'Fertilizers', 'url' => '/shop'], ['label' => 'Pest Control', 'url' => '/shop'], ['label' => 'Organic Solutions', 'url' => '/shop']]],
                            ['title' => 'Company', 'links' => [['label' => 'About Us', 'url' => '/about'], ['label' => 'Our Story', 'url' => '/about'], ['label' => 'Contact', 'url' => '/contact']]],
                        ],
                    ],
                ]),
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 12,
                'usage_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Get the template ID
        $templateId = DB::table('site_templates')->where('slug', 'organic-farm')->value('id');

        // Insert pages for this template
        $pages = [
            [
                'site_template_id' => $templateId,
                'title' => 'Home',
                'slug' => 'home',
                'is_homepage' => true,
                'content' => ['sections' => [
                    [
                        'type' => 'hero',
                        'content' => [
                            'layout' => 'centered',
                            'title' => 'Pure Organic Solutions for Sustainable Farming',
                            'subtitle' => 'Natural fertilizers and pest control products that protect your crops, soil, and the environment',
                            'buttonText' => 'Shop Products',
                            'buttonLink' => '/shop',
                            'secondaryButtonText' => 'Learn More',
                            'secondaryButtonLink' => '/about',
                            'backgroundImage' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=1600&q=80',
                            'overlayColor' => 'black',
                            'overlayOpacity' => 40,
                        ],
                        'style' => [
                            'minHeight' => 650,
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'content' => [
                            'layout' => 'row',
                            'items' => [
                                ['value' => '100%', 'label' => 'Organic'],
                                ['value' => '500+', 'label' => 'Happy Farmers'],
                                ['value' => '5 Years', 'label' => 'Experience'],
                                ['value' => 'Eco-Safe', 'label' => 'Certified'],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#059669',
                        ],
                    ],
                    [
                        'type' => 'features',
                        'content' => [
                            'title' => 'Why Choose Organic',
                            'subtitle' => 'Benefits of our natural products',
                            'layout' => 'grid',
                            'columns' => 3,
                            'items' => [
                                [
                                    'icon' => 'leaf',
                                    'title' => '100% Organic',
                                    'description' => 'Made from natural ingredients with no harmful chemicals or synthetic additives',
                                ],
                                [
                                    'icon' => 'shield-check',
                                    'title' => 'Safe for Environment',
                                    'description' => 'Eco-friendly solutions that protect soil health, water quality, and biodiversity',
                                ],
                                [
                                    'icon' => 'chart-bar',
                                    'title' => 'Proven Results',
                                    'description' => 'Increased yields and healthier crops naturally without compromising future harvests',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#f9fafb',
                            'textColor' => '#111827',
                        ],
                    ],
                    [
                        'type' => 'services',
                        'content' => [
                            'title' => 'Our Premium Products',
                            'subtitle' => 'Natural solutions for modern farming challenges',
                            'layout' => 'cards-images',
                            'columns' => 3,
                            'items' => [
                                [
                                    'title' => 'Organic Liquid Fertilizer',
                                    'description' => 'Premium liquid fertilizer from portable bio digester. Rich in essential nutrients for all crop types. Improves soil structure and promotes healthy root development.',
                                    'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=600&q=80',
                                ],
                                [
                                    'title' => 'Rabbit Urine Organic',
                                    'description' => 'Dual-purpose organic solution. Works as both fertilizer and natural pesticide. High nitrogen content for vigorous plant growth and natural insect repellent properties.',
                                    'image' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=600&q=80',
                                ],
                                [
                                    'title' => 'Neem Leaf Powder',
                                    'description' => 'Pure neem leaf powder from the natural wonder tree. Powerful organic pesticide that controls pests without harming beneficial insects. Safe for humans and animals.',
                                    'image' => 'https://images.unsplash.com/photo-1599629954294-1c2f7f2e3c1e?w=600&q=80',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                            'textColor' => '#111827',
                        ],
                    ],
                    [
                        'type' => 'about',
                        'content' => [
                            'layout' => 'image-right',
                            'title' => 'Committed to Sustainable Agriculture',
                            'description' => 'We believe in farming that works with nature, not against it. Our organic products help farmers achieve better yields while protecting the environment for future generations.',
                            'image' => 'https://images.unsplash.com/photo-1574943320219-553eb213f72d?w=800&q=80',
                            'features' => [
                                'Locally produced from natural sources',
                                'Tested and proven by Zambian farmers',
                                'Affordable prices for all farm sizes',
                                'Expert advice and support included',
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#f0fdf4',
                        ],
                    ],
                    [
                        'type' => 'testimonials',
                        'content' => [
                            'layout' => 'carousel',
                            'title' => 'What Farmers Say',
                            'items' => [
                                [
                                    'name' => 'John Banda',
                                    'role' => 'Maize Farmer, Chongwe',
                                    'text' => 'Since switching to these organic products, my soil health has improved dramatically. My maize yields increased by 30% and I no longer worry about chemical residues.',
                                    'rating' => 5,
                                ],
                                [
                                    'name' => 'Grace Mwanza',
                                    'role' => 'Vegetable Farmer, Lusaka',
                                    'text' => 'The neem powder completely solved my pest problems naturally. My vegetables are healthier and my customers love that they are truly organic.',
                                    'rating' => 5,
                                ],
                                [
                                    'name' => 'Peter Phiri',
                                    'role' => 'Commercial Farmer, Mkushi',
                                    'text' => 'Best investment I made for my farm. The liquid fertilizer works faster than I expected and the rabbit urine keeps pests away naturally.',
                                    'rating' => 5,
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'content' => [
                            'layout' => 'banner',
                            'title' => 'Ready to Go Organic?',
                            'description' => 'Join hundreds of farmers who have made the switch to sustainable, organic farming',
                            'buttonText' => 'Order Products',
                            'buttonLink' => '/shop',
                        ],
                        'style' => [
                            'backgroundColor' => '#059669',
                        ],
                    ],
                ]],
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'site_template_id' => $templateId,
                'title' => 'About',
                'slug' => 'about',
                'is_homepage' => false,
                'content' => ['sections' => [
                    [
                        'type' => 'page-header',
                        'content' => [
                            'title' => 'About Us',
                            'subtitle' => 'Growing naturally, farming sustainably',
                            'backgroundImage' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=1600&q=80',
                        ],
                        'style' => [
                            'backgroundType' => 'image',
                            'textColor' => '#ffffff',
                            'minHeight' => 300,
                        ],
                    ],
                    [
                        'type' => 'about',
                        'content' => [
                            'layout' => 'image-left',
                            'title' => 'Our Story',
                            'description' => 'Founded by passionate farmers who saw the need for truly organic agricultural solutions in Zambia. We started with a simple portable bio digester and a vision to help farmers transition to sustainable practices. Today, we serve over 500 farmers across the country with our range of certified organic products.',
                            'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&q=80',
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                        ],
                    ],
                    [
                        'type' => 'features',
                        'content' => [
                            'layout' => 'grid',
                            'title' => 'Our Values',
                            'subtitle' => 'What drives us every day',
                            'columns' => 4,
                            'items' => [
                                [
                                    'icon' => 'leaf',
                                    'title' => 'Sustainability',
                                    'description' => 'Protecting the environment for future generations',
                                ],
                                [
                                    'icon' => 'users',
                                    'title' => 'Community',
                                    'description' => 'Supporting local farmers and communities',
                                ],
                                [
                                    'icon' => 'shield-check',
                                    'title' => 'Quality',
                                    'description' => 'Only the best organic products',
                                ],
                                [
                                    'icon' => 'heart',
                                    'title' => 'Integrity',
                                    'description' => 'Honest and transparent in everything',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#f0fdf4',
                        ],
                    ],
                    [
                        'type' => 'about',
                        'content' => [
                            'layout' => 'image-right',
                            'title' => 'Our Mission',
                            'description' => 'To make organic farming accessible and profitable for every Zambian farmer. We believe that sustainable agriculture is not just better for the environment - it is better for farmers, consumers, and communities. Through education, quality products, and ongoing support, we are building a movement towards truly sustainable farming practices.',
                            'image' => 'https://images.unsplash.com/photo-1574943320219-553eb213f72d?w=800&q=80',
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'content' => [
                            'layout' => 'row',
                            'items' => [
                                ['value' => '500+', 'label' => 'Farmers Served'],
                                ['value' => '10,000+', 'label' => 'Hectares Organic'],
                                ['value' => '5 Years', 'label' => 'In Business'],
                                ['value' => '100%', 'label' => 'Satisfaction Rate'],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#059669',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'content' => [
                            'layout' => 'split',
                            'title' => 'Join the Organic Movement',
                            'description' => 'Ready to make the switch to sustainable farming? We are here to help you every step of the way.',
                            'buttonText' => 'Get Started',
                            'buttonLink' => '/contact',
                            'image' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=600&q=80',
                        ],
                        'style' => [
                            'backgroundColor' => '#10b981',
                        ],
                    ],
                ]],
                'sort_order' => 2,
            ],
            [
                'site_template_id' => $templateId,
                'title' => 'Shop',
                'slug' => 'shop',
                'is_homepage' => false,
                'content' => ['sections' => [
                    [
                        'type' => 'page-header',
                        'content' => [
                            'title' => 'Our Products',
                            'subtitle' => 'Premium organic solutions for every farming need',
                            'backgroundImage' => 'https://images.unsplash.com/photo-1574943320219-553eb213f72d?w=1600&q=80',
                        ],
                        'style' => [
                            'backgroundType' => 'image',
                            'textColor' => '#ffffff',
                            'minHeight' => 300,
                        ],
                    ],
                    [
                        'type' => 'services',
                        'content' => [
                            'title' => 'Featured Products',
                            'subtitle' => 'Our most popular organic solutions',
                            'layout' => 'alternating',
                            'items' => [
                                [
                                    'title' => 'Organic Liquid Fertilizer',
                                    'description' => 'Premium liquid fertilizer produced from our portable bio digester system. Rich in essential nutrients including nitrogen, phosphorus, and potassium. Perfect for all crop types including maize, vegetables, and fruit trees. Improves soil structure, promotes healthy root development, and increases water retention. Easy to apply and fast-acting results visible within 2 weeks.',
                                    'icon' => 'beaker',
                                    'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&q=80',
                                ],
                                [
                                    'title' => 'Rabbit Urine Organic Fertilizer & Pest Control',
                                    'description' => 'Dual-purpose organic solution that works as both fertilizer and natural pesticide. High nitrogen content (2.5%) promotes vigorous plant growth and deep green foliage. Natural insect repellent properties protect crops from aphids, caterpillars, and other common pests. Safe for beneficial insects like bees. Dilute 1:10 with water for application. Suitable for vegetables, flowers, and field crops.',
                                    'icon' => 'shield',
                                    'image' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=800&q=80',
                                ],
                                [
                                    'title' => 'Neem Leaf Powder',
                                    'description' => 'Pure neem leaf powder from the natural wonder tree (Azadirachta indica). Powerful organic pesticide that controls over 200 species of pests including whiteflies, beetles, and mites. Contains azadirachtin, a natural compound that disrupts insect growth and reproduction. Safe for humans, animals, and beneficial insects. Can be used as foliar spray or soil amendment. Also improves soil health and acts as natural fungicide.',
                                    'icon' => 'sparkles',
                                    'image' => 'https://images.unsplash.com/photo-1599629954294-1c2f7f2e3c1e?w=800&q=80',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                        ],
                    ],
                    [
                        'type' => 'pricing',
                        'content' => [
                            'layout' => 'cards',
                            'title' => 'Product Packages',
                            'subtitle' => 'Choose the right package for your farm size',
                            'plans' => [
                                [
                                    'name' => 'Starter Pack',
                                    'price' => 'K350',
                                    'features' => [
                                        '5L Liquid Fertilizer',
                                        '2L Rabbit Urine Solution',
                                        '500g Neem Powder',
                                        'Application guide included',
                                        'Covers 0.5 hectare',
                                    ],
                                    'buttonText' => 'Order Now',
                                ],
                                [
                                    'name' => 'Farm Pack',
                                    'price' => 'K950',
                                    'popular' => true,
                                    'features' => [
                                        '20L Liquid Fertilizer',
                                        '10L Rabbit Urine Solution',
                                        '2kg Neem Powder',
                                        'Free consultation',
                                        'Covers 2 hectares',
                                        'Free delivery in Lusaka',
                                    ],
                                    'buttonText' => 'Most Popular',
                                ],
                                [
                                    'name' => 'Commercial Pack',
                                    'price' => 'Custom',
                                    'features' => [
                                        'Bulk quantities available',
                                        'Custom product mix',
                                        'Dedicated support',
                                        'Training for farm workers',
                                        'Covers 5+ hectares',
                                        'Nationwide delivery',
                                    ],
                                    'buttonText' => 'Contact Us',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#f0fdf4',
                        ],
                    ],
                    [
                        'type' => 'features',
                        'content' => [
                            'layout' => 'grid',
                            'title' => 'Why Buy From Us',
                            'columns' => 3,
                            'items' => [
                                [
                                    'icon' => 'truck',
                                    'title' => 'Fast Delivery',
                                    'description' => 'Same-day delivery in Lusaka, nationwide shipping available',
                                ],
                                [
                                    'icon' => 'phone',
                                    'title' => 'Expert Support',
                                    'description' => 'Free consultation and ongoing advice from our agronomists',
                                ],
                                [
                                    'icon' => 'badge-check',
                                    'title' => 'Quality Guaranteed',
                                    'description' => 'Certified organic products with money-back guarantee',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'content' => [
                            'layout' => 'banner',
                            'title' => 'Ready to Order?',
                            'description' => 'Contact us today to place your order or get a custom quote for your farm',
                            'buttonText' => 'Contact Us',
                            'buttonLink' => '/contact',
                        ],
                        'style' => [
                            'backgroundColor' => '#059669',
                        ],
                    ],
                ]],
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'site_template_id' => $templateId,
                'title' => 'Contact',
                'slug' => 'contact',
                'is_homepage' => false,
                'content' => ['sections' => [
                    [
                        'type' => 'page-header',
                        'content' => [
                            'title' => 'Contact Us',
                            'subtitle' => 'We are here to help',
                            'backgroundImage' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=1600&q=80',
                        ],
                        'style' => [
                            'backgroundType' => 'image',
                            'textColor' => '#ffffff',
                            'minHeight' => 300,
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'content' => [
                            'title' => 'Get In Touch',
                            'subtitle' => 'Order products, ask questions, or request agricultural advice',
                            'showForm' => true,
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                            'textColor' => '#111827',
                        ],
                    ],
                ]],
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($pages as $page) {
            // JSON encode the content before inserting
            $page['content'] = json_encode($page['content']);
            
            DB::table('site_template_pages')->updateOrInsert(
                [
                    'site_template_id' => $page['site_template_id'],
                    'slug' => $page['slug'],
                ],
                $page
            );
        }
    }
}
