<?php

namespace Database\Seeders;

use App\Models\AgencyRole;
use Illuminate\Database\Seeder;

class AgencyRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemRoles = [
            [
                'role_name' => 'Owner',
                'is_system_role' => true,
                'description' => 'Full control over the agency',
                'permissions_json' => [
                    'create_site', 'edit_site', 'delete_site', 'publish_site', 'suspend_site',
                    'connect_domain', 'manage_storage',
                    'manage_clients', 'view_clients', 'edit_clients', 'delete_clients',
                    'manage_billing', 'view_billing', 'create_invoices', 'record_payments',
                    'invite_team_members', 'manage_team_members',
                    'view_reports', 'export_data',
                    'manage_templates', 'manage_branding',
                ],
            ],
            [
                'role_name' => 'Admin',
                'is_system_role' => true,
                'description' => 'Most permissions except billing',
                'permissions_json' => [
                    'create_site', 'edit_site', 'delete_site', 'publish_site', 'suspend_site',
                    'connect_domain', 'manage_storage',
                    'manage_clients', 'view_clients', 'edit_clients', 'delete_clients',
                    'view_billing',
                    'invite_team_members', 'manage_team_members',
                    'view_reports', 'export_data',
                    'manage_templates',
                ],
            ],
            [
                'role_name' => 'Project Manager',
                'is_system_role' => true,
                'description' => 'Client and site management',
                'permissions_json' => [
                    'create_site', 'edit_site', 'publish_site',
                    'manage_clients', 'view_clients', 'edit_clients',
                    'view_billing',
                    'view_reports',
                ],
            ],
            [
                'role_name' => 'Designer',
                'is_system_role' => true,
                'description' => 'Site editing and template management',
                'permissions_json' => [
                    'edit_site', 'publish_site',
                    'view_clients',
                    'manage_templates',
                ],
            ],
            [
                'role_name' => 'Developer',
                'is_system_role' => true,
                'description' => 'Site editing and domain management',
                'permissions_json' => [
                    'edit_site', 'publish_site',
                    'connect_domain',
                    'view_clients',
                ],
            ],
            [
                'role_name' => 'Content Editor',
                'is_system_role' => true,
                'description' => 'Content editing only',
                'permissions_json' => [
                    'edit_site',
                    'view_clients',
                ],
            ],
            [
                'role_name' => 'Billing Officer',
                'is_system_role' => true,
                'description' => 'Client billing and invoices',
                'permissions_json' => [
                    'view_clients',
                    'manage_billing', 'view_billing', 'create_invoices', 'record_payments',
                    'view_reports',
                ],
            ],
            [
                'role_name' => 'Support Staff',
                'is_system_role' => true,
                'description' => 'View-only access',
                'permissions_json' => [
                    'view_clients',
                    'view_billing',
                    'view_reports',
                ],
            ],
        ];

        foreach ($systemRoles as $role) {
            AgencyRole::updateOrCreate(
                [
                    'role_name' => $role['role_name'],
                    'is_system_role' => true,
                    'agency_id' => null,
                ],
                $role
            );
        }

        $this->command->info('Agency system roles seeded successfully!');
    }
}
