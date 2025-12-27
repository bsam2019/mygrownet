<?php

namespace App\Services\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\SitePermission;
use App\Infrastructure\GrowBuilder\Models\SiteRole;

class SiteRoleService
{
    /**
     * Role types
     */
    public const TYPE_STAFF = 'staff';
    public const TYPE_CLIENT = 'client';

    /**
     * Default role configurations
     * Staff roles: employees who manage the site
     * Client roles: customers/members who use the site
     */
    protected array $defaultRoles = [
        // === STAFF ROLES ===
        'admin' => [
            'name' => 'Administrator',
            'description' => 'Full access to all site features',
            'level' => 100,
            'type' => 'staff',
            'icon' => 'shield-check',
            'color' => 'indigo',
            'is_system' => true,
            'permissions' => ['*'], // All permissions
        ],
        'manager' => [
            'name' => 'Manager',
            'description' => 'Manage users, orders, and view analytics',
            'level' => 75,
            'type' => 'staff',
            'icon' => 'briefcase',
            'color' => 'purple',
            'is_system' => true,
            'permissions' => [
                'users.view', 'users.create', 'users.edit',
                'orders.view', 'orders.edit', 'orders.process',
                'posts.view', 'posts.create', 'posts.edit', 'posts.publish',
                'media.upload', 'media.delete',
                'analytics.view',
                'comments.moderate',
                'member.access', 'member.content', 'member.profile',
            ],
        ],
        'editor' => [
            'name' => 'Editor',
            'description' => 'Can create and manage content',
            'level' => 50,
            'type' => 'staff',
            'icon' => 'pencil-square',
            'color' => 'blue',
            'is_system' => true,
            'permissions' => [
                'posts.view', 'posts.create', 'posts.edit', 'posts.delete', 'posts.publish',
                'media.upload', 'media.delete', 'comments.moderate',
                'member.access', 'member.content', 'member.profile',
            ],
        ],
        'support' => [
            'name' => 'Support',
            'description' => 'Handle customer inquiries and orders',
            'level' => 40,
            'type' => 'staff',
            'icon' => 'chat-bubble-left-right',
            'color' => 'cyan',
            'is_system' => true,
            'permissions' => [
                'orders.view', 'orders.edit',
                'users.view',
                'comments.moderate',
                'member.access', 'member.profile',
            ],
        ],

        // === CLIENT ROLES ===
        'vip' => [
            'name' => 'VIP Member',
            'description' => 'Premium member with exclusive access',
            'level' => 25,
            'type' => 'client',
            'icon' => 'star',
            'color' => 'yellow',
            'is_system' => true,
            'permissions' => [
                'member.access', 'member.content', 'member.orders', 'member.profile',
                'member.vip', 'member.downloads',
            ],
        ],
        'member' => [
            'name' => 'Member',
            'description' => 'Access to member area and content',
            'level' => 20,
            'type' => 'client',
            'icon' => 'user',
            'color' => 'emerald',
            'is_system' => true,
            'permissions' => [
                'member.access', 'member.content', 'member.orders', 'member.profile',
            ],
        ],
        'customer' => [
            'name' => 'Customer',
            'description' => 'Can place orders and manage profile',
            'level' => 10,
            'type' => 'client',
            'icon' => 'shopping-bag',
            'color' => 'amber',
            'is_system' => true,
            'permissions' => [
                'member.orders', 'member.profile',
            ],
        ],
    ];

    /**
     * Create default roles for a new site
     */
    public function createDefaultRolesForSite(GrowBuilderSite $site): array
    {
        $createdRoles = [];

        foreach ($this->defaultRoles as $slug => $config) {
            $role = SiteRole::create([
                'site_id' => $site->id,
                'name' => $config['name'],
                'slug' => $slug,
                'description' => $config['description'],
                'level' => $config['level'],
                'type' => $config['type'] ?? self::TYPE_CLIENT,
                'icon' => $config['icon'] ?? null,
                'color' => $config['color'] ?? null,
                'is_system' => $config['is_system'],
            ]);

            // Assign permissions (skip for admin as they have all permissions via level)
            if ($config['permissions'] !== ['*']) {
                $permissionIds = SitePermission::whereIn('slug', $config['permissions'])
                    ->pluck('id')
                    ->toArray();
                $role->permissions()->sync($permissionIds);
            }

            $createdRoles[$slug] = $role;
        }

        return $createdRoles;
    }

    /**
     * Get the default member role for a site
     */
    public function getDefaultMemberRole(GrowBuilderSite $site): ?SiteRole
    {
        return SiteRole::forSite($site->id)
            ->where('slug', 'member')
            ->first();
    }

    /**
     * Get the default customer role for a site
     */
    public function getDefaultCustomerRole(GrowBuilderSite $site): ?SiteRole
    {
        return SiteRole::forSite($site->id)
            ->where('slug', 'customer')
            ->first();
    }

    /**
     * Get all roles for a site
     */
    public function getRolesForSite(GrowBuilderSite $site): \Illuminate\Database\Eloquent\Collection
    {
        return SiteRole::forSite($site->id)
            ->orderBy('level', 'desc')
            ->get();
    }

    /**
     * Create a custom role for a site
     */
    public function createCustomRole(
        GrowBuilderSite $site,
        string $name,
        string $slug,
        array $permissionSlugs,
        ?string $description = null,
        int $level = 15,
        string $type = self::TYPE_CLIENT,
        ?string $icon = null,
        ?string $color = null
    ): SiteRole {
        $role = SiteRole::create([
            'site_id' => $site->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'level' => $level,
            'type' => $type,
            'icon' => $icon,
            'color' => $color,
            'is_system' => false,
        ]);

        $permissionIds = SitePermission::whereIn('slug', $permissionSlugs)
            ->pluck('id')
            ->toArray();
        $role->permissions()->sync($permissionIds);

        return $role;
    }

    /**
     * Get staff roles for a site
     */
    public function getStaffRoles(GrowBuilderSite $site): \Illuminate\Database\Eloquent\Collection
    {
        return SiteRole::forSite($site->id)
            ->where('type', self::TYPE_STAFF)
            ->orderBy('level', 'desc')
            ->get();
    }

    /**
     * Get client roles for a site
     */
    public function getClientRoles(GrowBuilderSite $site): \Illuminate\Database\Eloquent\Collection
    {
        return SiteRole::forSite($site->id)
            ->where('type', self::TYPE_CLIENT)
            ->orderBy('level', 'desc')
            ->get();
    }

    /**
     * Check if a role is a staff role
     */
    public function isStaffRole(SiteRole $role): bool
    {
        return $role->type === self::TYPE_STAFF;
    }

    /**
     * Check if a role is a client role
     */
    public function isClientRole(SiteRole $role): bool
    {
        return $role->type === self::TYPE_CLIENT;
    }

    /**
     * Get available role types
     */
    public function getRoleTypes(): array
    {
        return [
            self::TYPE_STAFF => 'Staff (Employees)',
            self::TYPE_CLIENT => 'Client (Customers/Members)',
        ];
    }

    /**
     * Get default role configuration
     */
    public function getDefaultRoleConfig(): array
    {
        return $this->defaultRoles;
    }
}
