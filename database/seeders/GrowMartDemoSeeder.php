<?php

namespace Database\Seeders;

use App\Models\GrowMart\GrowMartCategory;
use App\Models\GrowMart\GrowMartInventory;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartProductImage;
use App\Models\GrowMart\GrowMartWarehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GrowMartDemoSeeder extends Seeder
{
    private array $categoryCache = [];

    public function run(): void
    {
        $this->command->info('Seeding GrowMart demo data...');

        $hasExistingProducts = GrowMartProduct::count() > 0;
        $hasOldCategories = GrowMartCategory::whereIn('slug', [
            'fresh-vegetables', 'fruits', 'dairy-eggs', 'meat-poultry',
        ])->exists();

        if ($hasExistingProducts && $hasOldCategories) {
            $this->command->info('Existing categories detected — migrating products to new structure...');
            $this->migrateExistingData();
        } else {
            $this->command->info('Fresh install — seeding all data...');
            $this->freshInstall();
        }

        $this->command->info('GrowMart demo data seeded successfully!');
    }

    private function migrateExistingData(): void
    {
        $other = $this->findOrCreateCategory('other', 'Other', null, 999);

        GrowMartProduct::query()->update(['category_id' => $other->id]);

        $oldSlugs = [
            'fresh-vegetables', 'fruits', 'dairy-eggs', 'meat-poultry',
            'fish-seafood', 'pantry-staples', 'beverages', 'snacks-treats',
            'frozen-foods', 'household-essentials', 'baby-kids',
        ];
        GrowMartCategory::whereIn('slug', $oldSlugs)->delete();

        $this->call(GrowMartCategorySeeder::class);

        $this->loadCategoryCache();
        $mapping = $this->getProductNameToCategorySlugMapping();

        foreach (GrowMartProduct::all() as $product) {
            $targetSlug = null;
            foreach ($mapping as $pattern => $slug) {
                if (stripos($product->name, $pattern) !== false) {
                    $targetSlug = $slug;
                    break;
                }
            }

            $newCat = $targetSlug ? ($this->categoryCache[$targetSlug] ?? null) : null;
            if ($newCat) {
                $product->update(['category_id' => $newCat->id]);
            }
        }

        $this->createAdditionalDemoProducts();

        $uncounted = GrowMartProduct::where('category_id', $other->id)->count();
        if ($uncounted > 0) {
            $this->command->warn("{$uncounted} products assigned to 'Other' — no matching category found.");
        }
    }

    private function freshInstall(): void
    {
        $warehouse = $this->createWarehouse();

        $this->call(GrowMartCategorySeeder::class);
        $this->loadCategoryCache();

        $this->createAllDemoProducts($warehouse);

        $this->command->info('Warehouse and demo inventory created.');
    }

    private function loadCategoryCache(): void
    {
        $this->categoryCache = GrowMartCategory::all()->keyBy('slug')->all();
    }

    private function findOrCreateCategory(string $slug, string $name, ?int $parentId, int $sortOrder): GrowMartCategory
    {
        return GrowMartCategory::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'parent_id' => $parentId,
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]
        );
    }

    private function slug(string $name): string
    {
        return Str::slug($name);
    }

    private function createWarehouse(): GrowMartWarehouse
    {
        return GrowMartWarehouse::firstOrCreate(
            ['name' => 'Lusaka Main Warehouse'],
            [
                'province' => 'Lusaka',
                'city' => 'Lusaka',
                'address' => 'Plot 123, Great East Road, Lusaka',
                'is_active' => true,
            ]
        );
    }

    private function product(string $categorySlug, string $name, string $unit, int $price, ?int $comparePrice, int $stock, string $image): array
    {
        return compact('categorySlug', 'name', 'unit', 'price', 'comparePrice', 'stock', 'image');
    }

    private function createProduct(array $data, GrowMartWarehouse $warehouse): void
    {
        $category = $this->categoryCache[$data['categorySlug']] ?? null;
        if (!$category) {
            $this->command->warn("Category slug '{$data['categorySlug']}' not found, skipping {$data['name']}");
            return;
        }

        $slug = Str::slug($data['name']);
        $existingCount = GrowMartProduct::where('slug', 'like', $slug . '%')->count();
        if ($existingCount > 0) {
            $slug .= '-' . ($existingCount + 1);
        }

        $product = GrowMartProduct::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $data['name'],
                'description' => "Fresh {$data['name']} — perfect for your daily meals. Sourced fresh and delivered to your door in Lusaka.",
                'unit' => $data['unit'],
                'price' => $data['price'],
                'compare_price' => $data['comparePrice'],
                'category_id' => $category->id,
                'status' => 'active',
            ]
        );

        if ($data['image']) {
            GrowMartProductImage::firstOrCreate(
                ['product_id' => $product->id, 'path' => $data['image']],
                ['sort_order' => 0]
            );
        }

        GrowMartInventory::firstOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
            ['quantity' => $data['stock'], 'low_stock_threshold' => 10]
        );
    }

    private function createAllDemoProducts(GrowMartWarehouse $warehouse): void
    {
        $products = $this->getFreshInstallProducts();

        foreach ($products as $data) {
            $this->createProduct($data, $warehouse);
        }
    }

    private function createAdditionalDemoProducts(): void
    {
        $warehouse = $this->createWarehouse();

        $products = $this->getAdditionalProducts();

        foreach ($products as $data) {
            $this->createProduct($data, $warehouse);
        }
    }

    /**
     * Product name keywords => new category slug mapping for migration.
     * Checked in order — first match wins.
     */
    private function getProductNameToCategorySlugMapping(): array
    {
        return [
            // Fresh Produce
            'Rape' => 'leafy-greens',
            'Chibwabwa' => 'leafy-greens',
            'Cabbage' => 'leafy-greens',
            'Bondwe' => 'leafy-greens',
            'Pumpkin Leaves' => 'leafy-greens',
            'Chinese Cabbage' => 'leafy-greens',

            'Potatoes' => 'vegetables',
            'Sweet Potatoes' => 'vegetables',
            'Onions' => 'vegetables',
            'Carrots' => 'vegetables',
            'Tomatoes' => 'vegetables',

            'Mangoes' => 'fruits',
            'Bananas' => 'fruits',
            'Pineapple' => 'fruits',
            'Pawpaw' => 'fruits',
            'Oranges' => 'fruits',
            'Lemons' => 'fruits',
            'Tangerines' => 'fruits',
            'Apples' => 'fruits',
            'Grapes' => 'fruits',
            'Watermelon' => 'fruits',

            // Staples
            'Mealie Meal' => 'mealie-meal',
            'Breakfast' => 'mealie-meal',
            'Rice' => 'rice',
            'Wheat Flour' => 'flour',
            'Spaghetti' => 'pasta',
            'Mixed Beans' => 'beans',
            'Cowpeas' => 'beans',
            'Lentils' => 'lentils',
            'Sugar' => 'sugar',
            'Salt' => 'salt',

            // Protein
            'Beef Mince' => 'beef',
            'Beef Stewing' => 'beef',
            'T-Bone' => 'beef',
            'Chicken' => 'chicken',
            'Pork Chops' => 'pork',
            'Pork Sausages' => 'pork',
            'Tilapia' => 'fish-seafood',
            'Bream' => 'fish-seafood',
            'Mackerel' => 'fish-seafood',
            'Fish Fingers' => 'fish-seafood',
            'Eggs' => 'eggs',
            'Free Range Eggs' => 'eggs',

            // Dairy & Breakfast
            'Fresh Milk' => 'milk',
            'Powdered Milk' => 'milk-powder',
            'Cheddar' => 'cheese',
            'Yogurt' => 'yogurt',
            'Coffee' => 'coffee',
            'Tea' => 'tea',
            'Hot Chocolate' => 'coffee',

            // Cooking Ingredients
            'Cooking Oil' => 'cooking-oil',
            'Olive Oil' => 'cooking-oil',
            'Curry Powder' => 'spices-seasonings',
            'Royco' => 'stock-cubes',
            'Stock Cubes' => 'stock-cubes',
            'Tomato Sauce' => 'sauces',
            'Chopped Tomatoes' => 'tomato-paste',
            'Tomato Paste' => 'tomato-paste',
            'Baked Beans' => 'beans',
            'Sweetcorn' => 'vegetables',
            'Pilchards' => 'fish-seafood',

            // Local Foods
            'Kapenta' => 'kapenta',
            'Groundnuts' => 'groundnuts',
            'Raw Shelled' => 'groundnuts',

            // Snacks
            'Crisps' => 'chips-crisps',
            'Tortilla Chips' => 'chips-crisps',
            'Popcorn' => 'chips-crisps',
            'Potato Crisps' => 'chips-crisps',
            'Cream Crackers' => 'biscuits',
            'Cookies' => 'biscuits',
            'Shortbread' => 'biscuits',
            'Chocolate Bar' => 'chocolate-sweets',
            'Chocolate & Sweets' => 'chocolate-sweets',
            'Fruit Sweets' => 'chocolate-sweets',
            'Gum' => 'chocolate-sweets',
            'Chicken Nuggets' => 'frozen-meat',

            // Beverages
            'Coca-Cola' => 'soft-drinks',
            'Fanta' => 'soft-drinks',
            'Sprite' => 'soft-drinks',
            'Mango Juice' => 'juices',
            'Still Water' => 'water',
            'Sparkling Water' => 'water',

            // Household
            'Laundry Detergent' => 'laundry-detergent',
            'Bleach' => 'cleaning-products',
            'Dishwashing' => 'dishwashing',
            'Toilet Tissue' => 'toilet-paper',
            'Paper Towels' => 'toilet-paper',
            'Facial Tissues' => 'toilet-paper',

            // Personal Care
            'Bath Soap' => 'soap',
            'Shampoo' => 'shampoo',
            'Toothpaste' => 'oral-care',
            'Deodorant' => 'soap',

            // Baby
            'Diapers' => 'baby-diapers',
            'Baby Cereal' => 'baby-food',
            'Baby Wipes' => 'baby-diapers',
            'Baby Lotion' => 'baby-diapers',

            // Frozen
            'Frozen Mixed Vegetables' => 'frozen-vegetables',
            'Ice Cream' => 'ice-cream',
        ];
    }

    /**
     * Full product list for fresh install — assigned to new category slugs directly.
     */
    private function getFreshInstallProducts(): array
    {
        return [
            // --- Fresh Produce > Leafy Greens ---
            $this->product('leafy-greens', 'Fresh Rape (1 bunch)', 'bunch', 1500, 2000, 80, 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400&h=400&fit=crop'),
            $this->product('leafy-greens', 'Pumpkin Leaves — Chibwabwa (1 bunch)', 'bunch', 1200, null, 60, 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400&h=400&fit=crop'),
            $this->product('leafy-greens', 'Chinese Cabbage (1 head)', 'head', 1800, 2500, 50, 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400&h=400&fit=crop'),
            $this->product('leafy-greens', 'Bondwe — Amaranthus (1 bunch)', 'bunch', 1000, null, 40, 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400&h=400&fit=crop'),

            // --- Fresh Produce > Vegetables ---
            $this->product('vegetables', 'Irish Potatoes (2kg)', '2kg bag', 3500, 4500, 100, 'https://images.unsplash.com/photo-1518977676601-b53f82aba902?w=400&h=400&fit=crop'),
            $this->product('vegetables', 'Sweet Potatoes (2kg)', '2kg bag', 3000, null, 70, 'https://images.unsplash.com/photo-1518977676601-b53f82aba902?w=400&h=400&fit=crop'),
            $this->product('vegetables', 'Onions (1kg)', 'kg', 2500, 3000, 120, 'https://images.unsplash.com/photo-1518977676601-b53f82aba902?w=400&h=400&fit=crop'),
            $this->product('vegetables', 'Carrots (1kg)', 'kg', 2000, null, 90, 'https://images.unsplash.com/photo-1518977676601-b53f82aba902?w=400&h=400&fit=crop'),
            $this->product('vegetables', 'Tomatoes (1kg)', 'kg', 2800, 3500, 110, 'https://images.unsplash.com/photo-1518977676601-b53f82aba902?w=400&h=400&fit=crop'),

            // --- Fresh Produce > Fruits ---
            $this->product('fruits', 'Mangoes (1kg)', 'kg', 3000, 4000, 40, 'https://images.unsplash.com/photo-1553279768-865429fa0078?w=400&h=400&fit=crop'),
            $this->product('fruits', 'Sweet Bananas (1 bunch)', 'bunch', 2000, null, 70, 'https://images.unsplash.com/photo-1553279768-865429fa0078?w=400&h=400&fit=crop'),
            $this->product('fruits', 'Pineapple — Whole', 'piece', 3500, 4500, 25, 'https://images.unsplash.com/photo-1553279768-865429fa0078?w=400&h=400&fit=crop'),
            $this->product('fruits', 'Pawpaw — Large', 'piece', 2500, null, 20, 'https://images.unsplash.com/photo-1553279768-865429fa0078?w=400&h=400&fit=crop'),
            $this->product('fruits', 'Valencia Oranges (1kg)', 'kg', 2200, 3000, 60, 'https://images.unsplash.com/photo-1547514701-42782101795e?w=400&h=400&fit=crop'),
            $this->product('fruits', 'Lemons (500g)', '500g', 1500, null, 50, 'https://images.unsplash.com/photo-1547514701-42782101795e?w=400&h=400&fit=crop'),
            $this->product('fruits', 'Tangerines (1kg)', 'kg', 2800, 3500, 40, 'https://images.unsplash.com/photo-1547514701-42782101795e?w=400&h=400&fit=crop'),
            $this->product('fruits', 'Green Apples (1kg)', 'kg', 4500, 5500, 35, 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400&h=400&fit=crop'),
            $this->product('fruits', 'Red Grapes (500g)', '500g', 3800, null, 25, 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400&h=400&fit=crop'),
            $this->product('fruits', 'Watermelon — Whole', 'piece', 5000, 6500, 15, 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400&h=400&fit=crop'),

            // --- Staples ---
            $this->product('mealie-meal', 'Mealie Meal Breakfast (25kg)', '25kg', 25000, 30000, 30, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
            $this->product('mealie-meal', 'Mealie Meal Breakfast (10kg)', '10kg', 11000, null, 50, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
            $this->product('rice', 'Long Grain Rice (2kg)', '2kg', 4500, 5500, 80, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
            $this->product('flour', 'Wheat Flour (2kg)', '2kg', 3500, null, 55, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
            $this->product('pasta', 'Spaghetti Pasta (500g)', '500g', 1500, 2000, 60, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
            $this->product('beans', 'Mixed Beans (1kg)', 'kg', 2200, null, 60, 'https://images.unsplash.com/photo-1515548002142-e166747c6e1c?w=400&h=400&fit=crop'),
            $this->product('beans', 'Cowpeas — Nyemba (1kg)', 'kg', 2500, 3200, 45, 'https://images.unsplash.com/photo-1515548002142-e166747c6e1c?w=400&h=400&fit=crop'),
            $this->product('sugar', 'White Sugar (2kg)', '2kg', 3500, 4500, 80, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
            $this->product('salt', 'Table Salt (1kg)', '1kg', 800, null, 100, 'https://images.unsplash.com/photo-1596040033229-98268daa2826?w=400&h=400&fit=crop'),

            // --- Protein ---
            $this->product('beef', 'Beef Mince (500g)', '500g', 5500, 6500, 40, 'https://images.unsplash.com/photo-1603048297172-925d76ba8b1b?w=400&h=400&fit=crop'),
            $this->product('beef', 'Beef Stewing (1kg)', 'kg', 8500, null, 30, 'https://images.unsplash.com/photo-1603048297172-925d76ba8b1b?w=400&h=400&fit=crop'),
            $this->product('beef', 'T-Bone Steak (2 pieces)', '2 pieces', 12000, 15000, 20, 'https://images.unsplash.com/photo-1603048297172-925d76ba8b1b?w=400&h=400&fit=crop'),
            $this->product('chicken', 'Whole Fresh Chicken (1.5–2kg)', 'piece', 8500, 10000, 35, 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=400&h=400&fit=crop'),
            $this->product('chicken', 'Chicken Thighs (1kg)', 'kg', 6500, null, 40, 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=400&h=400&fit=crop'),
            $this->product('chicken', 'Chicken Wings (1kg)', 'kg', 5500, 7000, 45, 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=400&h=400&fit=crop'),
            $this->product('pork', 'Pork Chops (500g)', '500g', 6000, 7500, 25, 'https://images.unsplash.com/photo-1559847844-5315695dadae?w=400&h=400&fit=crop'),
            $this->product('pork', 'Pork Sausages (500g)', '500g', 5000, null, 35, 'https://images.unsplash.com/photo-1559847844-5315695dadae?w=400&h=400&fit=crop'),
            $this->product('fish-seafood', 'Whole Tilapia — Fresh (500–800g)', 'piece', 4000, 5000, 30, 'https://images.unsplash.com/photo-1534080564583-6be75777b70a?w=400&h=400&fit=crop'),
            $this->product('fish-seafood', 'Bream Fillet (500g)', '500g', 6500, null, 20, 'https://images.unsplash.com/photo-1534080564583-6be75777b70a?w=400&h=400&fit=crop'),
            $this->product('fish-seafood', 'Smoked Mackerel (200g)', '200g', 2800, null, 40, 'https://images.unsplash.com/photo-1534080564583-6be75777b70a?w=400&h=400&fit=crop'),
            $this->product('eggs', 'Free Range Eggs (6 pack)', '6 pack', 2500, 3200, 100, 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=400&h=400&fit=crop'),
            $this->product('eggs', 'Free Range Eggs (12 pack)', '12 pack', 4500, null, 80, 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=400&h=400&fit=crop'),

            // --- Dairy & Breakfast ---
            $this->product('milk', 'Fresh Full Cream Milk (2L)', '2L', 3500, null, 60, 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400&h=400&fit=crop'),
            $this->product('milk', 'Fresh Full Cream Milk (1L)', '1L', 2000, 2500, 80, 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400&h=400&fit=crop'),
            $this->product('milk-powder', 'Powdered Milk (500g)', '500g', 4500, null, 40, 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400&h=400&fit=crop'),
            $this->product('cheese', 'Cheddar Cheese Block (250g)', '250g', 5500, 7000, 30, 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400&h=400&fit=crop'),
            $this->product('yogurt', 'Plain Yogurt (1L)', '1L', 2800, null, 45, 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400&h=400&fit=crop'),
            $this->product('yogurt', 'Strawberry Flavoured Yogurt (500ml)', '500ml', 1800, 2200, 50, 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400&h=400&fit=crop'),
            $this->product('coffee', 'Instant Coffee (100g)', '100g', 5500, 6500, 35, 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400&h=400&fit=crop'),
            $this->product('tea', 'Black Tea Bags (100 pack)', '100 pack', 2500, null, 60, 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400&h=400&fit=crop'),

            // --- Cooking Ingredients ---
            $this->product('cooking-oil', 'Vegetable Cooking Oil (2L)', '2L', 5500, 6500, 70, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop'),
            $this->product('cooking-oil', 'Vegetable Cooking Oil (1L)', '1L', 3000, null, 90, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop'),
            $this->product('cooking-oil', 'Extra Virgin Olive Oil (500ml)', '500ml', 8500, 10000, 25, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop'),
            $this->product('spices-seasonings', 'Curry Powder (100g)', '100g', 1500, null, 50, 'https://images.unsplash.com/photo-1596040033229-98268daa2826?w=400&h=400&fit=crop'),
            $this->product('stock-cubes', 'Royco Beef Stock Cubes (12 pack)', '12 pack', 1200, 1500, 120, 'https://images.unsplash.com/photo-1596040033229-98268daa2826?w=400&h=400&fit=crop'),
            $this->product('tomato-paste', 'Chopped Tomatoes (400g can)', '400g can', 1200, 1600, 70, 'https://images.unsplash.com/photo-1578164252939-c00163b2fdbc?w=400&h=400&fit=crop'),
            $this->product('sauces', 'Tomato Sauce (500ml)', '500ml', 1800, 2500, 70, 'https://images.unsplash.com/photo-1596040033229-98268daa2826?w=400&h=400&fit=crop'),

            // --- Local Foods ---
            $this->product('kapenta', 'Dry Kapenta (250g)', '250g', 3500, 4500, 50, 'https://images.unsplash.com/photo-1534080564583-6be75777b70a?w=400&h=400&fit=crop'),
            $this->product('groundnuts', 'Raw Shelled Groundnuts (500g)', '500g', 1800, null, 50, 'https://images.unsplash.com/photo-1515548002142-e166747c6e1c?w=400&h=400&fit=crop'),

            // --- Snacks & Ready-to-Eat ---
            $this->product('chips-crisps', 'Salted Potato Crisps (150g)', '150g', 1500, 2000, 100, 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=400&fit=crop'),
            $this->product('chips-crisps', 'Tortilla Chips (200g)', '200g', 1800, null, 70, 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=400&fit=crop'),
            $this->product('chips-crisps', 'Microwave Popcorn (100g)', '100g', 1200, 1500, 60, 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=400&fit=crop'),
            $this->product('biscuits', 'Cream Crackers (250g)', '250g', 1500, 2000, 80, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop'),
            $this->product('biscuits', 'Chocolate Cookies (200g)', '200g', 2000, null, 60, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop'),
            $this->product('biscuits', 'Shortbread Biscuits (150g)', '150g', 1800, 2200, 50, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop'),
            $this->product('chocolate-sweets', 'Milk Chocolate Bar (100g)', '100g', 2500, 3200, 80, 'https://images.unsplash.com/photo-1621934239195-9be7c6f51434?w=400&h=400&fit=crop'),
            $this->product('chocolate-sweets', 'Mixed Fruit Sweets (200g)', '200g', 1200, null, 90, 'https://images.unsplash.com/photo-1621934239195-9be7c6f51434?w=400&h=400&fit=crop'),

            // --- Beverages ---
            $this->product('soft-drinks', 'Coca-Cola (2L)', '2L', 2500, 3000, 100, 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=400&h=400&fit=crop'),
            $this->product('soft-drinks', 'Fanta Orange (2L)', '2L', 2500, null, 80, 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=400&h=400&fit=crop'),
            $this->product('soft-drinks', 'Sprite (2L)', '2L', 2500, 3000, 90, 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=400&h=400&fit=crop'),
            $this->product('juices', 'Mango Juice (1L)', '1L', 2200, null, 50, 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=400&h=400&fit=crop'),
            $this->product('water', 'Still Bottled Water (1.5L)', '1.5L', 1200, 1500, 150, 'https://images.unsplash.com/photo-1523362628745-0c100150b504?w=400&h=400&fit=crop'),
            $this->product('water', 'Still Bottled Water (500ml)', '500ml', 800, null, 200, 'https://images.unsplash.com/photo-1523362628745-0c100150b504?w=400&h=400&fit=crop'),
            $this->product('water', 'Sparkling Water (1L)', '1L', 1800, 2200, 40, 'https://images.unsplash.com/photo-1523362628745-0c100150b504?w=400&h=400&fit=crop'),

            // --- Household Essentials ---
            $this->product('laundry-detergent', 'Laundry Detergent (2kg)', '2kg', 6000, 7500, 60, 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop'),
            $this->product('cleaning-products', 'Household Bleach (1L)', '1L', 1500, 2000, 80, 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop'),
            $this->product('dishwashing', 'Dishwashing Liquid (500ml)', '500ml', 2000, null, 70, 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop'),
            $this->product('toilet-paper', 'Toilet Tissue (12 roll)', '12 pack', 7500, 9000, 50, 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop'),
            $this->product('toilet-paper', 'Paper Towels (6 roll)', '6 pack', 4500, null, 40, 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop'),
            $this->product('toilet-paper', 'Facial Tissues (3 pack)', '3 pack', 2500, 3000, 45, 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop'),

            // --- Personal Care & Baby ---
            $this->product('soap', 'Bath Soap (4 pack)', '4 pack', 2500, 3000, 80, 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop'),
            $this->product('shampoo', 'Shampoo (500ml)', '500ml', 4500, 5500, 40, 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop'),
            $this->product('oral-care', 'Toothpaste (100ml)', '100ml', 2000, null, 60, 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop'),
            $this->product('baby-diapers', 'Baby Diapers (30 pack — Size 4)', '30 pack', 15000, 18000, 30, 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400&h=400&fit=crop'),
            $this->product('baby-food', 'Baby Cereal (250g)', '250g', 3500, null, 40, 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400&h=400&fit=crop'),
            $this->product('baby-diapers', 'Baby Wipes (80 pack)', '80 pack', 2500, 3200, 50, 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400&h=400&fit=crop'),

            // --- Frozen & Chilled ---
            $this->product('frozen-vegetables', 'Frozen Mixed Vegetables (1kg)', '1kg', 3500, 4500, 40, 'https://images.unsplash.com/photo-1583258292688-d0213dc5a3a8?w=400&h=400&fit=crop'),
            $this->product('frozen-meat', 'Frozen Chicken Nuggets (500g)', '500g', 4500, 5500, 35, 'https://images.unsplash.com/photo-1583258292688-d0213dc5a3a8?w=400&h=400&fit=crop'),
            $this->product('ice-cream', 'Vanilla Ice Cream (2L)', '2L', 5000, null, 25, 'https://images.unsplash.com/photo-1583258292688-d0213dc5a3a8?w=400&h=400&fit=crop'),
            $this->product('frozen-meat', 'Frozen Fish Fingers (400g)', '400g', 3800, 4800, 30, 'https://images.unsplash.com/photo-1583258292688-d0213dc5a3a8?w=400&h=400&fit=crop'),

            // --- Bulk & Family Packs ---
            $this->product('25kg-mealie-meal', 'Mealie Meal Breakfast (25kg)', '25kg', 25000, 30000, 30, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
            $this->product('bulk-rice', 'Long Grain Rice (10kg)', '10kg', 20000, 25000, 20, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
            $this->product('bulk-sugar', 'White Sugar (10kg)', '10kg', 15000, 18000, 15, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop'),
            $this->product('large-cooking-oil-containers', 'Vegetable Cooking Oil (5L)', '5L', 12000, 15000, 25, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop'),
        ];
    }

    private function getAdditionalProducts(): array
    {
        return [
            // Fill gaps for categories that had no products after migration
            $this->product('lentils', 'Red Lentils (1kg)', '1kg', 2000, 2800, 40, 'https://images.unsplash.com/photo-1515548002142-e166747c6e1c?w=400&h=400&fit=crop'),
            $this->product('butter-margarine', 'Butter (500g)', '500g', 4500, 5500, 30, 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400&h=400&fit=crop'),
            $this->product('butter-margarine', 'Margarine Spread (500g)', '500g', 2500, null, 35, 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400&h=400&fit=crop'),
            $this->product('breakfast-cereals', 'Corn Flakes (500g)', '500g', 4500, 5500, 40, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop'),
            $this->product('breakfast-cereals', 'Oats (1kg)', '1kg', 3500, null, 35, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop'),
            $this->product('traditional-vegetables', 'Dried Pumpkin Leaves (200g)', '200g', 1500, 2000, 30, 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400&h=400&fit=crop'),
            $this->product('cassava-products', 'Cassava Flour (2kg)', '2kg', 3000, 4000, 25, 'https://images.unsplash.com/photo-1518977676601-b53f82aba902?w=400&h=400&fit=crop'),
            $this->product('instant-noodles', 'Instant Noodles (5 pack)', '5 pack', 1500, 2000, 100, 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=400&fit=crop'),
            $this->product('ready-meals', 'Canned Beef Stew (400g)', '400g can', 2500, 3200, 40, 'https://images.unsplash.com/photo-1578164252939-c00163b2fdbc?w=400&h=400&fit=crop'),
            $this->product('energy-drinks', 'Energy Drink (250ml)', '250ml', 1500, 2000, 60, 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=400&h=400&fit=crop'),
            $this->product('baby-formula', 'Baby Formula Stage 1 (400g)', '400g', 12000, 15000, 20, 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400&h=400&fit=crop'),
            $this->product('dry-fish', 'Dried Bream (500g)', '500g', 4000, 5000, 30, 'https://images.unsplash.com/photo-1534080564583-6be75777b70a?w=400&h=400&fit=crop'),
        ];
    }
}
