<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_video_series', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Basic Info
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('long_description')->nullable();
            
            // Assets (trailer foreign key added in separate migration)
            $table->text('poster_url')->nullable();
            $table->text('banner_url')->nullable();
            $table->unsignedBigInteger('trailer_video_id')->nullable();
            
            // Structure
            $table->integer('total_seasons')->default(1);
            $table->integer('total_episodes')->default(0);
            $table->enum('series_type', ['course', 'show', 'documentary', 'workshop_series'])->default('course');
            $table->boolean('is_ongoing')->default(false);
            $table->date('next_episode_date')->nullable();
            
            // Creator
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            
            // Access Control
            $table->enum('access_level', ['free', 'basic', 'premium', 'institutional'])->default('basic');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            
            // Engagement
            $table->bigInteger('view_count')->default(0);
            $table->integer('subscriber_count')->default(0);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('slug');
            $table->index('creator_id');
            $table->index(['is_published', 'published_at']);
            $table->index('series_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_video_series');
    }
};
