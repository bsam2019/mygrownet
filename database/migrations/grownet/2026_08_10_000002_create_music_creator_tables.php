<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Music Tracks Table
        if (!Schema::hasTable('music_tracks')) {
            Schema::create('music_tracks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('artist_user_id')->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('platform_id')->nullable();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('album_name')->nullable();
                $table->string('genre', 100)->default('Afro-Beats');
                $table->string('audio_url');
                $table->string('cover_art_url')->nullable();
                $table->integer('duration_seconds')->default(180);
                $table->string('isrc_code', 50)->nullable();
                $table->boolean('is_vip_only')->default(false);
                $table->integer('stream_count')->default(0);
                $table->timestamps();

                $table->index('artist_user_id');
                $table->index('genre');
                $table->index('stream_count');
            });
        }

        // Artist Fan Subscriptions Table
        if (!Schema::hasTable('artist_fan_subscriptions')) {
            Schema::create('artist_fan_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('artist_user_id')->constrained('users')->onDelete('cascade');
                $table->string('tier_name', 50)->default('Bronze Fan');
                $table->decimal('monthly_price', 10, 2)->default(50.00);
                $table->enum('status', ['active', 'cancelled', 'expired'])->default('active');
                $table->timestamp('subscribed_at')->useCurrent();
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->index(['user_id', 'artist_user_id', 'status']);
            });
        }

        // Music Stream Logs Table
        if (!Schema::hasTable('music_stream_logs')) {
            Schema::create('music_stream_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('track_id')->constrained('music_tracks')->onDelete('cascade');
                $table->foreignId('listener_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->integer('duration_listened_seconds')->default(30);
                $table->decimal('royalty_earned', 10, 4)->default(0.0000);
                $table->timestamps();

                $table->index('track_id');
                $table->index('listener_user_id');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('music_stream_logs');
        Schema::dropIfExists('artist_fan_subscriptions');
        Schema::dropIfExists('music_tracks');
    }
};
