<?php

namespace Database\Seeders;

use App\Models\MarketplaceCategory;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceSeller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Marketplace Demo Data...');

        // Ensure categories exist
        $this->call(MarketplaceCategorySeeder::class);

        // Create demo sellers
        $sellers = $this->createSellers();
        
        // Create demo products
        $this->createProducts($sellers);

        $this->command->info('Marketplace demo data seeded successfully!');
    }

    private function createSellers(): array
    {
        $sellerData = [
            [
                'business_name' => 'TechZone Zambia',
                'business_type' => 'registered',
                'province' => 'Lusaka',
                'district' => 'Lusaka Central',
                'phone' => '+260971234567',
                'email' => 'techzone@example.com',
                'description' => 'Your one-stop shop for quality electronics and gadgets. We offer genuine products with warranty.',
                'trust_level' => 'verified',
                'kyc_status' => 'approved',
                'total_orders' => 156,
                'rating' => 4.8,
            ],
            [
                'business_name' => 'Fashion Hub ZM',
                'business_type' => 'registered',
                'province' => 'Copperbelt',
                'district' => 'Kitwe',
                'phone' => '+260972345678',
                'email' => 'fashionhub@example.com',
                'description' => 'Trendy fashion for men, women and kids. Quality clothing at affordable prices.',
                'trust_level' => 'trusted',
                'kyc_status' => 'approved',
                'total_orders' => 342,
                'rating' => 4.9,
            ],
            [
                'business_name' => 'Fresh Farm Produce',
                'business_type' => 'individual',
                'province' => 'Southern',
                'district' => 'Livingstone',
                'phone' => '+260973456789',
                'email' => 'freshfarm@example.com',
                'description' => 'Farm-fresh vegetables, fruits and organic produce delivered to your doorstep.',
                'trust_level' => 'verified',
                'kyc_status' => 'approved',
                'total_orders' => 89,
                'rating' => 4.7,
            ],
            [
                'business_name' => 'HomeStyle Decor',
                'business_type' => 'registered',
                'province' => 'Lusaka',
                'district' => 'Chilanga',
                'phone' => '+260974567890',
                'email' => 'homestyle@example.com',
                'description' => 'Beautiful home decor, furniture and garden accessories to transform your space.',
                'trust_level' => 'top',
                'kyc_status' => 'approved',
                'total_orders' => 523,
                'rating' => 4.9,
            ],
            [
                'business_name' => 'Beauty Essentials',
                'business_type' => 'individual',
                'province' => 'Copperbelt',
                'district' => 'Ndola',
                'phone' => '+260975678901',
                'email' => 'beautyess@example.com',
                'description' => 'Premium skincare, makeup and beauty products. All products are genuine and imported.',
                'trust_level' => 'verified',
                'kyc_status' => 'approved',
                'total_orders' => 201,
                'rating' => 4.6,
            ],
            [
                'business_name' => 'Sports World ZM',
                'business_type' => 'registered',
                'province' => 'Lusaka',
                'district' => 'Lusaka East',
                'phone' => '+260976789012',
                'email' => 'sportsworld@example.com',
                'description' => 'Sports equipment, fitness gear and outdoor adventure supplies.',
                'trust_level' => 'new',
                'kyc_status' => 'approved',
                'total_orders' => 34,
                'rating' => 4.5,
            ],
        ];

        $sellers = [];
        
        foreach ($sellerData as $index => $data) {
            // Create or get a user for this seller
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['business_name'] . ' Owner',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'account_type' => 'member',
                    'account_types' => ['member'],
                ]
            );

            $seller = MarketplaceSeller::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($data, [
                    'is_active' => true,
                    'kyc_documents' => ['nrc_front' => 'demo/nrc_front.jpg', 'nrc_back' => 'demo/nrc_back.jpg'],
                ])
            );

            $sellers[$data['business_name']] = $seller;
        }

        return $sellers;
    }

    private function createProducts(array $sellers): void
    {
        $categories = MarketplaceCategory::all()->keyBy('name');

        $products = [
            // Electronics
            [
                'seller' => 'TechZone Zambia',
                'category' => 'Electronics',
                'name' => 'Samsung Galaxy A54 5G - 128GB',
                'description' => 'Brand new Samsung Galaxy A54 5G smartphone with 128GB storage, 8GB RAM. Features a stunning 6.4" Super AMOLED display, 50MP triple camera system, and 5000mAh battery. Comes with 1 year warranty.',
                'price' => 850000,
                'compare_price' => 950000,
                'stock' => 15,
                'featured' => true,
            ],
            [
                'seller' => 'TechZone Zambia',
                'category' => 'Electronics',
                'name' => 'JBL Flip 6 Bluetooth Speaker',
                'description' => 'Portable waterproof Bluetooth speaker with powerful sound. IP67 waterproof and dustproof rating. 12 hours of playtime. Perfect for outdoor adventures.',
                'price' => 180000,
                'compare_price' => null,
                'stock' => 25,
                'featured' => true,
            ],
            [
                'seller' => 'TechZone Zambia',
                'category' => 'Electronics',
                'name' => 'HP Laptop 15.6" Intel Core i5',
                'description' => 'HP 15.6" laptop with Intel Core i5 processor, 8GB RAM, 512GB SSD. Windows 11 Home. Perfect for work and study.',
                'price' => 1250000,
                'compare_price' => 1400000,
                'stock' => 8,
                'featured' => true,
            ],
            [
                'seller' => 'TechZone Zambia',
                'category' => 'Electronics',
                'name' => 'Wireless Earbuds TWS Pro',
                'description' => 'True wireless earbuds with active noise cancellation. 30 hours total battery life with charging case. Touch controls and voice assistant support.',
                'price' => 45000,
                'compare_price' => 65000,
                'stock' => 50,
                'featured' => false,
            ],

            // Fashion
            [
                'seller' => 'Fashion Hub ZM',
                'category' => 'Fashion',
                'name' => 'Men\'s Casual Polo Shirt - Navy Blue',
                'description' => 'Premium cotton polo shirt for men. Comfortable fit, breathable fabric. Available in sizes M, L, XL, XXL. Perfect for casual and semi-formal occasions.',
                'price' => 25000,
                'compare_price' => 35000,
                'stock' => 100,
                'featured' => true,
            ],
            [
                'seller' => 'Fashion Hub ZM',
                'category' => 'Fashion',
                'name' => 'Women\'s Ankara Print Dress',
                'description' => 'Beautiful African print dress made from quality Ankara fabric. Elegant design suitable for parties and special occasions. Available in various sizes.',
                'price' => 45000,
                'compare_price' => null,
                'stock' => 30,
                'featured' => true,
            ],
            [
                'seller' => 'Fashion Hub ZM',
                'category' => 'Fashion',
                'name' => 'Unisex Sneakers - White',
                'description' => 'Comfortable white sneakers suitable for both men and women. Durable sole, breathable upper. Sizes 36-45 available.',
                'price' => 35000,
                'compare_price' => 45000,
                'stock' => 60,
                'featured' => false,
            ],
            [
                'seller' => 'Fashion Hub ZM',
                'category' => 'Fashion',
                'name' => 'Kids School Uniform Set',
                'description' => 'Complete school uniform set including shirt and trousers/skirt. Durable fabric that withstands frequent washing. Various sizes for primary school children.',
                'price' => 18000,
                'compare_price' => null,
                'stock' => 200,
                'featured' => false,
            ],

            // Food & Groceries
            [
                'seller' => 'Fresh Farm Produce',
                'category' => 'Food & Groceries',
                'name' => 'Fresh Tomatoes - 5kg Box',
                'description' => 'Farm-fresh ripe tomatoes, perfect for cooking. Locally grown without pesticides. Delivered within 24 hours of harvest.',
                'price' => 8000,
                'compare_price' => null,
                'stock' => 50,
                'featured' => true,
            ],
            [
                'seller' => 'Fresh Farm Produce',
                'category' => 'Food & Groceries',
                'name' => 'Organic Honey - 1 Litre',
                'description' => 'Pure organic honey from rural Zambia. No additives or preservatives. Rich in natural nutrients and antioxidants.',
                'price' => 12000,
                'compare_price' => 15000,
                'stock' => 40,
                'featured' => true,
            ],
            [
                'seller' => 'Fresh Farm Produce',
                'category' => 'Food & Groceries',
                'name' => 'Mixed Vegetables Bundle',
                'description' => 'Assorted fresh vegetables including cabbage, carrots, onions, and green peppers. Perfect for a week\'s cooking.',
                'price' => 15000,
                'compare_price' => null,
                'stock' => 30,
                'featured' => false,
            ],

            // Home & Garden
            [
                'seller' => 'HomeStyle Decor',
                'category' => 'Home & Garden',
                'name' => 'Modern 3-Seater Sofa - Grey',
                'description' => 'Elegant modern sofa with premium fabric upholstery. Comfortable cushioning, sturdy wooden frame. Dimensions: 200cm x 85cm x 80cm.',
                'price' => 450000,
                'compare_price' => 550000,
                'stock' => 5,
                'featured' => true,
            ],
            [
                'seller' => 'HomeStyle Decor',
                'category' => 'Home & Garden',
                'name' => 'LED Ceiling Light - Crystal Design',
                'description' => 'Beautiful crystal LED ceiling light. Modern design with remote control for brightness adjustment. Energy efficient, long-lasting.',
                'price' => 85000,
                'compare_price' => null,
                'stock' => 20,
                'featured' => true,
            ],
            [
                'seller' => 'HomeStyle Decor',
                'category' => 'Home & Garden',
                'name' => 'Garden Tool Set - 5 Pieces',
                'description' => 'Complete garden tool set including trowel, fork, rake, pruner, and gloves. Durable steel construction with comfortable grips.',
                'price' => 25000,
                'compare_price' => 32000,
                'stock' => 35,
                'featured' => false,
            ],
            [
                'seller' => 'HomeStyle Decor',
                'category' => 'Home & Garden',
                'name' => 'Decorative Wall Clock - Vintage',
                'description' => 'Vintage-style wall clock with Roman numerals. Silent movement, no ticking noise. Diameter: 40cm. Perfect for living room or office.',
                'price' => 18000,
                'compare_price' => null,
                'stock' => 25,
                'featured' => false,
            ],

            // Health & Beauty
            [
                'seller' => 'Beauty Essentials',
                'category' => 'Health & Beauty',
                'name' => 'Vitamin C Serum - 30ml',
                'description' => 'Premium Vitamin C serum for brighter, healthier skin. Reduces dark spots and fine lines. Suitable for all skin types.',
                'price' => 35000,
                'compare_price' => 45000,
                'stock' => 45,
                'featured' => true,
            ],
            [
                'seller' => 'Beauty Essentials',
                'category' => 'Health & Beauty',
                'name' => 'Natural Hair Growth Oil - 100ml',
                'description' => 'Blend of natural oils for hair growth and strengthening. Contains castor oil, coconut oil, and essential oils. Promotes healthy hair.',
                'price' => 15000,
                'compare_price' => null,
                'stock' => 60,
                'featured' => true,
            ],
            [
                'seller' => 'Beauty Essentials',
                'category' => 'Health & Beauty',
                'name' => 'Makeup Brush Set - 12 Pieces',
                'description' => 'Professional makeup brush set with soft synthetic bristles. Includes brushes for foundation, powder, eyeshadow, and more. Comes with carrying case.',
                'price' => 28000,
                'compare_price' => 38000,
                'stock' => 30,
                'featured' => false,
            ],

            // Sports & Outdoors
            [
                'seller' => 'Sports World ZM',
                'category' => 'Sports & Outdoors',
                'name' => 'Football - Official Size 5',
                'description' => 'High-quality football suitable for matches and training. Durable PU leather, excellent grip. FIFA standard size and weight.',
                'price' => 12000,
                'compare_price' => null,
                'stock' => 40,
                'featured' => true,
            ],
            [
                'seller' => 'Sports World ZM',
                'category' => 'Sports & Outdoors',
                'name' => 'Yoga Mat - 6mm Thick',
                'description' => 'Non-slip yoga mat with carrying strap. 6mm thickness for comfort. Suitable for yoga, pilates, and floor exercises.',
                'price' => 18000,
                'compare_price' => 25000,
                'stock' => 35,
                'featured' => false,
            ],
            [
                'seller' => 'Sports World ZM',
                'category' => 'Sports & Outdoors',
                'name' => 'Camping Tent - 4 Person',
                'description' => 'Waterproof camping tent for 4 people. Easy setup, includes rain fly and ground sheet. Perfect for weekend camping trips.',
                'price' => 95000,
                'compare_price' => 120000,
                'stock' => 10,
                'featured' => true,
            ],
        ];

        // Sample product images (using placeholder URLs)
        $imagesByCategory = [
            'Electronics' => [
                'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=500&h=500&fit=crop',
            ],
            'Fashion' => [
                'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1539008835657-9e8e9680c956?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=500&h=500&fit=crop',
            ],
            'Food & Groceries' => [
                'https://images.unsplash.com/photo-1546470427-e26264be0b11?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=500&h=500&fit=crop',
            ],
            'Home & Garden' => [
                'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?w=500&h=500&fit=crop',
            ],
            'Health & Beauty' => [
                'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1526947425960-945c6e72858f?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=500&h=500&fit=crop',
            ],
            'Sports & Outdoors' => [
                'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=500&h=500&fit=crop',
                'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=500&h=500&fit=crop',
            ],
        ];

        $imageIndex = [];

        foreach ($products as $productData) {
            $seller = $sellers[$productData['seller']] ?? null;
            $category = $categories[$productData['category']] ?? null;

            if (!$seller || !$category) {
                continue;
            }

            // Get image for this category
            $catName = $productData['category'];
            if (!isset($imageIndex[$catName])) {
                $imageIndex[$catName] = 0;
            }
            $images = $imagesByCategory[$catName] ?? [];
            $imageUrl = $images[$imageIndex[$catName] % count($images)] ?? null;
            $imageIndex[$catName]++;

            $slug = Str::slug($productData['name']);
            $existingCount = MarketplaceProduct::where('slug', 'like', $slug . '%')->count();
            if ($existingCount > 0) {
                $slug .= '-' . ($existingCount + 1);
            }

            MarketplaceProduct::updateOrCreate(
                ['slug' => $slug],
                [
                    'seller_id' => $seller->id,
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'slug' => $slug,
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'compare_price' => $productData['compare_price'],
                    'stock_quantity' => $productData['stock'],
                    'images' => $imageUrl ? [$imageUrl] : [],
                    'status' => 'active',
                    'is_featured' => $productData['featured'],
                    'views' => rand(10, 500),
                ]
            );
        }
    }
}
