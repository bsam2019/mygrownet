<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Backfill existing videos that use Cloudflare provider with
     * thumbnail_storage_disk = 'cloudflare' to indicate they rely
     * on auto-generated thumbnails.
     */
    public function up(): void
    {
        // Mark all videos with a provider_video_id (Cloudflare videos) as using
        // Cloudflare auto-generated thumbnails
        DB::table('growstream_videos')
            ->where('video_provider', 'cloudflare')
            ->whereNotNull('provider_video_id')
            ->whereNull('thumbnail_storage_disk')
            ->update([
                'thumbnail_storage_disk' => 'cloudflare',
                'updated_at' => now(),
            ]);

        // Log the backfill
        $count = DB::table('growstream_videos')
            ->where('thumbnail_storage_disk', 'cloudflare')
            ->count();
        
        \Log::info("Backfilled {$count} videos with thumbnail_storage_disk='cloudflare'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset thumbnail_storage_disk for Cloudflare videos
        DB::table('growstream_videos')
            ->where('thumbnail_storage_disk', 'cloudflare')
            ->update([
                'thumbnail_storage_disk' => null,
                'updated_at' => now(),
            ]);
    }
};
