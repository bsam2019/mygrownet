<?php

namespace Database\Seeders;

use App\Domain\Employee\Constants\DelegatedPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DelegatedPermissionsSeeder extends Seeder
{
    /**
     * Seed the delegated permissions into the permissions table.
     * This allows them to be managed through the standard role/permission system.
     */
    public function run(): void
    {
        $permissions = DelegatedPermissions::getAllPermissions();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['slug' => str_replace('.', '-', $permission)]
            );
        }

        $this->command->info('Created ' . count($permissions) . ' delegated permissions.');
    }
}
