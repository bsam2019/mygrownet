<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BizBoostDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Get a demo user (create one if needed)
        $demoUser = DB::table('users')->where('email', 'demo@bizboost.test')->first();
        
        if (!$demoUser) {
            $demoUserId = DB::table('users')->insertGetId([
                'name' => 'Demo Business Owner',
                'email' => 'demo@bizboost.test',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $demoUserId = $demoUser->id;
        }

        // Create demo business
        $businessId = DB::table('bizboost_businesses')->insertGetId([
            'user_id' => $demoUserId,
            'name' => 'Lusaka Fashion Boutique',
            'slug' => 'lusaka-fashion-boutique',
            'description' => 'Your one-stop shop for trendy African fashion and accessories. We bring you the latest styles from across the continent.',
            'industry' => 'boutique',
            'address' => 'Shop 12, Manda Hill Mall',
            'city' => 'Lusaka',
            'province' => 'Lusaka',
            'country' => 'Zambia',
            'phone' => '+260 97 123 4567',
            'whatsapp' => '+260 97 123 4567',
            'email' => 'info@lusakafashion.com',
            'timezone' => 'Africa/Lusaka',
            'locale' => 'en',
            'currency' => 'ZMW',
            'social_links' => json_encode([
                'facebook' => 'https://facebook.com/lusakafashion',
                'instagram' => 'https://instagram.com/lusakafashion',
            ]),
            'business_hours' => json_encode([
                'monday' => ['09:00', '18:00'],
                'tuesday' => ['09:00', '18:00'],
                'wednesday' => ['09:00', '18:00'],
                'thursday' => ['09:00', '18:00'],
                'friday' => ['09:00', '18:00'],
                'saturday' => ['09:00', '15:00'],
                'sunday' => null,
            ]),
            'is_active' => true,
            'onboarding_completed' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create business profile
        DB::table('bizboost_business_profiles')->insert([
            'business_id' => $businessId,
            'about' => 'Welcome to Lusaka Fashion Boutique! We specialize in authentic African fashion, from traditional chitenge to modern Afro-fusion styles. Our mission is to celebrate African beauty and craftsmanship.',
            'tagline' => 'Wear Your Heritage with Pride',
            'contact_email' => 'info@lusakafashion.com',
            'show_products' => true,
            'show_contact_form' => true,
            'show_whatsapp_button' => true,
            'show_social_links' => true,
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create demo products
        $products = [
            [
                'name' => 'Chitenge Maxi Dress',
                'sku' => 'CMD-001',
                'description' => 'Beautiful floor-length dress made from premium chitenge fabric. Perfect for weddings and special occasions.',
                'price' => 450.00,
                'sale_price' => 380.00,
                'category' => 'Dresses',
                'stock_quantity' => 15,
                'is_featured' => true,
            ],
            [
                'name' => 'African Print Shirt',
                'sku' => 'APS-002',
                'description' => 'Stylish men\'s shirt with vibrant African print. Comfortable cotton blend.',
                'price' => 250.00,
                'category' => 'Men',
                'stock_quantity' => 25,
                'is_featured' => true,
            ],
            [
                'name' => 'Beaded Necklace Set',
                'sku' => 'BNS-003',
                'description' => 'Handcrafted beaded necklace and earring set. Traditional Zambian design.',
                'price' => 120.00,
                'category' => 'Accessories',
                'stock_quantity' => 30,
                'is_featured' => false,
            ],
            [
                'name' => 'Ankara Headwrap',
                'sku' => 'AHW-004',
                'description' => 'Versatile ankara fabric headwrap. Multiple styling options.',
                'price' => 85.00,
                'category' => 'Accessories',
                'stock_quantity' => 50,
                'is_featured' => false,
            ],
            [
                'name' => 'Kente Clutch Bag',
                'sku' => 'KCB-005',
                'description' => 'Elegant clutch bag with authentic kente fabric accent.',
                'price' => 180.00,
                'category' => 'Bags',
                'stock_quantity' => 20,
                'is_featured' => true,
            ],
        ];

        foreach ($products as $product) {
            DB::table('bizboost_products')->insert(array_merge($product, [
                'business_id' => $businessId,
                'currency' => 'ZMW',
                'track_inventory' => true,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create demo customers
        $customers = [
            ['name' => 'Grace Mwanza', 'phone' => '+260 97 111 1111', 'email' => 'grace@example.com', 'source' => 'walk-in'],
            ['name' => 'Chanda Banda', 'phone' => '+260 97 222 2222', 'email' => 'chanda@example.com', 'source' => 'referral'],
            ['name' => 'Mutale Phiri', 'phone' => '+260 97 333 3333', 'email' => 'mutale@example.com', 'source' => 'social'],
            ['name' => 'Bwalya Tembo', 'phone' => '+260 97 444 4444', 'source' => 'walk-in'],
            ['name' => 'Natasha Zulu', 'phone' => '+260 97 555 5555', 'email' => 'natasha@example.com', 'source' => 'social'],
        ];

        foreach ($customers as $customer) {
            DB::table('bizboost_customers')->insert(array_merge($customer, [
                'business_id' => $businessId,
                'is_active' => true,
                'total_spent' => rand(100, 2000),
                'total_orders' => rand(1, 10),
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now(),
            ]));
        }

        // Create customer tags
        $tags = [
            ['name' => 'VIP', 'color' => '#8b5cf6'],
            ['name' => 'Regular', 'color' => '#3b82f6'],
            ['name' => 'New', 'color' => '#10b981'],
            ['name' => 'Wholesale', 'color' => '#f59e0b'],
        ];

        foreach ($tags as $tag) {
            DB::table('bizboost_customer_tags')->insert(array_merge($tag, [
                'business_id' => $businessId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create demo posts
        $posts = [
            [
                'title' => 'New Collection Alert!',
                'caption' => '🎉 Our new chitenge collection has arrived! Beautiful patterns, premium quality. Visit us today at Manda Hill Mall. #LusakaFashion #AfricanFashion #Chitenge',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'platform_targets' => json_encode(['facebook', 'instagram']),
            ],
            [
                'title' => 'Weekend Sale',
                'caption' => '🔥 WEEKEND SALE! 20% off all dresses this Saturday and Sunday. Don\'t miss out! #Sale #Fashion #Lusaka',
                'status' => 'scheduled',
                'scheduled_at' => now()->addDays(2),
                'platform_targets' => json_encode(['facebook', 'instagram']),
            ],
            [
                'title' => 'Customer Spotlight',
                'caption' => 'Thank you to our amazing customer Grace for rocking our Chitenge Maxi Dress! You look stunning! 💃 #CustomerLove #AfricanBeauty',
                'status' => 'draft',
                'platform_targets' => json_encode(['instagram']),
            ],
        ];

        foreach ($posts as $post) {
            DB::table('bizboost_posts')->insert(array_merge($post, [
                'business_id' => $businessId,
                'post_type' => 'standard',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create demo sales
        $productIds = DB::table('bizboost_products')
            ->where('business_id', $businessId)
            ->pluck('id', 'name')
            ->toArray();

        $customerIds = DB::table('bizboost_customers')
            ->where('business_id', $businessId)
            ->pluck('id')
            ->toArray();

        $sales = [
            ['product_name' => 'Chitenge Maxi Dress', 'quantity' => 1, 'unit_price' => 380, 'days_ago' => 1],
            ['product_name' => 'African Print Shirt', 'quantity' => 2, 'unit_price' => 250, 'days_ago' => 2],
            ['product_name' => 'Beaded Necklace Set', 'quantity' => 1, 'unit_price' => 120, 'days_ago' => 3],
            ['product_name' => 'Kente Clutch Bag', 'quantity' => 1, 'unit_price' => 180, 'days_ago' => 5],
            ['product_name' => 'Ankara Headwrap', 'quantity' => 3, 'unit_price' => 85, 'days_ago' => 7],
            ['product_name' => 'Chitenge Maxi Dress', 'quantity' => 1, 'unit_price' => 450, 'days_ago' => 10],
        ];

        foreach ($sales as $sale) {
            $productId = $productIds[$sale['product_name']] ?? null;
            DB::table('bizboost_sales')->insert([
                'business_id' => $businessId,
                'customer_id' => $customerIds[array_rand($customerIds)] ?? null,
                'product_id' => $productId,
                'product_name' => $sale['product_name'],
                'quantity' => $sale['quantity'],
                'unit_price' => $sale['unit_price'],
                'total_amount' => $sale['quantity'] * $sale['unit_price'],
                'currency' => 'ZMW',
                'sale_date' => now()->subDays($sale['days_ago']),
                'payment_method' => ['cash', 'mobile_money'][rand(0, 1)],
                'source' => 'manual',
                'created_at' => now()->subDays($sale['days_ago']),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('BizBoost demo data seeded successfully!');
        $this->command->info("Demo login: demo@bizboost.test / password");
    }
}
