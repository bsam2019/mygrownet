<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorageProductSeeder extends Seeder
{
    public function run(): void
    {
        // Note: This seeder creates storage products for GrowNet MLM integration
        // Products link to storage_plans and enable commission earning
        
        $categoryId = DB::table('product_categories')->where('slug', 'storage')->value('id');
        
        if (!$categoryId) {
            $categoryId = DB::table('product_categories')->insertGetId([
                'name' => 'Cloud Storage',
                'slug' => 'storage',
                'description' => 'Secure cloud storage solutions',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $products = [
            [
                'name' => 'GrowBackup Starter',
                'slug' => 'growbackup-starter',
                'description' => '2GB secure cloud storage - Perfect for personal use. Includes: folder organization, direct downloads, 25MB max file size.',
                'category_id' => $categoryId,
                'price' => 50.00,
                'bp_value' => 50, // Business Points value
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GrowBackup Basic',
                'slug' => 'growbackup-basic',
                'description' => '20GB secure cloud storage with sharing capabilities. Includes: all Starter features plus file sharing, 100MB max file size.',
                'category_id' => $categoryId,
                'price' => 150.00,
                'bp_value' => 150,
                'is_active' => true,
                'is_featured' => true, // Most popular
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GrowBackup Growth',
                'slug' => 'growbackup-growth',
                'description' => '100GB storage with team folders and versioning. Includes: all Basic features plus team collaboration, file versioning, 500MB max file size.',
                'category_id' => $categoryId,
                'price' => 500.00,
                'bp_value' => 500,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GrowBackup Pro',
                'slug' => 'growbackup-pro',
                'description' => '500GB premium storage with priority support. Includes: all Growth features plus priority support, 2GB max file size, advanced features.',
                'category_id' => $categoryId,
                'price' => 1500.00,
                'bp_value' => 1500,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['slug' => $product['slug']],
                $product
            );
        }
        
        $this->command->info('Storage products seeded successfully!');
    }
}
