<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Full system access'
            ],
            [
                'name' => 'Investment Manager',
                'slug' => 'investment-manager',
                'description' => 'Manages investments and approvals'
            ],
            [
                'name' => 'Support Agent',
                'slug' => 'support-agent',
                'description' => 'Handles customer support'
            ],
            [
                'name' => 'Investor',
                'slug' => 'investor',
                'description' => 'Regular platform investor'
            ]
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'name' => $roleData['name'],
                    'slug' => $roleData['slug'],
                    'description' => $roleData['description'],
                    'guard_name' => 'web',
                    'is_active' => true
                ]
            );
        }

        // Create Permissions
        $permissions = [
            // Investment Management
            ['name' => 'view investments', 'slug' => 'view-investments'],
            ['name' => 'create investments', 'slug' => 'create-investments'],
            ['name' => 'approve investments', 'slug' => 'approve-investments'],
            ['name' => 'reject investments', 'slug' => 'reject-investments'],
            ['name' => 'delete investments', 'slug' => 'delete-investments'],

            // User Management
            ['name' => 'view users', 'slug' => 'view-users'],
            ['name' => 'create users', 'slug' => 'create-users'],
            ['name' => 'edit users', 'slug' => 'edit-users'],
            ['name' => 'delete users', 'slug' => 'delete-users'],
            ['name' => 'manage user status', 'slug' => 'manage-user-status'],

            // Transaction Management
            ['name' => 'view transactions', 'slug' => 'view-transactions'],
            ['name' => 'approve withdrawals', 'slug' => 'approve-withdrawals'],
            ['name' => 'reject withdrawals', 'slug' => 'reject-withdrawals'],

            // Reports and Analytics
            ['name' => 'view reports', 'slug' => 'view-reports'],
            ['name' => 'export reports', 'slug' => 'export-reports'],
            ['name' => 'view metrics', 'slug' => 'view-metrics'],

            // System Settings
            ['name' => 'manage settings', 'slug' => 'manage-settings'],
            ['name' => 'manage roles', 'slug' => 'manage-roles'],
            ['name' => 'manage permissions', 'slug' => 'manage-permissions'],

            // Categories
            ['name' => 'manage categories', 'slug' => 'manage-categories'],

            // Activity Logs
            ['name' => 'view activity logs', 'slug' => 'view-activity-logs'],

            // Support
            ['name' => 'manage support tickets', 'slug' => 'manage-support-tickets'],
            ['name' => 'view support tickets', 'slug' => 'view-support-tickets'],

            // Employee Management
            ['name' => 'view employees', 'slug' => 'view-employees'],
            ['name' => 'create employees', 'slug' => 'create-employees'],
            ['name' => 'edit employees', 'slug' => 'edit-employees'],
            ['name' => 'delete employees', 'slug' => 'delete-employees'],

            // Department Management
            ['name' => 'view departments', 'slug' => 'view-departments'],
            ['name' => 'create departments', 'slug' => 'create-departments'],
            ['name' => 'edit departments', 'slug' => 'edit-departments'],
            ['name' => 'delete departments', 'slug' => 'delete-departments'],
            ['name' => 'manage department heads', 'slug' => 'manage-department-heads'],

            // Position Management
            ['name' => 'view positions', 'slug' => 'view-positions'],
            ['name' => 'create positions', 'slug' => 'create-positions'],
            ['name' => 'edit positions', 'slug' => 'edit-positions'],
            ['name' => 'delete positions', 'slug' => 'delete-positions'],

            // Performance Management
            ['name' => 'view performance', 'slug' => 'view-performance'],
            ['name' => 'create performance reviews', 'slug' => 'create-performance-reviews'],
            ['name' => 'edit performance reviews', 'slug' => 'edit-performance-reviews'],
            ['name' => 'delete performance reviews', 'slug' => 'delete-performance-reviews'],
            ['name' => 'manage performance goals', 'slug' => 'manage-performance-goals'],

            // Commission Management
            ['name' => 'view commissions', 'slug' => 'view-commissions'],
            ['name' => 'create commissions', 'slug' => 'create-commissions'],
            ['name' => 'calculate commissions', 'slug' => 'calculate-commissions'],
            ['name' => 'approve commissions', 'slug' => 'approve-commissions'],
            ['name' => 'process commission payments', 'slug' => 'process-commission-payments'],
            ['name' => 'view commission reports', 'slug' => 'view-commission-reports'],
            ['name' => 'view commission analytics', 'slug' => 'view-commission-analytics']
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'slug' => $permission['slug'],
                    'guard_name' => 'web',
                    'description' => 'Ability to ' . $permission['name']
                ]
            );
        }

        // Get roles
        $admin = Role::where('name', 'Administrator')->first();
        $manager = Role::where('name', 'Investment Manager')->first();
        $support = Role::where('name', 'Support Agent')->first();
        $investor = Role::where('name', 'Investor')->first();

        // Assign all permissions to admin
        $admin->permissions()->sync(Permission::all());

        // Assign manager permissions
        $manager->permissions()->sync(
            Permission::whereIn('slug', [
                'view-investments',
                'approve-investments',
                'reject-investments',
                'view-users',
                'view-transactions',
                'approve-withdrawals',
                'reject-withdrawals',
                'view-reports',
                'export-reports',
                'view-metrics',
                'view-activity-logs',
                'view-employees',
                'create-employees',
                'edit-employees',
                'view-departments',
                'create-departments',
                'edit-departments',
                'manage-department-heads',
                'view-positions',
                'create-positions',
                'edit-positions',
                'view-performance',
                'create-performance-reviews',
                'edit-performance-reviews',
                'manage-performance-goals',
                'view-commissions',
                'create-commissions',
                'calculate-commissions',
                'approve-commissions',
                'view-commission-reports',
                'view-commission-analytics'
            ])->get()
        );

        // Assign support permissions
        $support->permissions()->sync(
            Permission::whereIn('slug', [
                'view-users',
                'view-investments',
                'view-transactions',
                'manage-support-tickets',
                'view-support-tickets'
            ])->get()
        );

        // Assign investor permissions
        $investor->permissions()->sync(
            Permission::whereIn('slug', [
                'view-investments',
                'create-investments',
                'view-transactions',
                'view-support-tickets'
            ])->get()
        );
    }
}
