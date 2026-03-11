<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add foreign keys for videos table
        Schema::table('growstream_videos', function (Blueprint $table) {
            $table->foreign('series_id')->references('id')->on('growstream_video_series')->nullOnDelete();
            $table->foreign('trailer_video_id')->references('id')->on('growstream_videos')->nullOnDelete();
        });
        
        // Add foreign keys for video_series table
        Schema::table('growstream_video_series', function (Blueprint $table) {
            $table->foreign('trailer_video_id')->references('id')->on('growstream_videos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('growstream_videos', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropForeign(['trailer_video_id']);
        });
        
        Schema::table('growstream_video_series', function (Blueprint $table) {
            $table->dropForeign(['trailer_video_id']);
        });
    }
};
