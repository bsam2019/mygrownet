<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ApplicationPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create new permissions for job applications
        $permissions = [
            'review-applications',
            'hire-applicants',
            'manage-job-postings',
            'invite-employees',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);

        // Assign some permissions to manager role
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'review-applications',
            'invite-employees',
        ]);

        // Create HR role with specific permissions
        $hrRole = Role::firstOrCreate(['name' => 'hr']);
        $hrRole->givePermissionTo([
            'review-applications',
            'hire-applicants',
            'manage-job-postings',
            'invite-employees',
            'view-employees',
            'create-employees',
            'edit-employees',
        ]);

        $this->command->info('Application permissions seeded successfully!');
    }
}
