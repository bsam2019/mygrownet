<?php

namespace Database\Seeders;

use App\Models\GrowMart\GrowMartCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GrowMartCategorySeeder extends Seeder
{
    private int $sortOrder = 0;

    public function run(): void
    {
        $this->command->info('Seeding GrowMart categories...');

        $this->sortOrder = 0;

        $parents = [
            $this->parent('Fresh Produce', 'Fresh fruits, vegetables, and leafy greens from local farms', [
                'Fruits', 'Vegetables', 'Leafy Greens',
            ]),
            $this->parent('Staples', 'Essential pantry staples including mealie meal, rice, flour, and more', [
                'Mealie Meal', 'Rice', 'Flour', 'Pasta', 'Beans', 'Lentils', 'Sugar', 'Salt',
            ]),
            $this->parent('Protein', 'Fresh meat, poultry, fish, and eggs', [
                'Beef', 'Chicken', 'Pork', 'Fish & Seafood', 'Eggs',
            ]),
            $this->parent('Dairy & Breakfast', 'Milk, cheese, yogurt, cereals, tea, and coffee', [
                'Milk', 'Milk Powder', 'Butter & Margarine', 'Cheese', 'Yogurt',
                'Breakfast Cereals', 'Tea', 'Coffee',
            ]),
            $this->parent('Cooking Ingredients', 'Oils, spices, stock cubes, sauces, and tomato paste', [
                'Cooking Oil', 'Spices & Seasonings', 'Stock Cubes', 'Tomato Paste', 'Sauces',
            ]),
            $this->parent('Local Foods', 'Traditional Zambian foods including kapenta, dry fish, and groundnuts', [
                'Kapenta', 'Dry Fish', 'Groundnuts', 'Traditional Vegetables', 'Cassava Products',
            ]),
            $this->parent('Snacks & Ready-to-Eat', 'Crisps, biscuits, sweets, instant noodles, and ready meals', [
                'Chips & Crisps', 'Biscuits', 'Chocolate & Sweets', 'Instant Noodles', 'Ready Meals',
            ]),
            $this->parent('Beverages', 'Water, soft drinks, juices, and energy drinks', [
                'Water', 'Soft Drinks', 'Juices', 'Energy Drinks',
            ]),
            $this->parent('Household Essentials', 'Cleaning products, laundry detergent, dishwashing, and toilet paper', [
                'Cleaning Products', 'Laundry Detergent', 'Dishwashing', 'Toilet Paper',
            ]),
            $this->parent('Personal Care & Baby', 'Soap, shampoo, oral care, baby diapers, food, and formula', [
                'Soap', 'Shampoo', 'Oral Care', 'Baby Diapers', 'Baby Food', 'Baby Formula',
            ]),
            $this->parent('Frozen & Chilled', 'Frozen meat, frozen vegetables, and ice cream', [
                'Frozen Meat', 'Frozen Vegetables', 'Ice Cream',
            ]),
            $this->parent('Bulk & Family Packs', 'Large quantity packs for savings', [
                '25kg Mealie Meal', 'Bulk Rice', 'Bulk Sugar', 'Large Cooking Oil Containers',
            ]),
        ];

        // Create "Other" as catch-all
        GrowMartCategory::updateOrCreate(
            ['slug' => 'other'],
            [
                'name' => 'Other',
                'description' => 'Uncategorized products',
                'sort_order' => 999,
                'is_active' => true,
            ]
        );

        $this->command->info('GrowMart categories seeded! (' . count($parents) . ' parent categories + children + Other)');
    }

    private function image(string $categorySlug): ?string
    {
        $images = [
            'fresh-produce' => 'https://images.unsplash.com/photo-1566385101042-1a0b68c2c3f3?w=400&h=400&fit=crop',
            'staples' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop',
            'protein' => 'https://images.unsplash.com/photo-1603048297172-925d76ba8b1b?w=400&h=400&fit=crop',
            'dairy-breakfast' => 'https://images.unsplash.com/photo-1628088062854-b1870b58a5b0?w=400&h=400&fit=crop',
            'cooking-ingredients' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=400&fit=crop',
            'local-foods' => 'https://images.unsplash.com/photo-1534080564583-6be75777b70a?w=400&h=400&fit=crop',
            'snacks-ready-to-eat' => 'https://images.unsplash.com/photo-1621934239195-9be7c6f51434?w=400&h=400&fit=crop',
            'beverages' => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400&h=400&fit=crop',
            'household-essentials' => 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=400&h=400&fit=crop',
            'personal-care-baby' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=400&h=400&fit=crop',
            'frozen-chilled' => 'https://images.unsplash.com/photo-1583258292688-d0213dc5a3a8?w=400&h=400&fit=crop',
            'bulk-family-packs' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=400&fit=crop',
        ];
        return $images[$categorySlug] ?? null;
    }

    private function parent(string $name, string $description, array $children): GrowMartCategory
    {
        $this->sortOrder++;
        $slug = Str::slug($name);

        $parent = GrowMartCategory::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $description,
                'image' => $this->image($slug),
                'sort_order' => $this->sortOrder,
                'is_active' => true,
            ]
        );

        foreach ($children as $childName) {
            GrowMartCategory::firstOrCreate(
                ['slug' => Str::slug($childName)],
                [
                    'name' => $childName,
                    'parent_id' => $parent->id,
                    'sort_order' => 1,
                    'is_active' => true,
                ]
            );
        }

        return $parent;
    }
}
