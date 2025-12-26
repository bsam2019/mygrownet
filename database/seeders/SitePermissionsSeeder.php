<?php

namespace Database\Seeders;

use App\Infrastructure\GrowBuilder\Models\SitePermission;
use Illuminate\Database\Seeder;

class SitePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Users group
            ['name' => 'View Users', 'slug' => 'users.view', 'group_name' => 'users', 'description' => 'View user list'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'group_name' => 'users', 'description' => 'Create new users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'group_name' => 'users', 'description' => 'Edit user profiles'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'group_name' => 'users', 'description' => 'Delete users'],
            ['name' => 'Assign Roles', 'slug' => 'users.roles', 'group_name' => 'users', 'description' => 'Assign roles to users'],

            // Content group
            ['name' => 'View Posts', 'slug' => 'posts.view', 'group_name' => 'content', 'description' => 'View all posts'],
            ['name' => 'Create Posts', 'slug' => 'posts.create', 'group_name' => 'content', 'description' => 'Create new posts'],
            ['name' => 'Edit Posts', 'slug' => 'posts.edit', 'group_name' => 'content', 'description' => 'Edit posts'],
            ['name' => 'Delete Posts', 'slug' => 'posts.delete', 'group_name' => 'content', 'description' => 'Delete posts'],
            ['name' => 'Publish Posts', 'slug' => 'posts.publish', 'group_name' => 'content', 'description' => 'Publish/unpublish posts'],
            ['name' => 'Upload Media', 'slug' => 'media.upload', 'group_name' => 'content', 'description' => 'Upload media files'],
            ['name' => 'Delete Media', 'slug' => 'media.delete', 'group_name' => 'content', 'description' => 'Delete media files'],
            ['name' => 'Moderate Comments', 'slug' => 'comments.moderate', 'group_name' => 'content', 'description' => 'Moderate comments'],

            // Orders group
            ['name' => 'View Orders', 'slug' => 'orders.view', 'group_name' => 'orders', 'description' => 'View all orders'],
            ['name' => 'Manage Orders', 'slug' => 'orders.manage', 'group_name' => 'orders', 'description' => 'Update order status'],
            ['name' => 'Process Refunds', 'slug' => 'orders.refund', 'group_name' => 'orders', 'description' => 'Process refunds'],
            ['name' => 'View Products', 'slug' => 'products.view', 'group_name' => 'orders', 'description' => 'View products'],
            ['name' => 'Manage Products', 'slug' => 'products.manage', 'group_name' => 'orders', 'description' => 'Create/edit products'],

            // Settings group
            ['name' => 'View Settings', 'slug' => 'settings.view', 'group_name' => 'settings', 'description' => 'View site settings'],
            ['name' => 'Edit Settings', 'slug' => 'settings.edit', 'group_name' => 'settings', 'description' => 'Edit site settings'],
            ['name' => 'View Analytics', 'slug' => 'analytics.view', 'group_name' => 'settings', 'description' => 'View analytics'],

            // Member group
            ['name' => 'Access Member Area', 'slug' => 'member.access', 'group_name' => 'member', 'description' => 'Access member area'],
            ['name' => 'View Member Content', 'slug' => 'member.content', 'group_name' => 'member', 'description' => 'View member-only content'],
            ['name' => 'Place Orders', 'slug' => 'member.orders', 'group_name' => 'member', 'description' => 'Place orders'],
            ['name' => 'Edit Profile', 'slug' => 'member.profile', 'group_name' => 'member', 'description' => 'Edit own profile'],
        ];

        foreach ($permissions as $permission) {
            SitePermission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $this->command->info('Site permissions seeded successfully!');
    }
}
