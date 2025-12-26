<?php

namespace Database\Seeders;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderTemplate;
use Illuminate\Database\Seeder;

class GrowBuilderTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Business Pro',
                'slug' => 'business-pro',
                'category' => 'business',
                'description' => 'Professional template for general businesses',
                'is_premium' => false,
                'price' => 0,
                'structure_json' => [
                    'sections' => [
                        [
                            'type' => 'hero',
                            'content' => [
                                'title' => 'Welcome to Our Business',
                                'subtitle' => 'Professional services you can trust',
                                'buttonText' => 'Get Started',
                                'buttonLink' => '#contact',
                            ],
                        ],
                        [
                            'type' => 'services',
                            'content' => [
                                'title' => 'Our Services',
                                'items' => [
                                    ['icon' => 'briefcase', 'title' => 'Consulting', 'description' => 'Expert business advice'],
                                    ['icon' => 'chart-bar', 'title' => 'Analytics', 'description' => 'Data-driven insights'],
                                    ['icon' => 'users', 'title' => 'Support', 'description' => '24/7 customer support'],
                                ],
                            ],
                        ],
                        [
                            'type' => 'about',
                            'content' => [
                                'title' => 'About Us',
                                'description' => 'We are a dedicated team of professionals committed to delivering excellence.',
                            ],
                        ],
                        [
                            'type' => 'contact',
                            'content' => [
                                'title' => 'Contact Us',
                                'description' => 'Get in touch today',
                                'showForm' => true,
                            ],
                        ],
                    ],
                ],
                'default_styles' => [
                    'primaryColor' => '#2563eb',
                    'secondaryColor' => '#64748b',
                    'accentColor' => '#059669',
                    'backgroundColor' => '#ffffff',
                    'textColor' => '#1f2937',
                    'headingFont' => 'Inter',
                    'bodyFont' => 'Inter',
                    'borderRadius' => 8,
                ],
            ],
            [
                'name' => 'Restaurant Starter',
                'slug' => 'restaurant-starter',
                'category' => 'restaurant',
                'description' => 'Perfect for restaurants and food businesses',
                'is_premium' => false,
                'price' => 0,
                'structure_json' => [
                    'sections' => [
                        [
                            'type' => 'hero',
                            'content' => [
                                'title' => 'Delicious Food Awaits',
                                'subtitle' => 'Experience the finest cuisine in town',
                                'buttonText' => 'View Menu',
                                'buttonLink' => '#menu',
                            ],
                        ],
                        [
                            'type' => 'features',
                            'content' => [
                                'title' => 'Why Choose Us',
                                'items' => [
                                    ['icon' => 'fire', 'title' => 'Fresh Ingredients', 'description' => 'Locally sourced'],
                                    ['icon' => 'clock', 'title' => 'Fast Service', 'description' => 'Quick delivery'],
                                    ['icon' => 'heart', 'title' => 'Made with Love', 'description' => 'Family recipes'],
                                ],
                            ],
                        ],
                        [
                            'type' => 'gallery',
                            'content' => [
                                'title' => 'Our Dishes',
                                'images' => [],
                            ],
                        ],
                        [
                            'type' => 'contact',
                            'content' => [
                                'title' => 'Visit Us',
                                'description' => 'Find us at our location',
                                'showForm' => true,
                                'showMap' => true,
                            ],
                        ],
                    ],
                ],
                'default_styles' => [
                    'primaryColor' => '#dc2626',
                    'secondaryColor' => '#78350f',
                    'accentColor' => '#f59e0b',
                    'backgroundColor' => '#fffbeb',
                    'textColor' => '#1f2937',
                    'headingFont' => 'Playfair Display',
                    'bodyFont' => 'Inter',
                    'borderRadius' => 12,
                ],
            ],
            [
                'name' => 'Church Community',
                'slug' => 'church-community',
                'category' => 'church',
                'description' => 'Welcoming template for churches and religious organizations',
                'is_premium' => false,
                'price' => 0,
                'structure_json' => [
                    'sections' => [
                        [
                            'type' => 'hero',
                            'content' => [
                                'title' => 'Welcome Home',
                                'subtitle' => 'Join our community of faith',
                                'buttonText' => 'Join Us Sunday',
                                'buttonLink' => '#services',
                            ],
                        ],
                        [
                            'type' => 'about',
                            'content' => [
                                'title' => 'Our Mission',
                                'description' => 'We are a community dedicated to spreading love, hope, and faith.',
                            ],
                        ],
                        [
                            'type' => 'services',
                            'content' => [
                                'title' => 'Service Times',
                                'items' => [
                                    ['icon' => 'sun', 'title' => 'Sunday Service', 'description' => '9:00 AM & 11:00 AM'],
                                    ['icon' => 'book', 'title' => 'Bible Study', 'description' => 'Wednesday 7:00 PM'],
                                    ['icon' => 'users', 'title' => 'Youth Group', 'description' => 'Friday 6:00 PM'],
                                ],
                            ],
                        ],
                        [
                            'type' => 'contact',
                            'content' => [
                                'title' => 'Get In Touch',
                                'description' => 'We would love to hear from you',
                                'showForm' => true,
                            ],
                        ],
                    ],
                ],
                'default_styles' => [
                    'primaryColor' => '#7c3aed',
                    'secondaryColor' => '#6366f1',
                    'accentColor' => '#f59e0b',
                    'backgroundColor' => '#faf5ff',
                    'textColor' => '#1f2937',
                    'headingFont' => 'Merriweather',
                    'bodyFont' => 'Inter',
                    'borderRadius' => 8,
                ],
            ],
            [
                'name' => 'Tutor Pro',
                'slug' => 'tutor-pro',
                'category' => 'tutor',
                'description' => 'Professional template for tutors and educators',
                'is_premium' => false,
                'price' => 0,
                'structure_json' => [
                    'sections' => [
                        [
                            'type' => 'hero',
                            'content' => [
                                'title' => 'Expert Tutoring Services',
                                'subtitle' => 'Helping students achieve their academic goals',
                                'buttonText' => 'Book a Session',
                                'buttonLink' => '#contact',
                            ],
                        ],
                        [
                            'type' => 'services',
                            'content' => [
                                'title' => 'Subjects I Teach',
                                'items' => [
                                    ['icon' => 'calculator', 'title' => 'Mathematics', 'description' => 'All levels'],
                                    ['icon' => 'beaker', 'title' => 'Science', 'description' => 'Physics, Chemistry, Biology'],
                                    ['icon' => 'language', 'title' => 'Languages', 'description' => 'English, French'],
                                ],
                            ],
                        ],
                        [
                            'type' => 'testimonials',
                            'content' => [
                                'title' => 'Student Success Stories',
                                'items' => [
                                    ['name' => 'Parent', 'text' => 'My child\'s grades improved significantly!'],
                                ],
                            ],
                        ],
                        [
                            'type' => 'pricing',
                            'content' => [
                                'title' => 'Pricing',
                                'plans' => [
                                    ['name' => 'Single Session', 'price' => 'K100', 'features' => ['1 hour session', 'Any subject']],
                                    ['name' => 'Monthly Package', 'price' => 'K350', 'features' => ['4 sessions', 'Progress reports', 'WhatsApp support']],
                                ],
                            ],
                        ],
                        [
                            'type' => 'contact',
                            'content' => [
                                'title' => 'Book Your Session',
                                'description' => 'Contact me to schedule a tutoring session',
                                'showForm' => true,
                            ],
                        ],
                    ],
                ],
                'default_styles' => [
                    'primaryColor' => '#0891b2',
                    'secondaryColor' => '#0e7490',
                    'accentColor' => '#f59e0b',
                    'backgroundColor' => '#ecfeff',
                    'textColor' => '#1f2937',
                    'headingFont' => 'Inter',
                    'bodyFont' => 'Inter',
                    'borderRadius' => 8,
                ],
            ],
            [
                'name' => 'Portfolio Minimal',
                'slug' => 'portfolio-minimal',
                'category' => 'portfolio',
                'description' => 'Clean portfolio template for professionals',
                'is_premium' => false,
                'price' => 0,
                'structure_json' => [
                    'sections' => [
                        [
                            'type' => 'hero',
                            'content' => [
                                'title' => 'Hi, I\'m [Your Name]',
                                'subtitle' => 'Designer & Developer',
                                'buttonText' => 'View My Work',
                                'buttonLink' => '#portfolio',
                            ],
                        ],
                        [
                            'type' => 'about',
                            'content' => [
                                'title' => 'About Me',
                                'description' => 'I\'m a passionate professional with years of experience in my field.',
                            ],
                        ],
                        [
                            'type' => 'gallery',
                            'content' => [
                                'title' => 'My Work',
                                'images' => [],
                            ],
                        ],
                        [
                            'type' => 'contact',
                            'content' => [
                                'title' => 'Let\'s Work Together',
                                'description' => 'Have a project in mind? Let\'s talk!',
                                'showForm' => true,
                            ],
                        ],
                    ],
                ],
                'default_styles' => [
                    'primaryColor' => '#18181b',
                    'secondaryColor' => '#71717a',
                    'accentColor' => '#f59e0b',
                    'backgroundColor' => '#fafafa',
                    'textColor' => '#18181b',
                    'headingFont' => 'Inter',
                    'bodyFont' => 'Inter',
                    'borderRadius' => 4,
                ],
            ],
        ];

        foreach ($templates as $template) {
            GrowBuilderTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
