<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('growstream_videos', function (Blueprint $table) {
            // Point assignment fields
            $table->integer('watch_points')->default(0)->after('share_count')->comment('Points awarded for watching the video');
            $table->integer('completion_points')->default(0)->after('watch_points')->comment('Points awarded for completing the video');
            $table->integer('share_points')->default(0)->after('completion_points')->comment('Points awarded for sharing the video');
            
            // Starter kit integration fields
            $table->boolean('is_starter_kit_content')->default(false)->after('share_points')->comment('Whether this video is part of starter kit content');
            $table->string('starter_kit_tier')->nullable()->after('is_starter_kit_content')->comment('Starter kit tier requirement (basic, premium, elite, all)');
            $table->integer('starter_kit_unlock_order')->nullable()->after('starter_kit_tier')->comment('Order in which content unlocks in starter kit');
            $table->integer('starter_kit_points_reward')->default(0)->after('starter_kit_unlock_order')->comment('Points awarded when accessed through starter kit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('growstream_videos', function (Blueprint $table) {
            $table->dropColumn([
                'watch_points',
                'completion_points', 
                'share_points',
                'is_starter_kit_content',
                'starter_kit_tier',
                'starter_kit_unlock_order',
                'starter_kit_points_reward'
            ]);
        });
    }
};