<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_videos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Basic Info
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('long_description')->nullable();
            
            // Video Provider (Abstraction)
            $table->enum('video_provider', ['digitalocean', 'cloudflare', 'local'])->default('digitalocean');
            $table->string('provider_video_id')->nullable();
            $table->text('playback_url')->nullable();
            $table->enum('playback_policy', ['public', 'signed'])->default('signed');
            
            // Upload Status
            $table->enum('upload_status', ['pending', 'uploading', 'processing', 'ready', 'failed'])->default('pending');
            $table->integer('upload_progress')->default(0);
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            
            // Video Properties
            $table->integer('duration')->nullable()->comment('Duration in seconds');
            $table->bigInteger('file_size')->nullable()->comment('File size in bytes');
            $table->string('resolution', 20)->nullable();
            $table->string('aspect_ratio', 10)->nullable();
            
            // Assets (trailer foreign key added in separate migration)
            $table->text('thumbnail_url')->nullable();
            $table->text('poster_url')->nullable();
            $table->text('banner_url')->nullable();
            $table->unsignedBigInteger('trailer_video_id')->nullable();
            
            // Content Classification
            $table->enum('content_type', ['movie', 'series', 'episode', 'lesson', 'short', 'workshop', 'webinar'])->default('lesson');
            $table->string('language', 5)->default('en');
            $table->json('subtitles_available')->nullable();
            
            // Access Control
            $table->enum('access_level', ['free', 'basic', 'premium', 'institutional'])->default('basic');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_downloadable')->default(false);
            
            // Series Structure (foreign key added in separate migration)
            $table->unsignedBigInteger('series_id')->nullable();
            $table->integer('season_number')->nullable();
            $table->integer('episode_number')->nullable();
            
            // Creator
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            
            // Ratings
            $table->enum('content_rating', ['G', 'PG', 'PG-13', 'R', 'NR'])->default('NR');
            $table->decimal('quality_rating', 3, 1)->default(0)->comment('0.0-5.0 user rating');
            $table->enum('skill_level', ['beginner', 'intermediate', 'advanced', 'expert'])->nullable();
            
            // Engagement Metrics
            $table->bigInteger('view_count')->default(0);
            $table->integer('unique_viewers')->default(0);
            $table->bigInteger('total_watch_time')->default(0)->comment('Total watch time in seconds');
            $table->integer('average_watch_duration')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->integer('like_count')->default(0);
            $table->integer('share_count')->default(0);
            
            // SEO
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->json('keywords')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('slug');
            $table->index(['video_provider', 'provider_video_id']);
            $table->index(['series_id', 'season_number', 'episode_number']);
            $table->index('creator_id');
            $table->index(['is_published', 'published_at']);
            $table->index('access_level');
            $table->index('content_type');
            $table->index('upload_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_videos');
    }
};
