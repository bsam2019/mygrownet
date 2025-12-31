<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            ['slug' => 'products.view', 'name' => 'View Products', 'description' => 'Can view products list'],
            ['slug' => 'products.create', 'name' => 'Create Products', 'description' => 'Can create new products'],
            ['slug' => 'products.edit', 'name' => 'Edit Products', 'description' => 'Can edit existing products'],
            ['slug' => 'products.delete', 'name' => 'Delete Products', 'description' => 'Can delete products'],
        ];

        foreach ($permissions as $permission) {
            // Check if permission already exists
            $exists = DB::table('site_permissions')
                ->where('slug', $permission['slug'])
                ->exists();

            if (!$exists) {
                DB::table('site_permissions')->insert([
                    'slug' => $permission['slug'],
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('site_permissions')
            ->whereIn('slug', ['products.view', 'products.create', 'products.edit', 'products.delete'])
            ->delete();
    }
};
