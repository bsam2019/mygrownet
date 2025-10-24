<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        // Create Categories
        $categories = [
            [
                'name' => 'Learning Materials',
                'slug' => 'learning-materials',
                'description' => 'E-books, courses, and educational content',
                'icon' => '📚',
                'sort_order' => 1,
            ],
            [
                'name' => 'Business Tools',
                'slug' => 'business-tools',
                'description' => 'Tools and resources for your business',
                'icon' => '🛠️',
                'sort_order' => 2,
            ],
            [
                'name' => 'Wellness Products',
                'slug' => 'wellness-products',
                'description' => 'Health and wellness items',
                'icon' => '💪',
                'sort_order' => 3,
            ],
            [
                'name' => 'Digital Services',
                'slug' => 'digital-services',
                'description' => 'Online services and subscriptions',
                'icon' => '💻',
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $categoryData) {
            ProductCategory::create($categoryData);
        }

        // Create Products
        $products = [
            // Learning Materials
            [
                'category_id' => 1,
                'name' => 'Financial Literacy E-Book',
                'slug' => 'financial-literacy-ebook',
                'description' => 'Comprehensive guide to personal finance management, investing, and wealth building. Perfect for beginners and intermediate learners.',
                'price' => 150.00,
                'bp_value' => 10,
                'stock_quantity' => 999,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => 1,
                'name' => 'Entrepreneurship Masterclass',
                'slug' => 'entrepreneurship-masterclass',
                'description' => 'Complete video course on starting and growing your own business. Includes templates and worksheets.',
                'price' => 500.00,
                'bp_value' => 10,
                'stock_quantity' => 999,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => 1,
                'name' => 'Digital Marketing Guide',
                'slug' => 'digital-marketing-guide',
                'description' => 'Learn social media marketing, content creation, and online advertising strategies.',
                'price' => 200.00,
                'bp_value' => 10,
                'stock_quantity' => 999,
                'sort_order' => 3,
            ],
            
            // Business Tools
            [
                'category_id' => 2,
                'name' => 'Business Plan Template Pack',
                'slug' => 'business-plan-template-pack',
                'description' => 'Professional business plan templates for various industries. Includes financial projections and pitch deck.',
                'price' => 300.00,
                'bp_value' => 10,
                'stock_quantity' => 999,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => 2,
                'name' => 'Marketing Materials Bundle',
                'slug' => 'marketing-materials-bundle',
                'description' => 'Customizable flyers, brochures, and social media templates for your business.',
                'price' => 250.00,
                'bp_value' => 10,
                'stock_quantity' => 999,
                'sort_order' => 2,
            ],
            
            // Wellness Products
            [
                'category_id' => 3,
                'name' => 'Wellness Journal',
                'slug' => 'wellness-journal',
                'description' => 'Track your health, fitness, and personal development goals with this comprehensive journal.',
                'price' => 100.00,
                'bp_value' => 10,
                'stock_quantity' => 50,
                'sort_order' => 1,
            ],
            [
                'category_id' => 3,
                'name' => 'Nutrition Guide & Meal Planner',
                'slug' => 'nutrition-guide-meal-planner',
                'description' => 'Complete nutrition guide with meal planning templates and healthy recipes.',
                'price' => 180.00,
                'bp_value' => 10,
                'stock_quantity' => 999,
                'sort_order' => 2,
            ],
            
            // Digital Services
            [
                'category_id' => 4,
                'name' => 'One-on-One Coaching Session',
                'slug' => 'coaching-session',
                'description' => '60-minute personal coaching session with experienced business mentor.',
                'price' => 400.00,
                'bp_value' => 20,
                'stock_quantity' => 20,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => 4,
                'name' => 'Website Design Consultation',
                'slug' => 'website-design-consultation',
                'description' => 'Professional consultation for your business website design and development.',
                'price' => 600.00,
                'bp_value' => 20,
                'stock_quantity' => 15,
                'sort_order' => 2,
            ],
            [
                'category_id' => 4,
                'name' => 'Social Media Management (1 Month)',
                'slug' => 'social-media-management',
                'description' => 'Professional social media management for your business. Includes content creation and posting.',
                'price' => 800.00,
                'bp_value' => 20,
                'stock_quantity' => 10,
                'sort_order' => 3,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
