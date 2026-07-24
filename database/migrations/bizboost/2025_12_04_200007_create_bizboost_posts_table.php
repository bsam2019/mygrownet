<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('caption');
            $table->string('status')->default('draft'); // draft, scheduled, publishing, published, failed
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('platform_targets')->nullable(); // ['facebook', 'instagram']
            $table->json('external_ids')->nullable(); // {'facebook': '123', 'instagram': '456'}
            $table->json('analytics')->nullable(); // engagement metrics
            $table->string('post_type')->default('standard'); // standard, story, reel
            $table->foreignId('template_id')->nullable()->constrained('bizboost_templates')->onDelete('set null');
            $table->foreignId('campaign_id')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
            
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'scheduled_at']);
            $table->index(['business_id', 'published_at']);
            $table->index('campaign_id');
        });

        Schema::create('bizboost_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('bizboost_posts')->onDelete('cascade');
            $table->string('type'); // image, video
            $table->string('path');
            $table->string('filename');
            $table->integer('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration')->nullable(); // for videos, in seconds
            $table->string('thumbnail_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['post_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_post_media');
        Schema::dropIfExists('bizboost_posts');
    }
};
