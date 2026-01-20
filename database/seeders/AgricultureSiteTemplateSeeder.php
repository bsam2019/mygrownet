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
                'content' => json_encode([
                    [
                        'type' => 'hero',
                        'content' => [
                            'title' => 'Pure Organic Solutions',
                            'subtitle' => 'Natural fertilizers and pest control for sustainable farming',
                            'buttonText' => 'View Products',
                            'buttonLink' => '#products',
                            'secondaryButtonText' => 'Contact Us',
                            'secondaryButtonLink' => '#contact',
                            'textPosition' => 'center',
                            'backgroundImage' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=1600&q=80',
                        ],
                        'style' => [
                            'backgroundType' => 'image',
                            'textColor' => '#ffffff',
                            'minHeight' => 600,
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
                                    'description' => 'Made from natural ingredients with no harmful chemicals',
                                ],
                                [
                                    'icon' => 'shield-check',
                                    'title' => 'Safe for Environment',
                                    'description' => 'Eco-friendly solutions that protect soil and water',
                                ],
                                [
                                    'icon' => 'chart-bar',
                                    'title' => 'Proven Results',
                                    'description' => 'Increased yields and healthier crops naturally',
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
                            'title' => 'Our Products',
                            'subtitle' => 'Natural solutions for modern farming',
                            'layout' => 'cards',
                            'columns' => 3,
                            'items' => [
                                [
                                    'title' => 'Organic Liquid Fertilizer',
                                    'description' => 'From portable bio digester. Rich in nutrients for all crops.',
                                    'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=600&q=80',
                                ],
                                [
                                    'title' => 'Rabbit Urine Organic',
                                    'description' => 'Dual-purpose fertilizer and pest control. Natural insecticide.',
                                    'image' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=600&q=80',
                                ],
                                [
                                    'title' => 'Neem Leaf Powder',
                                    'description' => 'Natural wonder tree powder. Powerful organic pesticide.',
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
                        'type' => 'contact',
                        'content' => [
                            'title' => 'Get In Touch',
                            'subtitle' => 'Order products or ask questions',
                            'showForm' => true,
                        ],
                        'style' => [
                            'backgroundColor' => '#f9fafb',
                            'textColor' => '#111827',
                        ],
                    ],
                ]),
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'site_template_id' => $templateId,
                'title' => 'Products',
                'slug' => 'products',
                'is_homepage' => false,
                'content' => json_encode([
                    [
                        'type' => 'page-header',
                        'content' => [
                            'title' => 'Our Products',
                            'subtitle' => 'Natural organic solutions for your farm',
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
                            'title' => 'Product Catalog',
                            'subtitle' => 'Choose the right solution for your needs',
                            'layout' => 'detailed',
                            'items' => [
                                [
                                    'title' => 'Organic Liquid Fertilizer',
                                    'description' => 'Premium liquid fertilizer from portable bio digester. Rich in essential nutrients.',
                                    'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&q=80',
                                ],
                                [
                                    'title' => 'Rabbit Urine Organic',
                                    'description' => 'Dual-purpose organic solution. Works as both fertilizer and natural pesticide.',
                                    'image' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=800&q=80',
                                ],
                                [
                                    'title' => 'Neem Leaf Powder',
                                    'description' => 'Pure neem leaf powder from the natural wonder tree. Powerful organic pesticide.',
                                    'image' => 'https://images.unsplash.com/photo-1599629954294-1c2f7f2e3c1e?w=800&q=80',
                                ],
                            ],
                        ],
                        'style' => [
                            'backgroundColor' => '#ffffff',
                            'textColor' => '#111827',
                        ],
                    ],
                ]),
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'site_template_id' => $templateId,
                'title' => 'Contact',
                'slug' => 'contact',
                'is_homepage' => false,
                'content' => json_encode([
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
                ]),
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($pages as $page) {
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
