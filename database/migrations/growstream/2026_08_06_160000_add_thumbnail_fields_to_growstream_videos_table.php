<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growstream_videos', function (Blueprint $table) {
            // Storage location indicator: null (not set), 'cloudflare' (auto-generated), 'wasabi' (custom)
            $table->enum('thumbnail_storage_disk', ['cloudflare', 'wasabi'])
                ->nullable()
                ->after('thumbnail_url')
                ->comment('Where thumbnail is stored: cloudflare auto-generated or wasabi custom upload');
            
            // JSON structure: {"thumb": "url", "medium": "url", "large": "url", "webp": {...}}
            $table->json('thumbnail_sizes')
                ->nullable()
                ->after('thumbnail_storage_disk')
                ->comment('Multiple thumbnail sizes with URLs for responsive images');
        });
    }

    public function down(): void
    {
        Schema::table('growstream_videos', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_storage_disk', 'thumbnail_sizes']);
        });
    }
};
