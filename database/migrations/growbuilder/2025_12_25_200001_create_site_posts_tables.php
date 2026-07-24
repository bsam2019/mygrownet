<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Site Post Categories
        Schema::create('site_post_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('site_post_categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->unique(['site_id', 'slug']);
        });

        // Site Posts (blog/news for each site)
        Schema::create('site_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('site_users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->enum('visibility', ['public', 'members', 'private'])->default('public');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->boolean('comments_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
            $table->index(['site_id', 'status', 'published_at']);
        });

        // Post-Category Pivot
        Schema::create('site_post_category', function (Blueprint $table) {
            $table->foreignId('site_post_id')->constrained('site_posts')->cascadeOnDelete();
            $table->foreignId('site_post_category_id')->constrained('site_post_categories')->cascadeOnDelete();

            $table->primary(['site_post_id', 'site_post_category_id']);
        });

        // Site Comments
        Schema::create('site_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->cascadeOnDelete();
            $table->foreignId('post_id')->constrained('site_posts')->cascadeOnDelete();
            $table->foreignId('site_user_id')->nullable()->constrained('site_users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('site_comments')->cascadeOnDelete();
            $table->string('author_name', 100)->nullable(); // For guests
            $table->string('author_email')->nullable(); // For guests
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'spam', 'trash'])->default('pending');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['post_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_comments');
        Schema::dropIfExists('site_post_category');
        Schema::dropIfExists('site_posts');
        Schema::dropIfExists('site_post_categories');
    }
};
