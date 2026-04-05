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

            // NEW PREMIUM TEMPLATES
            [
                'name' => 'Minimal',
                'slug' => 'minimal',
                'category' => 'wedding',
                'category_name' => 'Wedding',
                'category_icon' => '💍',
                'preview_text' => 'Alex & Jordan',
                'description' => 'Ultra-minimalist design with vertical sidebar navigation and clean typography. Perfect for modern couples who appreciate simplicity and elegance.',
                'preview_image' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=800&q=80',
                'is_active' => true,
                'is_premium' => true,
                'settings' => [
                    'colors' => [
                        'primary' => '#1a1a1a',        // Near black
                        'secondary' => '#4a4a4a',      // Dark gray
                        'accent' => '#ffffff',         // Pure white
                        'background' => '#fafafa',     // Off-white
                    ],
                    'fonts' => [
                        'heading' => 'Cormorant Garamond',  // Elegant serif
                        'body' => 'Karla',                   // Clean sans-serif
                    ],
                    'layout' => [
                        'heroStyle' => 'sidebar',
                        'navigationStyle' => 'vertical',
                        'showCountdown' => true,
                        'showGallery' => true,
                    ],
                    'decorations' => [
                        'backgroundPattern' => 'none',
                        'headerImage' => '/images/Wedding/minimal-hero.jpg',
                        'borderStyle' => 'minimal',
                    ],
                ],
            ],
            [
                'name' => 'Magazine',
                'slug' => 'magazine',
                'category' => 'wedding',
                'category_name' => 'Wedding',
                'category_icon' => '💍',
                'preview_text' => 'Chris & Taylor',
                'description' => 'Editorial magazine-style layout with bold typography and striking visuals. Inspired by high-fashion wedding publications.',
                'preview_image' => 'https://images.unsplash.com/photo-1606800052052-a08af7148866?w=800&q=80',
                'is_active' => true,
                'is_premium' => true,
                'settings' => [
                    'colors' => [
                        'primary' => '#000000',        // Pure black
                        'secondary' => '#e5e5e5',      // Light gray
                        'accent' => '#d4af37',         // Gold accent
                        'background' => '#ffffff',     // Pure white
                    ],
                    'fonts' => [
                        'heading' => 'Playfair Display',  // Editorial serif
                        'body' => 'Lato',                  // Modern sans-serif
                    ],
                    'layout' => [
                        'heroStyle' => 'fullscreen',
                        'navigationStyle' => 'fixed',
                        'showCountdown' => true,
                        'showGallery' => true,
                    ],
                    'decorations' => [
                        'backgroundPattern' => 'editorial',
                        'headerImage' => '/images/Wedding/magazine-hero.jpg',
                        'borderStyle' => 'bold',
                    ],
                ],
            ],
            [
                'name' => 'Dark Luxury',
                'slug' => 'dark-luxury',
                'category' => 'wedding',
                'category_name' => 'Wedding',
                'category_icon' => '💍',
                'preview_text' => 'Marcus & Isabella',
                'description' => 'Sophisticated dark theme with luxurious gold accents. Perfect for elegant evening weddings and black-tie affairs.',
                'preview_image' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=800&q=80',
                'is_active' => true,
                'is_premium' => true,
                'settings' => [
                    'colors' => [
                        'primary' => '#1a1a1a',        // Deep charcoal
                        'secondary' => '#2d2d2d',      // Dark gray
                        'accent' => '#d4af37',         // Luxe gold
                        'background' => '#0a0a0a',     // Near black
                    ],
                    'fonts' => [
                        'heading' => 'Cinzel',         // Luxury serif
                        'body' => 'Montserrat',        // Elegant sans-serif
                    ],
                    'layout' => [
                        'heroStyle' => 'dramatic',
                        'navigationStyle' => 'floating',
                        'showCountdown' => true,
                        'showGallery' => true,
                    ],
                    'decorations' => [
                        'backgroundPattern' => 'luxury',
                        'headerImage' => '/images/Wedding/dark-luxury-hero.jpg',
                        'borderStyle' => 'ornate-gold',
                    ],
                ],
            ],
            [
                'name' => 'Flora Classic',
                'slug' => 'flora-classic',
                'category' => 'wedding',
                'category_name' => 'Wedding',
                'category_icon' => '💍',
                'preview_text' => 'Robert & Elizabeth',
                'description' => 'Elegant floral design with ornate frames and classic typography. Perfect for traditional weddings with timeless sophistication.',
                'preview_image' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80',
                'is_active' => true,
                'is_premium' => false,
                'settings' => [
                    'colors' => [
                        'primary' => '#e11d48',        // Deep rose
                        'secondary' => '#f43f5e',      // Bright pink
                        'accent' => '#fef3c7',         // Soft amber
                        'background' => '#fff7ed',     // Warm cream
                    ],
                    'fonts' => [
                        'heading' => 'Playfair Display',  // Classic serif
                        'body' => 'Crimson Text',        // Elegant body font
                    ],
                    'layout' => [
                        'heroStyle' => 'centered',
                        'navigationStyle' => 'tabs',
                        'showCountdown' => true,
                        'showGallery' => true,
                    ],
                    'decorations' => [
                        'backgroundPattern' => 'floral',
                        'headerImage' => '/images/Wedding/flora-hero.jpg',
                        'borderStyle' => 'ornate',
                    ],
                ],
            ],
            [
                'name' => 'Romantic',
                'slug' => 'romantic',
                'category' => 'wedding',
                'category_name' => 'Wedding',
                'category_icon' => '💍',
                'preview_text' => 'Daniel & Sophie',
                'description' => 'Soft, dreamy design with delicate florals and romantic pastels. Perfect for intimate garden weddings and romantic celebrations.',
                'preview_image' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80',
                'is_active' => true,
                'is_premium' => false,
                'settings' => [
                    'colors' => [
                        'primary' => '#d4a5a5',        // Dusty rose
                        'secondary' => '#e8c4c4',      // Blush pink
                        'accent' => '#f5e6e8',         // Soft pink
                        'background' => '#fffbf7',     // Warm cream
                    ],
                    'fonts' => [
                        'heading' => 'Dancing Script',  // Romantic script
                        'body' => 'Lora',               // Elegant serif
                    ],
                    'layout' => [
                        'heroStyle' => 'centered',
                        'navigationStyle' => 'tabs',
                        'showCountdown' => true,
                        'showGallery' => true,
                    ],
                    'decorations' => [
                        'backgroundPattern' => 'floral',
                        'headerImage' => '/images/Wedding/romantic-hero.jpg',
                        'borderStyle' => 'delicate',
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
