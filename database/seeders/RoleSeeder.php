<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // IMPORTANT: Professional Levels vs Roles
        // ============================================
        // Professional Levels (Associate, Professional, Senior, Manager, Director, Executive, Ambassador)
        // are NOT roles - they are progression levels stored in users.professional_level (1-7)
        // 
        // Roles are for ACCESS CONTROL only:
        // - superadmin: Super administrator (highest access)
        // - admin: Platform administrators
        // - employee: Company employees
        // - member: Regular platform users
        // ============================================
        
        // Create system roles (ACCESS CONTROL)
        $superadminRole = Role::firstOrCreate(
            ['name' => 'superadmin'],
            ['slug' => 'superadmin', 'description' => 'Super Administrator - Highest level access, can manage everything including roles']
        );
        
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['slug' => 'admin', 'description' => 'Administrator - Full platform access, can manage users and content']
        );
        
        $employeeRole = Role::firstOrCreate(
            ['name' => 'employee'],
            ['slug' => 'employee', 'description' => 'Employee - Company staff with limited admin access']
        );
        
        // Member role - Primary role for all platform users
        // MyGrowNet is subscription-based, not investment-based
        $memberRole = Role::firstOrCreate(
            ['name' => 'member'],
            ['slug' => 'member', 'description' => 'Platform Member - Regular user with subscription access']
        );
        
        // Keep legacy roles for backward compatibility (will be deprecated)
        $investorRole = Role::firstOrCreate(
            ['name' => 'investor'],
            ['slug' => 'investor', 'description' => 'LEGACY - Use "member" instead. Kept for backward compatibility.']
        );

        // Create permissions
        $permissions = [
            // Dashboard permissions
            'view_admin_dashboard',
            'view_employee_dashboard',
            'view_member_dashboard',
            'view_manager_dashboard',  // Legacy
            'view_investor_dashboard', // Legacy
            
            // Role & Permission management (superadmin only)
            'manage_roles',
            'manage_permissions',
            'assign_roles',
            'view_roles',
            'view_permissions',
            
            // User management
            'manage_users',
            'manage_members',
            'view_team_members',
            'view_team_users',  // Legacy
            'approve_withdrawals',
            'approve_tier_upgrades',
            
            // Subscription management (MyGrowNet is subscription-based)
            'manage_subscriptions',
            'view_team_subscriptions',
            'create_subscription',
            'cancel_subscription',
            
            // Investment management (Legacy - for community projects only)
            'manage_investments',
            'view_team_investments',
            'create_investments',
            
            // Content management
            'manage_courses',
            'manage_learning_packs',
            'manage_community_projects',
            
            // Report permissions
            'view_admin_reports',
            'view_team_reports',
            'view_personal_reports',
            
            // Matrix and referral permissions
            'manage_matrix',
            'view_team_matrix',
            'view_personal_matrix',
            
            // Financial permissions
            'manage_profit_distribution',
            'manage_commissions',
            'view_team_commissions',
            'view_personal_commissions',
            
            // Points management
            'manage_points',
            'award_points',
            'view_team_points',
            'view_personal_points',
        ];

        foreach ($permissions as $permission) {
            $slug = str_replace('_', '-', $permission);
            
            // Try to find by slug first (in case name format is different)
            $existingPermission = Permission::where('slug', $slug)->where('guard_name', 'web')->first();
            
            if ($existingPermission) {
                // Update existing permission
                $existingPermission->update([
                    'name' => $permission,
                    'description' => ucwords(str_replace('_', ' ', $permission))
                ]);
            } else {
                // Create new permission
                Permission::create([
                    'name' => $permission,
                    'slug' => $slug,
                    'guard_name' => 'web',
                    'description' => ucwords(str_replace('_', ' ', $permission))
                ]);
            }
        }

        // Assign permissions to roles
        
        // Superadmin gets ALL permissions
        $superadminRole->syncPermissions($permissions);
        
        // Admin gets all except role/permission management
        $adminPermissions = array_filter($permissions, function($permission) {
            return !in_array($permission, ['manage_roles', 'manage_permissions', 'assign_roles']);
        });
        $adminRole->syncPermissions($adminPermissions);
        
        // Employee role permissions
        $employeeRole->syncPermissions([
            'view_employee_dashboard',
            'view_team_users',
            'view_team_members',
            'view_team_subscriptions',
            'view_team_reports',
            'view_team_matrix',
            'view_team_commissions',
            'view_team_points',
            'manage_courses',
            'manage_learning_packs',
        ]);
        
        // Member role permissions (subscription-based platform)
        $memberRole->syncPermissions([
            'view_member_dashboard',
            'create_subscription',
            'cancel_subscription',
            'view_personal_reports',
            'view_personal_matrix',
            'view_personal_commissions',
            'view_personal_points',
        ]);
        
        // Legacy investor role (backward compatibility) - same as member
        $investorRole->syncPermissions([
            'view_investor_dashboard',
            'view_member_dashboard',  // Redirect to member dashboard
            'create_investments',
            'create_subscription',
            'view_personal_reports',
            'view_personal_matrix',
            'view_personal_commissions',
            'view_personal_points',
        ]);
        
        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->newLine();
        $this->command->info('✅ System Roles (Access Control):');
        $this->command->line('  • superadmin - Super administrator (highest access)');
        $this->command->line('  • admin - Platform administrators');
        $this->command->line('  • employee - Company employees');
        $this->command->line('  • member - Regular platform users');
        $this->command->newLine();
        $this->command->info('⚠️  Legacy Roles (Backward Compatibility):');
        $this->command->line('  • investor - Use "member" instead');
        $this->command->newLine();
        $this->command->warn('IMPORTANT: Professional Levels (Associate, Professional, Senior, Manager, Director, Executive, Ambassador)');
        $this->command->warn('are NOT roles! They are progression levels stored in users.professional_level (1-7).');
    }
}