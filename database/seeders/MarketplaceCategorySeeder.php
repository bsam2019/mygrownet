<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Hierarchical categories: Main categories with subcategories
        // Using African-themed images where possible
        $categories = [
            // 1. Electronics & Technology
            [
                'name' => 'Electronics',
                'icon' => '📱',
                'image_url' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&h=300&fit=crop', // African person with phone
                'description' => 'Phones, laptops, TVs, and electronic gadgets',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Phones & Tablets', 'icon' => '📲', 'description' => 'Smartphones, tablets, and mobile accessories'],
                    ['name' => 'Computers & Laptops', 'icon' => '💻', 'description' => 'Desktops, laptops, and computer accessories'],
                    ['name' => 'TVs & Audio', 'icon' => '📺', 'description' => 'Televisions, speakers, and sound systems'],
                    ['name' => 'Cameras & Photography', 'icon' => '📷', 'description' => 'Cameras, lenses, and photography equipment'],
                    ['name' => 'Gaming', 'icon' => '🎮', 'description' => 'Gaming consoles, games, and accessories'],
                    ['name' => 'Accessories', 'icon' => '🔌', 'description' => 'Chargers, cables, cases, and electronic accessories'],
                ],
            ],
            
            // 2. Fashion & Apparel
            [
                'name' => 'Fashion',
                'icon' => '👗',
                'image_url' => 'https://images.unsplash.com/photo-1590735213920-68192a487bc2?w=400&h=300&fit=crop', // African fashion/chitenge
                'description' => 'Clothing, shoes, and fashion accessories',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'African Fashion', 'icon' => '🌍', 'description' => 'Chitenge, African prints, and traditional attire'],
                    ['name' => "Men's Clothing", 'icon' => '👔', 'description' => 'Shirts, trousers, suits, and menswear'],
                    ['name' => "Women's Clothing", 'icon' => '👚', 'description' => 'Dresses, blouses, skirts, and womenswear'],
                    ['name' => 'Shoes & Footwear', 'icon' => '👟', 'description' => 'Shoes, sandals, boots, and slippers'],
                    ['name' => 'Bags & Luggage', 'icon' => '👜', 'description' => 'Handbags, backpacks, and travel bags'],
                    ['name' => 'Jewelry & Watches', 'icon' => '💍', 'description' => 'Necklaces, bracelets, watches, and accessories'],
                ],
            ],
            
            // 3. Home & Living
            [
                'name' => 'Home & Living',
                'icon' => '🏠',
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=300&fit=crop', // Modern African home
                'description' => 'Furniture, decor, and home essentials',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Furniture', 'icon' => '🛋️', 'description' => 'Sofas, beds, tables, chairs, and cabinets'],
                    ['name' => 'Kitchen & Dining', 'icon' => '🍳', 'description' => 'Cookware, utensils, and dining essentials'],
                    ['name' => 'Bedding & Bath', 'icon' => '🛏️', 'description' => 'Bedsheets, towels, and bathroom accessories'],
                    ['name' => 'Home Decor', 'icon' => '🖼️', 'description' => 'Wall art, curtains, rugs, and decorations'],
                    ['name' => 'Lighting', 'icon' => '💡', 'description' => 'Lamps, bulbs, and lighting fixtures'],
                    ['name' => 'Storage & Organization', 'icon' => '📦', 'description' => 'Shelves, boxes, and storage solutions'],
                ],
            ],

            // 4. Health & Beauty
            [
                'name' => 'Health & Beauty',
                'icon' => '💄',
                'image_url' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=400&h=300&fit=crop', // African beauty
                'description' => 'Skincare, makeup, and personal care',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Skincare', 'icon' => '🧴', 'description' => 'Lotions, creams, and skincare treatments'],
                    ['name' => 'Hair Care', 'icon' => '💇', 'description' => 'Shampoo, wigs, braids, and hair products'],
                    ['name' => 'Makeup & Cosmetics', 'icon' => '💋', 'description' => 'Lipstick, foundation, and beauty products'],
                    ['name' => 'Fragrances', 'icon' => '🌸', 'description' => 'Perfumes, colognes, and body sprays'],
                    ['name' => 'Health & Wellness', 'icon' => '💊', 'description' => 'Vitamins, supplements, and health products'],
                    ['name' => 'Personal Care', 'icon' => '🧼', 'description' => 'Soap, deodorant, and hygiene products'],
                ],
            ],
            
            // 5. Food & Groceries
            [
                'name' => 'Food & Groceries',
                'icon' => '🍎',
                'image_url' => 'https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=400&h=300&fit=crop', // African market produce
                'description' => 'Fresh produce, packaged foods, and beverages',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Fresh Produce', 'icon' => '🥬', 'description' => 'Fresh fruits, vegetables, and herbs'],
                    ['name' => 'Meat & Poultry', 'icon' => '🍖', 'description' => 'Fresh meat, chicken, and fish'],
                    ['name' => 'Grains & Cereals', 'icon' => '🌾', 'description' => 'Mealie meal, rice, flour, and cereals'],
                    ['name' => 'Beverages', 'icon' => '🥤', 'description' => 'Drinks, juices, water, and soft drinks'],
                    ['name' => 'Snacks & Confectionery', 'icon' => '🍪', 'description' => 'Chips, biscuits, sweets, and snacks'],
                    ['name' => 'Cooking Essentials', 'icon' => '🧂', 'description' => 'Oil, spices, sauces, and condiments'],
                ],
            ],
            
            // 6. Agriculture & Farming
            [
                'name' => 'Agriculture & Farming',
                'icon' => '🌾',
                'image_url' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=400&h=300&fit=crop', // African farming
                'description' => 'Seeds, fertilizers, equipment, and livestock',
                'sort_order' => 6,
                'children' => [
                    ['name' => 'Seeds & Seedlings', 'icon' => '🌱', 'description' => 'Vegetable seeds, crop seeds, and seedlings'],
                    ['name' => 'Fertilizers & Chemicals', 'icon' => '🧪', 'description' => 'Fertilizers, pesticides, and farm chemicals'],
                    ['name' => 'Farm Equipment', 'icon' => '🚜', 'description' => 'Tractors, ploughs, and farming tools'],
                    ['name' => 'Irrigation & Water', 'icon' => '💧', 'description' => 'Pumps, pipes, and irrigation systems'],
                    ['name' => 'Livestock & Poultry', 'icon' => '🐄', 'description' => 'Cattle, goats, chickens, and pigs'],
                    ['name' => 'Animal Feed', 'icon' => '🌽', 'description' => 'Poultry feed, cattle feed, and supplements'],
                    ['name' => 'Aquaculture', 'icon' => '🐟', 'description' => 'Fish, fish feed, and aquaculture supplies'],
                    ['name' => 'Beekeeping', 'icon' => '🐝', 'description' => 'Beehives, honey, and beekeeping equipment'],
                ],
            ],
            
            // 7. Building & Construction
            [
                'name' => 'Building & Hardware',
                'icon' => '🔨',
                'image_url' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=400&h=300&fit=crop', // Construction in Africa
                'description' => 'Construction materials, tools, and supplies',
                'sort_order' => 7,
                'children' => [
                    ['name' => 'Building Materials', 'icon' => '🧱', 'description' => 'Cement, bricks, blocks, and roofing'],
                    ['name' => 'Hardware & Tools', 'icon' => '🛠️', 'description' => 'Hand tools, power tools, and hardware'],
                    ['name' => 'Electrical Supplies', 'icon' => '⚡', 'description' => 'Wires, switches, sockets, and electrical items'],
                    ['name' => 'Plumbing', 'icon' => '🚿', 'description' => 'Pipes, fittings, taps, and plumbing supplies'],
                    ['name' => 'Paints & Finishes', 'icon' => '🎨', 'description' => 'Paints, varnishes, and finishing materials'],
                    ['name' => 'Doors & Windows', 'icon' => '🚪', 'description' => 'Doors, windows, frames, and fittings'],
                ],
            ],

            // 8. Automotive
            [
                'name' => 'Automotive',
                'icon' => '🚗',
                'image_url' => 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=400&h=300&fit=crop', // Car mechanic
                'description' => 'Car parts, accessories, and vehicles',
                'sort_order' => 8,
                'children' => [
                    ['name' => 'Car Parts', 'icon' => '🔧', 'description' => 'Engine parts, brakes, and spare parts'],
                    ['name' => 'Tyres & Wheels', 'icon' => '🛞', 'description' => 'Tyres, rims, and wheel accessories'],
                    ['name' => 'Car Accessories', 'icon' => '🪞', 'description' => 'Seat covers, mats, and car accessories'],
                    ['name' => 'Car Electronics', 'icon' => '📻', 'description' => 'Car stereos, GPS, and electronics'],
                    ['name' => 'Motorcycles', 'icon' => '🏍️', 'description' => 'Motorbikes and motorcycle parts'],
                    ['name' => 'Bicycles', 'icon' => '🚲', 'description' => 'Bicycles and cycling accessories'],
                    ['name' => 'Oils & Lubricants', 'icon' => '🛢️', 'description' => 'Engine oil, grease, and lubricants'],
                ],
            ],
            
            // 9. Baby & Kids
            [
                'name' => 'Baby & Kids',
                'icon' => '👶',
                'image_url' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400&h=300&fit=crop', // African child
                'description' => 'Baby products, toys, and children\'s items',
                'sort_order' => 9,
                'children' => [
                    ['name' => 'Baby Care', 'icon' => '🍼', 'description' => 'Diapers, baby food, and baby essentials'],
                    ['name' => 'Baby Clothing', 'icon' => '👕', 'description' => 'Baby clothes, shoes, and accessories'],
                    ['name' => 'Baby Gear', 'icon' => '🚼', 'description' => 'Strollers, car seats, and baby carriers'],
                    ['name' => 'Toys & Games', 'icon' => '🧸', 'description' => 'Children\'s toys, games, and puzzles'],
                    ['name' => 'Kids Clothing', 'icon' => '👧', 'description' => 'Children\'s clothing and school uniforms'],
                    ['name' => 'School Supplies', 'icon' => '✏️', 'description' => 'Books, bags, and school essentials'],
                ],
            ],
            
            // 10. Sports & Outdoors
            [
                'name' => 'Sports & Outdoors',
                'icon' => '⚽',
                'image_url' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=400&h=300&fit=crop', // African football
                'description' => 'Sports equipment and outdoor gear',
                'sort_order' => 10,
                'children' => [
                    ['name' => 'Football & Soccer', 'icon' => '⚽', 'description' => 'Footballs, boots, and soccer gear'],
                    ['name' => 'Fitness & Gym', 'icon' => '🏋️', 'description' => 'Gym equipment and workout gear'],
                    ['name' => 'Camping & Hiking', 'icon' => '⛺', 'description' => 'Tents, sleeping bags, and outdoor gear'],
                    ['name' => 'Swimming', 'icon' => '🏊', 'description' => 'Swimwear, goggles, and pool accessories'],
                    ['name' => 'Cycling', 'icon' => '🚴', 'description' => 'Cycling gear and accessories'],
                    ['name' => 'Other Sports', 'icon' => '🎾', 'description' => 'Tennis, basketball, and other sports'],
                ],
            ],
            
            // 11. Energy & Solar
            [
                'name' => 'Energy & Solar',
                'icon' => '☀️',
                'image_url' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?w=400&h=300&fit=crop', // Solar panels Africa
                'description' => 'Solar panels, batteries, and power solutions',
                'sort_order' => 11,
                'children' => [
                    ['name' => 'Solar Panels', 'icon' => '🔆', 'description' => 'Solar panels and mounting systems'],
                    ['name' => 'Batteries & Inverters', 'icon' => '🔋', 'description' => 'Batteries, inverters, and charge controllers'],
                    ['name' => 'Generators', 'icon' => '⚡', 'description' => 'Petrol, diesel, and gas generators'],
                    ['name' => 'Solar Lighting', 'icon' => '💡', 'description' => 'Solar lamps, street lights, and torches'],
                    ['name' => 'Solar Water Heaters', 'icon' => '🌡️', 'description' => 'Solar geysers and water heating systems'],
                ],
            ],
            
            // 12. Home Appliances
            [
                'name' => 'Home Appliances',
                'icon' => '🔌',
                'image_url' => 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=400&h=300&fit=crop', // Kitchen appliances
                'description' => 'Large and small home appliances',
                'sort_order' => 12,
                'children' => [
                    ['name' => 'Large Appliances', 'icon' => '🧊', 'description' => 'Refrigerators, washing machines, and stoves'],
                    ['name' => 'Small Appliances', 'icon' => '🍞', 'description' => 'Blenders, kettles, irons, and toasters'],
                    ['name' => 'Air Conditioning', 'icon' => '❄️', 'description' => 'ACs, fans, and cooling systems'],
                    ['name' => 'Vacuum & Cleaning', 'icon' => '🧹', 'description' => 'Vacuum cleaners and cleaning appliances'],
                ],
            ],

            // 13. Office & Business
            [
                'name' => 'Office & Business',
                'icon' => '💼',
                'image_url' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786?w=400&h=300&fit=crop', // African business
                'description' => 'Office equipment and business supplies',
                'sort_order' => 13,
                'children' => [
                    ['name' => 'Office Furniture', 'icon' => '🪑', 'description' => 'Desks, chairs, and office furniture'],
                    ['name' => 'Office Equipment', 'icon' => '🖨️', 'description' => 'Printers, copiers, and office machines'],
                    ['name' => 'Stationery', 'icon' => '📝', 'description' => 'Pens, paper, files, and office supplies'],
                    ['name' => 'Business Machines', 'icon' => '🧮', 'description' => 'Cash registers, POS systems, and scales'],
                ],
            ],
            
            // 14. Arts & Crafts
            [
                'name' => 'Arts & Crafts',
                'icon' => '🎨',
                'image_url' => 'https://images.unsplash.com/photo-1590845947670-c009801ffa74?w=400&h=300&fit=crop', // African crafts
                'description' => 'African art, handmade crafts, and creative supplies',
                'sort_order' => 14,
                'children' => [
                    ['name' => 'African Art', 'icon' => '🪘', 'description' => 'Traditional carvings, sculptures, and artwork'],
                    ['name' => 'Paintings & Wall Art', 'icon' => '🖼️', 'description' => 'Paintings, prints, and wall decorations'],
                    ['name' => 'Handmade Crafts', 'icon' => '🧶', 'description' => 'Baskets, pottery, and handcrafted items'],
                    ['name' => 'Craft Supplies', 'icon' => '✂️', 'description' => 'Art supplies, fabrics, and craft materials'],
                ],
            ],
            
            // 15. Books & Media
            [
                'name' => 'Books & Media',
                'icon' => '📚',
                'image_url' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400&h=300&fit=crop', // Books and reading
                'description' => 'Books, music, and educational materials',
                'sort_order' => 15,
                'children' => [
                    ['name' => 'Books', 'icon' => '📖', 'description' => 'Fiction, non-fiction, and educational books'],
                    ['name' => 'Textbooks', 'icon' => '📕', 'description' => 'School and university textbooks'],
                    ['name' => 'Music & Movies', 'icon' => '🎵', 'description' => 'CDs, DVDs, and digital media'],
                    ['name' => 'Musical Instruments', 'icon' => '🎸', 'description' => 'Guitars, drums, and instruments'],
                ],
            ],
            
            // 16. Pets & Animals
            [
                'name' => 'Pets & Animals',
                'icon' => '🐕',
                'image_url' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400&h=300&fit=crop', // Dogs
                'description' => 'Pet food, accessories, and animal supplies',
                'sort_order' => 16,
                'children' => [
                    ['name' => 'Pet Food', 'icon' => '🦴', 'description' => 'Dog food, cat food, and pet treats'],
                    ['name' => 'Pet Accessories', 'icon' => '🐾', 'description' => 'Collars, leashes, and pet supplies'],
                    ['name' => 'Pet Health', 'icon' => '💉', 'description' => 'Pet medicines and health products'],
                ],
            ],
            
            // 17. Security & Safety
            [
                'name' => 'Security & Safety',
                'icon' => '🔒',
                'image_url' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?w=400&h=300&fit=crop', // Security camera
                'description' => 'Security systems and safety equipment',
                'sort_order' => 17,
                'children' => [
                    ['name' => 'CCTV & Cameras', 'icon' => '📹', 'description' => 'Security cameras and surveillance systems'],
                    ['name' => 'Locks & Safes', 'icon' => '🔐', 'description' => 'Padlocks, door locks, and safes'],
                    ['name' => 'Alarms & Sensors', 'icon' => '🚨', 'description' => 'Alarm systems and motion sensors'],
                    ['name' => 'Safety Equipment', 'icon' => '🦺', 'description' => 'Fire extinguishers and safety gear'],
                ],
            ],
            
            // 18. General Merchandise
            [
                'name' => 'General Merchandise',
                'icon' => '🏪',
                'image_url' => 'https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=400&h=300&fit=crop', // Shop/store
                'description' => 'Wholesale goods and general dealer products',
                'sort_order' => 18,
                'children' => [
                    ['name' => 'Wholesale & Bulk', 'icon' => '📦', 'description' => 'Bulk purchases and wholesale items'],
                    ['name' => 'Household Items', 'icon' => '🧺', 'description' => 'Cleaning supplies and household goods'],
                    ['name' => 'Party Supplies', 'icon' => '🎉', 'description' => 'Decorations, balloons, and party items'],
                ],
            ],
            
            // 19. Services
            [
                'name' => 'Services',
                'icon' => '🛠️',
                'image_url' => 'https://images.unsplash.com/photo-1560264280-88b68371db39?w=400&h=300&fit=crop', // Professional services
                'description' => 'Professional services and skilled trades',
                'sort_order' => 19,
                'children' => [
                    ['name' => 'Repairs & Maintenance', 'icon' => '🔧', 'description' => 'Repair services for electronics and appliances'],
                    ['name' => 'Professional Services', 'icon' => '👨‍💼', 'description' => 'Consulting, legal, and business services'],
                    ['name' => 'Home Services', 'icon' => '🏠', 'description' => 'Cleaning, plumbing, and home repairs'],
                    ['name' => 'Transport & Delivery', 'icon' => '🚚', 'description' => 'Delivery and transport services'],
                ],
            ],
        ];

        // Disable foreign key checks and clear existing categories
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('marketplace_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        foreach ($categories as $parentData) {
            $children = $parentData['children'] ?? [];
            unset($parentData['children']);
            
            // Insert parent category
            $parentId = DB::table('marketplace_categories')->insertGetId([
                'parent_id' => null,
                'name' => $parentData['name'],
                'slug' => Str::slug($parentData['name']),
                'icon' => $parentData['icon'],
                'image_url' => $parentData['image_url'] ?? null,
                'description' => $parentData['description'],
                'sort_order' => $parentData['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Insert children
            foreach ($children as $index => $child) {
                DB::table('marketplace_categories')->insert([
                    'parent_id' => $parentId,
                    'name' => $child['name'],
                    'slug' => Str::slug($child['name']),
                    'icon' => $child['icon'],
                    'image_url' => null,
                    'description' => $child['description'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        $totalCategories = DB::table('marketplace_categories')->count();
        $this->command->info("✅ Seeded {$totalCategories} marketplace categories (19 main + subcategories)");
    }
}
