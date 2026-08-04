<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Registers the GrowStream module in the `modules` table.
 *
 * module_subscriptions.module_id is a foreign key to modules.id. GrowStream
 * subscriptions (startCheckout) write module_id = 'growstream', but no row
 * ever existed, so the insert failed with "FOREIGN KEY constraint failed".
 * Insert it idempotently. Access logic itself reads config (not this table),
 * so the row is only needed to satisfy the FK.
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('modules')->where('id', 'growstream')->exists();

        if ($exists) {
            return;
        }

        DB::table('modules')->insert([
            'id' => 'growstream',
            'name' => 'GrowStream',
            'slug' => 'growstream',
            'category' => 'personal',
            'description' => 'Video streaming & learning platform',
            'icon' => 'PlayCircleIcon',
            'color' => 'fuchsia',
            'thumbnail' => null,
            'account_types' => json_encode(['member', 'business']),
            'required_roles' => null,
            'min_user_level' => null,
            'routes' => json_encode([
                'integrated' => '/growstream',
                'standalone' => 'https://growstream.mygrownet.com',
            ]),
            'pwa_config' => json_encode(['enabled' => true, 'installable' => true]),
            'features' => json_encode(['offline' => false, 'notifications' => true]),
            'subscription_tiers' => null,
            'requires_subscription' => true,
            'version' => '1.0.0',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('modules')->where('id', 'growstream')->delete();
    }
};
