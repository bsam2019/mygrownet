<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrowMartModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('modules')->updateOrInsert(
            ['id' => 'growmart'],
            [
                'name' => 'GrowMart',
                'slug' => 'growmart',
                'category' => 'personal',
                'description' => 'Online grocery supermarket — shop fresh produce, meats, and pantry staples delivered to your door',
                'icon' => '🛒',
                'color' => '#059669', // Emerald
                'thumbnail' => null,
                'account_types' => json_encode(['member', 'business', 'client']),
                'required_roles' => null,
                'min_user_level' => null,
                'routes' => json_encode([
                    'integrated' => '/growmart',
                    'standalone' => '/growmart',
                    'welcome' => '/growmart',
                ]),
                'pwa_config' => json_encode([
                    'enabled' => true,
                    'installable' => true,
                    'offline_capable' => false,
                ]),
                'features' => json_encode([
                    'offline' => false,
                    'notifications' => true,
                    'wishlist' => true,
                    'coupons' => true,
                    'reviews' => true,
                    'tracking' => true,
                ]),
                'subscription_tiers' => null,
                'requires_subscription' => false,
                'version' => '1.0.0',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
