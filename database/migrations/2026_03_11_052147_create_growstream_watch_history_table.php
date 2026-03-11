<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_watch_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('video_id')->constrained('growstream_videos')->cascadeOnDelete();
            
            // Progress
            $table->integer('current_position')->default(0)->comment('Current position in seconds');
            $table->integer('duration')->comment('Total video duration in seconds');
            $table->decimal('progress_percentage', 5, 2)->default(0);
            
            // Status
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            
            // Session Info
            $table->string('session_id', 100)->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Timestamps
            $table->timestamp('started_at');
            $table->timestamp('last_watched_at');
            $table->timestamps();
            
            // Unique constraint - one progress record per user per video
            $table->unique(['user_id', 'video_id']);
            $table->index(['user_id', 'last_watched_at']);
            $table->index('video_id');
            $table->index('is_completed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_watch_history');
    }
};
