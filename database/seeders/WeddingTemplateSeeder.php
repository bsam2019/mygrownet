<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\Wedding\WeddingTemplateModel;

class WeddingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // WEDDING TEMPLATES
            [
                'name' => 'Modern Minimal',
                'slug' => 'modern-minimal',
                'category' => 'wedding',
                'category_name' => 'Wedding',
                'category_icon' => '💍',
                'preview_text' => 'Michael & Sarah',
                'description' => 'Ultra-clean monochrome design with bold typography and dramatic contrast. Perfect for contemporary couples who value sophistication and timeless elegance.',
                'preview_image' => 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=800&q=80',
                'is_active' => true,
                'is_premium' => false,
                'settings' => [
                    'colors' => [
                        'primary' => '#000000',        // Pure black for maximum contrast
                        'secondary' => '#1a1a1a',      // Deep charcoal
                        'accent' => '#f5f5f5',         // Off-white
                        'background' => '#ffffff',     // Pure white
                    ],
                    'fonts' => [
                        'heading' => 'Cormorant Garamond',  // Elegant serif with personality
                        'body' => 'Inter',
                    ],
                    'layout' => [
                        'heroStyle' => 'centered',
                        'navigationStyle' => 'tabs',
                        'showCountdown' => true,
                        'showGallery' => true,
                    ],
                    'decorations' => [
                        'backgroundPattern' => 'minimal',
                        'headerImage' => '/images/Wedding/minimal-flora.jpg',
                        'borderStyle' => 'sharp',
                    ],
                ],
            ],
            [
                'name' => 'Elegant Gold',
                'slug' => 'elegant-gold',
                'category' => 'wedding',
                'category_name' => 'Wedding',
                'category_icon' => '💍',
                'preview_text' => 'James & Emily',
                'description' => 'Luxurious champagne and gold design with ornate flourishes. Exudes opulence and grandeur for formal, black-tie celebrations.',
                'preview_image' => 'https://images.unsplash.com/photo-1537633552985-df8429e8048b?w=800&q=80',
                'is_active' => true,
                'is_premium' => true,
                'settings' => [
                    'colors' => [
                        'primary' => '#8b6914',        // Rich antique gold
                        'secondary' => '#c4941f',      // Bright gold
                        'accent' => '#fef9e7',         // Champagne cream
                        'background' => '#fffef7',     // Warm ivory
                    ],
                    'fonts' => [
                        'heading' => 'Cinzel',         // Regal, classical serif
                        'body' => 'Crimson Text',      // Elegant body font
                    ],
                    'layout' => [
                        'heroStyle' => 'centered',
                        'navigationStyle' => 'tabs',
                        'showCountdown' => true,
                        'showGallery' => true,
                    ],
                    'decorations' => [
                        'backgroundPattern' => 'ornate',
                        'headerImage' => '/images/Wedding/gold-flora.jpg',
                        'borderStyle' => 'ornate',
                    ],
                ],
            ],
            [
                'name' => 'Garden Party',
                'slug' => 'garden-party',
                'category' => 'wedding',
                'category_name' => 'Wedding',
                'category_icon' => '💍',
                'preview_text' => 'David & Grace',
                'description' => 'Fresh botanical design with lush greens and natural textures. Brings the beauty of nature to life for outdoor garden ceremonies.',
                'preview_image' => 'https://images.unsplash.com/photo-1591604466107-ec97de577aff?w=800&q=80',
                'is_active' => true,
                'is_premium' => false,
                'settings' => [
                    'colors' => [
                        'primary' => '#047857',        // Deep forest green
                        'secondary' => '#059669',      // Emerald green
                        'accent' => '#d1fae5',         // Mint cream
                        'background' => '#f0fdf4',     // Soft sage
                    ],
                    'fonts' => [
                        'heading' => 'Libre Baskerville',  // Natural, organic serif
                        'body' => 'Lato',
                    ],
                    'layout' => [
                        'heroStyle' => 'centered',
                        'navigationStyle' => 'tabs',
                        'showCountdown' => true,
                        'showGallery' => true,
                    ],
                    'decorations' => [
                        'backgroundPattern' => 'botanical',
                        'headerImage' => '/images/Wedding/garden-flora.jpg',
                        'borderStyle' => 'organic',
                    ],
                ],
            ],
            [
                'name' => 'Sunset Romance',
                'slug' => 'sunset-romance',
                'category' => 'wedding',
                'category_name' => 'Wedding',
                'category_icon' => '💍',
                'preview_text' => 'Alex & Sophie',
                'description' => 'Dreamy coral and blush palette inspired by golden hour. Radiates warmth and romance for intimate sunset ceremonies.',
                'preview_image' => 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?w=800&q=80',
                'is_active' => true,
                'is_premium' => false,
                'settings' => [
                    'colors' => [
                        'primary' => '#dc2626',        // Deep coral red
                        'secondary' => '#f97316',      // Sunset orange
                        'accent' => '#ffe4e6',         // Soft blush
                        'background' => '#fff7ed',     // Warm cream
                    ],
                    'fonts' => [
                        'heading' => 'Allura',         // Flowing, romantic script
                        'body' => 'Quicksand',         // Soft, rounded sans-serif
                    ],
                    'layout' => [
                        'heroStyle' => 'centered',
                        'navigationStyle' => 'tabs',
                        'showCountdown' => true,
                        'showGallery' => true,
                    ],
                    'decorations' => [
                        'backgroundPattern' => 'sunset',
                        'headerImage' => '/images/Wedding/sunset-flora.jpg',
                        'borderStyle' => 'soft',
                    ],
                ],
            ],

            // BIRTHDAY TEMPLATES
            [
                'name' => 'Birthday Bash',
                'slug' => 'birthday-bash',
                'category' => 'birthday',
                'category_name' => 'Birthday',
                'category_icon' => '🎂',
                'preview_text' => 'Emma Turns 30!',
                'description' => 'Fun, colorful design with playful elements and balloons. Perfect for birthday parties of all ages.',
                'preview_image' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=800&q=80',
                'is_active' => true,
                'is_premium' => false,
                'settings' => [
                    'colors' => [
                        'primary' => '#9333ea',
                        'secondary' => '#ec4899',
                        'accent' => '#fbbf24',
                        'background' => '#faf5ff',
                    ],
                    'fonts' => [
                        'heading' => 'Fredoka',
                        'body' => 'Inter',
                    ],
                ],
            ],

            // ANNIVERSARY TEMPLATES
            [
                'name' => 'Anniversary Elegance',
                'slug' => 'anniversary-elegance',
                'category' => 'anniversary',
                'category_name' => 'Anniversary',
                'category_icon' => '💕',
                'preview_text' => 'John & Mary - 25 Years',
                'description' => 'Romantic and elegant design celebrating years of love. Perfect for milestone anniversary celebrations.',
                'preview_image' => 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=800&q=80',
                'is_active' => true,
                'is_premium' => false,
                'settings' => [
                    'colors' => [
                        'primary' => '#be123c',
                        'secondary' => '#e11d48',
                        'accent' => '#fecdd3',
                        'background' => '#fff1f2',
                    ],
                    'fonts' => [
                        'heading' => 'Playfair Display',
                        'body' => 'Lora',
                    ],
                ],
            ],

            // PARTY TEMPLATES
            [
                'name' => 'Party Vibes',
                'slug' => 'party-vibes',
                'category' => 'party',
                'category_name' => 'Party',
                'category_icon' => '🎉',
                'preview_text' => 'Summer Celebration',
                'description' => 'Energetic, vibrant design with neon-inspired colors. Perfect for any celebration or party event.',
                'preview_image' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&q=80',
                'is_active' => true,
                'is_premium' => false,
                'settings' => [
                    'colors' => [
                        'primary' => '#0891b2',
                        'secondary' => '#8b5cf6',
                        'accent' => '#fbbf24',
                        'background' => '#f0fdfa',
                    ],
                    'fonts' => [
                        'heading' => 'Poppins',
                        'body' => 'Inter',
                    ],
                ],
            ],
        ];

        foreach ($templates as $template) {
            WeddingTemplateModel::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
