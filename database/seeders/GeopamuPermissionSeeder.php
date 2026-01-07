<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class GeopamuPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permission with slug
        // This permission is ONLY for Geopamu admin access
        // Do NOT assign to admin role to keep systems separate
        Permission::firstOrCreate(
            ['name' => 'manage-geopamu'],
            [
                'guard_name' => 'web',
                'slug' => 'manage-geopamu'
            ]
        );

        $this->command->info('Geopamu permission created (not assigned to any role by default).');
        $this->command->info('To grant access: $user->givePermissionTo("manage-geopamu");');
    }
}
