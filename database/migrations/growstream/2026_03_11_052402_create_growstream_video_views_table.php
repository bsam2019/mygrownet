<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_video_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('growstream_videos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // View Details
            $table->integer('watch_duration')->comment('Seconds actually watched');
            $table->decimal('completion_percentage', 5, 2)->default(0);
            
            // Session Info
            $table->string('session_id', 100)->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('country_code', 2)->nullable();
            
            // Quality
            $table->string('quality_level', 20)->nullable();
            $table->integer('buffering_count')->default(0);
            
            // Referrer
            $table->text('referrer_url')->nullable();
            $table->string('traffic_source', 50)->nullable();
            
            $table->timestamp('viewed_at');
            
            // Indexes
            $table->index(['video_id', 'viewed_at']);
            $table->index(['user_id', 'viewed_at']);
            $table->index('viewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_video_views');
    }
};
