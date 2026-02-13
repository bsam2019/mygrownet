<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\RoleModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GeopamuCmsSeeder extends Seeder
{
    /**
     * Seed Geopamu Investments Limited as pilot CMS tenant
     */
    public function run(): void
    {
        // Create Geopamu company
        $company = CompanyModel::create([
            'name' => 'Geopamu Investments Limited',
            'industry_type' => 'printing_branding',
            'email' => 'info@geopamu.com',
            'phone' => '+260977123456',
            'address' => 'Lusaka, Zambia',
            'city' => 'Lusaka',
            'status' => 'active',
            'settings' => [
                'currency' => 'ZMW',
                'business_hours' => '08:00-17:00',
                'payment_methods' => ['cash', 'mobile_money', 'bank_transfer'],
                'vat_enabled' => true,
                'vat_rate' => 16,
                'invoice_due_days' => 30,
                'approval_thresholds' => [
                    'expense' => 1000,
                    'commission' => 500,
                ],
            ],
        ]);

        $this->command->info("✓ Created company: {$company->name}");

        // Create predefined roles
        $roles = [
            [
                'name' => 'Owner',
                'is_system_role' => true,
                'permissions' => [
                    'company.manage',
                    'users.manage',
                    'roles.manage',
                    'customers.manage',
                    'jobs.manage',
                    'invoices.manage',
                    'payments.manage',
                    'expenses.manage',
                    'expenses.approve',
                    'inventory.manage',
                    'assets.manage',
                    'payroll.manage',
                    'commission.manage',
                    'commission.approve',
                    'reports.view',
                    'documents.manage',
                    'settings.manage',
                ],
                'approval_authority' => ['limit' => 999999999],
            ],
            [
                'name' => 'Admin',
                'is_system_role' => true,
                'permissions' => [
                    'users.manage',
                    'customers.manage',
                    'jobs.manage',
                    'invoices.manage',
                    'payments.manage',
                    'expenses.manage',
                    'inventory.manage',
                    'assets.manage',
                    'reports.view',
                    'documents.manage',
                ],
                'approval_authority' => ['limit' => 10000],
            ],
            [
                'name' => 'Finance',
                'is_system_role' => true,
                'permissions' => [
                    'invoices.manage',
                    'payments.manage',
                    'expenses.view',
                    'expenses.approve',
                    'commission.view',
                    'commission.approve',
                    'reports.view',
                ],
                'approval_authority' => ['limit' => 5000],
            ],
            [
                'name' => 'Staff',
                'is_system_role' => true,
                'permissions' => [
                    'customers.view',
                    'jobs.view',
                    'jobs.create',
                    'jobs.update',
                    'invoices.view',
                    'inventory.view',
                ],
                'approval_authority' => ['limit' => 0],
            ],
            [
                'name' => 'Casual',
                'is_system_role' => true,
                'permissions' => [
                    'jobs.view',
                ],
                'approval_authority' => ['limit' => 0],
            ],
        ];

        foreach ($roles as $roleData) {
            $role = RoleModel::create([
                'company_id' => $company->id,
                'name' => $roleData['name'],
                'is_system_role' => $roleData['is_system_role'],
                'permissions' => $roleData['permissions'],
                'approval_authority' => $roleData['approval_authority'],
            ]);

            $this->command->info("  ✓ Created role: {$role->name}");
        }

        // Create or find owner user in main users table
        // Use unguarded to bypass boot method that sets account_type
        $ownerUser = User::unguarded(function () {
            return User::firstOrCreate(
                ['email' => 'owner@geopamu.com'],
                [
                    'name' => 'Geopamu Owner',
                    'password' => Hash::make('password'),
                    'phone' => '+260977123456',
                    'account_type' => 'business',
                    'account_types' => ['business'], // Array, not JSON string
                    'referral_code' => 'MGN' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6)),
                ]
            );
        });

        // Create CMS user for owner
        $ownerRole = RoleModel::where('company_id', $company->id)
            ->where('name', 'Owner')
            ->first();

        $cmsUser = CmsUserModel::create([
            'company_id' => $company->id,
            'user_id' => $ownerUser->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
        ]);

        $this->command->info("✓ Created CMS user for: {$ownerUser->name}");
        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('Geopamu CMS Setup Complete!');
        $this->command->info('===========================================');
        $this->command->info('Company: Geopamu Investments Limited');
        $this->command->info('Login: owner@geopamu.com');
        $this->command->info('Password: password');
        $this->command->info('URL: /cms');
        $this->command->info('===========================================');
    }
}
