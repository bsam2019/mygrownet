<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CommissionPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create commission-related permissions
        $permissions = [
            'calculate-commissions' => 'Calculate employee commissions',
            'create-commissions' => 'Create commission records',
            'approve-commissions' => 'Approve commission payments',
            'mark-commissions-paid' => 'Mark commissions as paid',
            'viewCommissionAnalytics' => 'View commission analytics and reports',
            'generate-commission-reports' => 'Generate commission reports',
            'manage-commission-settings' => 'Manage commission calculation settings',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        // Field Agent permissions
        $fieldAgentRole = Role::firstOrCreate(['name' => 'field-agent']);
        $fieldAgentRole->givePermissionTo([
            'calculate-commissions',
            'create-commissions',
        ]);

        // Manager permissions
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'calculate-commissions',
            'create-commissions',
            'approve-commissions',
            'mark-commissions-paid',
            'viewCommissionAnalytics',
            'generate-commission-reports',
        ]);

        // HR Manager permissions
        $hrManagerRole = Role::firstOrCreate(['name' => 'hr-manager']);
        $hrManagerRole->givePermissionTo([
            'calculate-commissions',
            'create-commissions',
            'approve-commissions',
            'mark-commissions-paid',
            'viewCommissionAnalytics',
            'generate-commission-reports',
            'manage-commission-settings',
        ]);

        // Admin permissions (all commission permissions)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::where('name', 'like', '%commission%')->pluck('name'));
    }
}