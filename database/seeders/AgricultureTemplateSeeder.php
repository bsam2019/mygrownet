<?php

namespace Database\Seeders;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderTemplate;
use Illuminate\Database\Seeder;

class AgricultureTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Organic Farm',
                'slug' => 'organic-farm',
                'category' => 'agriculture',
                'description' => 'Professional template for organic farming, agricultural products, and eco-friendly businesses',
                'preview_image' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=800&q=80',
                'thumbnail' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=400&q=80',
                'is_active' => true,
                'is_premium' => false,
                'price' => 0,
                'structure_json' => [
                    'pages' => [
                        [
                        'name' => 'Home',
                        'slug' => 'home',
                        'is_home' => true,
                        'sections' => [
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
                                        [
                                            'icon' => 'currency-dollar',
                                            'title' => 'Cost Effective',
                                            'description' => 'Affordable prices for quality organic products',
                                        ],
                                        [
                                            'icon' => 'truck',
                                            'title' => 'Fast Delivery',
                                            'description' => 'Quick delivery across Zambia',
                                        ],
                                        [
                                            'icon' => 'user-group',
                                            'title' => 'Expert Support',
                                            'description' => 'Agricultural advice and guidance included',
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
                                            'description' => 'From portable bio digester. Rich in nutrients for all crops. Improves soil health and plant growth naturally.',
                                            'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=600&q=80',
                                            'features' => ['1 Liter bottle', 'All crops', 'Fast absorption'],
                                        ],
                                        [
                                            'title' => 'Rabbit Urine Organic',
                                            'description' => 'Dual-purpose fertilizer and pest control. Natural insecticide properties. Boosts plant immunity and growth.',
                                            'image' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=600&q=80',
                                            'features' => ['1 Liter bottle', 'Pest control', '100% natural'],
                                        ],
                                        [
                                            'title' => 'Neem Leaf Powder',
                                            'description' => 'Natural wonder tree powder. Herbal food supplement. Powerful organic pesticide and soil conditioner.',
                                            'image' => 'https://images.unsplash.com/photo-1599629954294-1c2f7f2e3c1e?w=600&q=80',
                                            'features' => ['Pure powder', 'Multi-purpose', 'Long lasting'],
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
                                    'title' => 'About Our Farm',
                                    'description' => 'We are dedicated to providing farmers with high-quality organic products that promote sustainable agriculture. Our products are made from natural sources and tested for effectiveness. We believe in farming that works with nature, not against it.',
                                    'imagePosition' => 'right',
                                    'image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&q=80',
                                    'features' => [
                                        'Locally produced in Zambia',
                                        'Tested and proven formulas',
                                        'Affordable for all farmers',
                                        'Free agricultural advice',
                                    ],
                                ],
                                'style' => [
                                    'backgroundColor' => '#f0fdf4',
                                    'textColor' => '#111827',
                                ],
                            ],
                            [
                                'type' => 'testimonials',
                                'content' => [
                                    'title' => 'What Farmers Say',
                                    'subtitle' => 'Real results from real farmers',
                                    'layout' => 'grid',
                                    'items' => [
                                        [
                                            'name' => 'John Mwansa',
                                            'role' => 'Vegetable Farmer, Lusaka',
                                            'content' => 'The organic liquid fertilizer has transformed my farm. My vegetables are healthier and I have seen a 40% increase in yield. Highly recommended!',
                                            'rating' => 5,
                                        ],
                                        [
                                            'name' => 'Grace Phiri',
                                            'role' => 'Maize Farmer, Chongwe',
                                            'content' => 'Rabbit urine organic works wonders as both fertilizer and pest control. My crops are pest-free and growing strong. Best investment I made!',
                                            'rating' => 5,
                                        ],
                                        [
                                            'name' => 'Patrick Banda',
                                            'role' => 'Commercial Farmer, Kafue',
                                            'content' => 'Neem powder is amazing for pest control. Natural, safe, and effective. My family and workers feel safer using organic products.',
                                            'rating' => 5,
                                        ],
                                    ],
                                ],
                                'style' => [
                                    'backgroundColor' => '#ffffff',
                                    'textColor' => '#111827',
                                ],
                            ],
                            [
                                'type' => 'cta',
                                'content' => [
                                    'title' => 'Ready to Go Organic?',
                                    'subtitle' => 'Join thousands of farmers using our natural products',
                                    'buttonText' => 'Order Now',
                                    'buttonLink' => '#contact',
                                    'secondaryButtonText' => 'Learn More',
                                    'secondaryButtonLink' => '#products',
                                ],
                                'style' => [
                                    'backgroundColor' => '#059669',
                                    'textColor' => '#ffffff',
                                ],
                            ],
                            [
                                'type' => 'contact',
                                'content' => [
                                    'title' => 'Get In Touch',
                                    'subtitle' => 'Order products or ask questions',
                                    'showForm' => true,
                                    'showMap' => false,
                                    'contactInfo' => [
                                        'phone' => '+260 XXX XXX XXX',
                                        'email' => 'info@organicfarm.zm',
                                        'address' => 'Lusaka, Zambia',
                                    ],
                                ],
                                'style' => [
                                    'backgroundColor' => '#f9fafb',
                                    'textColor' => '#111827',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Products',
                        'slug' => 'products',
                        'sections' => [
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
                                            'description' => 'Premium liquid fertilizer from portable bio digester. Rich in essential nutrients including nitrogen, phosphorus, and potassium. Suitable for all types of crops including vegetables, maize, and fruits.',
                                            'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&q=80',
                                            'features' => [
                                                'Available in 1L, 5L, and 20L containers',
                                                'Fast nutrient absorption',
                                                'Improves soil structure',
                                                'Increases crop yield by 30-50%',
                                                'Safe for all crops',
                                            ],
                                            'price' => 'Contact for pricing',
                                        ],
                                        [
                                            'title' => 'Rabbit Urine Organic Fertilizer & Pest Control',
                                            'description' => 'Dual-purpose organic solution. Works as both fertilizer and natural pesticide. High in nitrogen and contains natural compounds that repel common pests. Environmentally friendly alternative to chemical pesticides.',
                                            'image' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=800&q=80',
                                            'features' => [
                                                'Available in 1L and 5L bottles',
                                                'Natural pest repellent',
                                                'Boosts plant immunity',
                                                'No harmful residues',
                                                'Safe for organic farming',
                                            ],
                                            'price' => 'Contact for pricing',
                                        ],
                                        [
                                            'title' => 'Neem Leaf Powder',
                                            'description' => 'Pure neem leaf powder from the natural wonder tree. Powerful organic pesticide and soil conditioner. Also used as herbal food supplement. Contains azadirachtin which disrupts pest life cycles naturally.',
                                            'image' => 'https://images.unsplash.com/photo-1599629954294-1c2f7f2e3c1e?w=800&q=80',
                                            'features' => [
                                                'Available in jars and bulk',
                                                'Multi-purpose use',
                                                'Long shelf life',
                                                'Controls 200+ pest species',
                                                'Safe for beneficial insects',
                                            ],
                                            'price' => 'Contact for pricing',
                                        ],
                                    ],
                                ],
                                'style' => [
                                    'backgroundColor' => '#ffffff',
                                    'textColor' => '#111827',
                                ],
                            ],
                            [
                                'type' => 'faq',
                                'content' => [
                                    'title' => 'Product Questions',
                                    'items' => [
                                        [
                                            'question' => 'How do I apply the liquid fertilizer?',
                                            'answer' => 'Dilute 1 part fertilizer with 10 parts water. Apply to soil around plants or as foliar spray. Use every 2 weeks during growing season.',
                                        ],
                                        [
                                            'question' => 'Is rabbit urine safe for vegetables?',
                                            'answer' => 'Yes, completely safe. It is 100% organic and leaves no harmful residues. Safe to use on all edible crops.',
                                        ],
                                        [
                                            'question' => 'How long does neem powder last?',
                                            'answer' => 'When stored in a cool, dry place, neem powder remains effective for up to 2 years.',
                                        ],
                                        [
                                            'question' => 'Do you offer bulk discounts?',
                                            'answer' => 'Yes, we offer attractive discounts for bulk orders. Contact us for wholesale pricing.',
                                        ],
                                    ],
                                ],
                                'style' => [
                                    'backgroundColor' => '#f9fafb',
                                    'textColor' => '#111827',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'About',
                        'slug' => 'about',
                        'sections' => [
                            [
                                'type' => 'page-header',
                                'content' => [
                                    'title' => 'About Us',
                                    'subtitle' => 'Committed to sustainable agriculture',
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
                                    'title' => 'Our Story',
                                    'description' => 'We started with a simple mission: to help Zambian farmers grow healthier crops using natural, organic methods. Our products are developed and tested locally to ensure they work in our climate and soil conditions. We believe that sustainable farming is not just good for the environment, but also more profitable for farmers in the long run.',
                                    'imagePosition' => 'right',
                                    'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&q=80',
                                ],
                                'style' => [
                                    'backgroundColor' => '#ffffff',
                                    'textColor' => '#111827',
                                ],
                            ],
                            [
                                'type' => 'features',
                                'content' => [
                                    'title' => 'Our Values',
                                    'layout' => 'grid',
                                    'columns' => 2,
                                    'items' => [
                                        [
                                            'icon' => 'leaf',
                                            'title' => 'Sustainability',
                                            'description' => 'We promote farming practices that protect the environment for future generations',
                                        ],
                                        [
                                            'icon' => 'shield-check',
                                            'title' => 'Quality',
                                            'description' => 'Every product is tested to ensure it meets our high standards',
                                        ],
                                        [
                                            'icon' => 'users',
                                            'title' => 'Community',
                                            'description' => 'We support local farmers with training and affordable products',
                                        ],
                                        [
                                            'icon' => 'light-bulb',
                                            'title' => 'Innovation',
                                            'description' => 'Constantly researching new organic solutions for modern challenges',
                                        ],
                                    ],
                                ],
                                'style' => [
                                    'backgroundColor' => '#f0fdf4',
                                    'textColor' => '#111827',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Contact',
                        'slug' => 'contact',
                        'sections' => [
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
                                    'showMap' => false,
                                    'contactInfo' => [
                                        'phone' => '+260 XXX XXX XXX',
                                        'email' => 'info@organicfarm.zm',
                                        'address' => 'Lusaka, Zambia',
                                        'hours' => 'Monday - Saturday: 8:00 AM - 5:00 PM',
                                    ],
                                ],
                                'style' => [
                                    'backgroundColor' => '#ffffff',
                                    'textColor' => '#111827',
                                ],
                            ],
                            [
                                'type' => 'features',
                                'content' => [
                                    'title' => 'Why Order From Us',
                                    'layout' => 'grid',
                                    'columns' => 3,
                                    'items' => [
                                        [
                                            'icon' => 'truck',
                                            'title' => 'Fast Delivery',
                                            'description' => 'Quick delivery across Lusaka and surrounding areas',
                                        ],
                                        [
                                            'icon' => 'currency-dollar',
                                            'title' => 'Best Prices',
                                            'description' => 'Competitive pricing with bulk discounts available',
                                        ],
                                        [
                                            'icon' => 'phone',
                                            'title' => 'Expert Support',
                                            'description' => 'Free agricultural advice with every purchase',
                                        ],
                                    ],
                                ],
                                'style' => [
                                    'backgroundColor' => '#f9fafb',
                                    'textColor' => '#111827',
                                ],
                            ],
                        ],
                    ],
                ],
                'default_styles' => [
                    'primaryColor' => '#059669',      // Emerald green for organic/nature
                    'secondaryColor' => '#10b981',    // Lighter green
                    'accentColor' => '#f59e0b',       // Amber for warmth
                    'backgroundColor' => '#ffffff',   // Clean white
                    'textColor' => '#1f2937',
                    'headingFont' => 'Inter',
                    'bodyFont' => 'Inter',
                    'borderRadius' => 8,
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            GrowBuilderTemplate::updateOrCreate(
                ['slug' => $templateData['slug']],
                $templateData
            );
        }
    }
}
