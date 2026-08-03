<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event', 60);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('video_id')->nullable()->constrained('growstream_videos')->nullOnDelete();
            $table->foreignId('creator_id')->nullable()->constrained('growstream_creator_profiles')->nullOnDelete();
            $table->string('session_id', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['event', 'occurred_at']);
            $table->index(['user_id', 'event']);
            $table->index(['video_id', 'event']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_analytics_events');
    }
};
