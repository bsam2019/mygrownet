<?php

namespace App\Services\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\SitePermission;
use App\Infrastructure\GrowBuilder\Models\SiteRole;

class SiteRoleService
{
    /**
     * Default role configurations
     */
    protected array $defaultRoles = [
        'admin' => [
            'name' => 'Administrator',
            'description' => 'Full access to all site features',
            'level' => 100,
            'is_system' => true,
            'permissions' => ['*'], // All permissions
        ],
        'editor' => [
            'name' => 'Editor',
            'description' => 'Can create and manage content',
            'level' => 50,
            'is_system' => true,
            'permissions' => [
                'posts.view', 'posts.create', 'posts.edit', 'posts.delete', 'posts.publish',
                'media.upload', 'media.delete', 'comments.moderate',
                'member.access', 'member.content', 'member.profile',
            ],
        ],
        'member' => [
            'name' => 'Member',
            'description' => 'Access to member area and content',
            'level' => 20,
            'is_system' => true,
            'permissions' => [
                'member.access', 'member.content', 'member.orders', 'member.profile',
            ],
        ],
        'customer' => [
            'name' => 'Customer',
            'description' => 'Can place orders and manage profile',
            'level' => 10,
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
        int $level = 15
    ): SiteRole {
        $role = SiteRole::create([
            'site_id' => $site->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'level' => $level,
            'is_system' => false,
        ]);

        $permissionIds = SitePermission::whereIn('slug', $permissionSlugs)
            ->pluck('id')
            ->toArray();
        $role->permissions()->sync($permissionIds);

        return $role;
    }
}
