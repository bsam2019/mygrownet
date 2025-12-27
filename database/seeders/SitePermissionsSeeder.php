<?php

namespace Database\Seeders;

use App\Infrastructure\GrowBuilder\Models\SitePermission;
use Illuminate\Database\Seeder;

class SitePermissionsSeeder extends Seeder
{
    /**
     * Global permissions available to all GrowBuilder sites.
     * These are the building blocks for role-based access control.
     */
    protected array $permissions = [
        // User Management
        'users' => [
            'users.view' => 'View users list',
            'users.create' => 'Create new users',
            'users.edit' => 'Edit user details',
            'users.delete' => 'Delete users',
            'users.roles' => 'Manage user roles',
        ],

        // Content Management
        'content' => [
            'posts.view' => 'View posts',
            'posts.create' => 'Create posts',
            'posts.edit' => 'Edit posts',
            'posts.delete' => 'Delete posts',
            'posts.publish' => 'Publish/unpublish posts',
            'pages.view' => 'View pages',
            'pages.edit' => 'Edit pages',
            'media.view' => 'View media library',
            'media.upload' => 'Upload media files',
            'media.delete' => 'Delete media files',
            'comments.view' => 'View comments',
            'comments.moderate' => 'Moderate comments',
        ],

        // Orders & Commerce
        'orders' => [
            'orders.view' => 'View orders',
            'orders.create' => 'Create orders',
            'orders.edit' => 'Edit orders',
            'orders.delete' => 'Delete orders',
            'orders.process' => 'Process/fulfill orders',
            'orders.refund' => 'Issue refunds',
            'products.view' => 'View products',
            'products.create' => 'Create products',
            'products.edit' => 'Edit products',
            'products.delete' => 'Delete products',
        ],

        // Messages & Support
        'messages' => [
            'messages.view' => 'View contact messages',
            'messages.reply' => 'Reply to messages',
            'messages.delete' => 'Delete messages',
        ],

        // Analytics & Reports
        'analytics' => [
            'analytics.view' => 'View site analytics',
            'analytics.export' => 'Export analytics data',
            'reports.view' => 'View reports',
            'reports.generate' => 'Generate reports',
        ],

        // Site Settings
        'settings' => [
            'settings.view' => 'View site settings',
            'settings.edit' => 'Edit site settings',
            'settings.design' => 'Edit site design/theme',
            'settings.integrations' => 'Manage integrations',
            'settings.billing' => 'Manage billing',
        ],

        // Member Area (for site visitors/customers)
        'member' => [
            'member.access' => 'Access member dashboard',
            'member.profile' => 'Edit own profile',
            'member.orders' => 'View own orders',
            'member.content' => 'Access member-only content',
            'member.downloads' => 'Access downloads',
            'member.vip' => 'Access VIP features',
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding global site permissions...');

        $count = 0;
        foreach ($this->permissions as $group => $perms) {
            foreach ($perms as $slug => $description) {
                SitePermission::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $this->slugToName($slug),
                        'slug' => $slug,
                        'group_name' => $group,
                        'description' => $description,
                    ]
                );
                $count++;
            }
        }

        $this->command->info("Seeded {$count} permissions across " . count($this->permissions) . " groups.");
    }

    /**
     * Convert slug to readable name
     */
    protected function slugToName(string $slug): string
    {
        // users.view -> View Users
        $parts = explode('.', $slug);
        $action = ucfirst($parts[1] ?? '');
        $resource = ucfirst($parts[0] ?? '');
        
        return "{$action} {$resource}";
    }
}
