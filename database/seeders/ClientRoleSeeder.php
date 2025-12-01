<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ClientRoleSeeder extends Seeder
{
    /**
     * Create the Client role for non-MLM users.
     */
    public function run(): void
    {
        // Create Client role if it doesn't exist
        $clientRole = Role::firstOrCreate(
            ['name' => 'Client', 'guard_name' => 'web'],
            ['name' => 'Client', 'guard_name' => 'web', 'slug' => 'client']
        );

        // Create Business role for SME users
        $businessRole = Role::firstOrCreate(
            ['name' => 'Business', 'guard_name' => 'web'],
            ['name' => 'Business', 'guard_name' => 'web', 'slug' => 'business']
        );

        // Create basic permissions for clients
        $clientPermissions = [
            'view marketplace',
            'purchase products',
            'view venture builder',
            'invest in ventures',
            'manage profile',
            'view wallet',
        ];

        foreach ($clientPermissions as $permissionName) {
            $slug = \Illuminate\Support\Str::slug($permissionName);
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['name' => $permissionName, 'guard_name' => 'web', 'slug' => $slug]
            );
            
            // Assign to both Client and Business roles
            if (!$clientRole->hasPermissionTo($permission)) {
                $clientRole->givePermissionTo($permission);
            }
            if (!$businessRole->hasPermissionTo($permission)) {
                $businessRole->givePermissionTo($permission);
            }
        }

        // Additional permissions for Business role
        $businessPermissions = [
            'access accounting',
            'manage staff',
            'manage tasks',
            'view reports',
        ];

        foreach ($businessPermissions as $permissionName) {
            $slug = \Illuminate\Support\Str::slug($permissionName);
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['name' => $permissionName, 'guard_name' => 'web', 'slug' => $slug]
            );
            
            if (!$businessRole->hasPermissionTo($permission)) {
                $businessRole->givePermissionTo($permission);
            }
        }

        $this->command->info('Client and Business roles created successfully.');
    }
}
