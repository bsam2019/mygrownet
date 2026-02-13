<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\RoleModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use App\Models\User;

class AddAdminToCmsSeeder extends Seeder
{
    /**
     * Add existing admin user to CMS
     */
    public function run(): void
    {
        // Find the Geopamu company
        $company = CompanyModel::where('name', 'Geopamu Investments Limited')->first();

        if (!$company) {
            $this->command->error('Geopamu company not found. Run GeopamuCmsSeeder first.');
            return;
        }

        // Find the Owner role
        $ownerRole = RoleModel::where('company_id', $company->id)
            ->where('name', 'Owner')
            ->first();

        if (!$ownerRole) {
            $this->command->error('Owner role not found.');
            return;
        }

        // Find admin user (assuming email is admin@example.com or similar)
        // You can modify this to match your actual admin email
        $adminUser = User::where('email', 'admin@mygrownet.com')
            ->orWhere('email', 'admin@example.com')
            ->orWhereHas('roles', function($q) {
                $q->where('name', 'Admin');
            })
            ->first();

        if (!$adminUser) {
            // If no specific admin found, get the first user with Admin role
            $adminUser = User::whereHas('roles', function($q) {
                $q->where('name', 'Admin');
            })->first();
        }

        if (!$adminUser) {
            $this->command->error('Admin user not found.');
            return;
        }

        // Check if CMS user already exists
        $existingCmsUser = CmsUserModel::where('user_id', $adminUser->id)->first();

        if ($existingCmsUser) {
            $this->command->info("Admin user already has CMS access.");
            return;
        }

        // Create CMS user for admin
        $cmsUser = CmsUserModel::create([
            'company_id' => $company->id,
            'user_id' => $adminUser->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
        ]);

        $this->command->info("✓ Added CMS access for: {$adminUser->name} ({$adminUser->email})");
        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('Admin CMS Access Granted!');
        $this->command->info('===========================================');
        $this->command->info("User: {$adminUser->name}");
        $this->command->info("Email: {$adminUser->email}");
        $this->command->info('Company: Geopamu Investments Limited');
        $this->command->info('Role: Owner');
        $this->command->info('URL: /cms');
        $this->command->info('===========================================');
    }
}
