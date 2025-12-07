<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Global templates (system-provided)
        Schema::create('bizboost_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category'); // flyer, menu, promotion, poster, pricing, social
            $table->string('industry')->nullable(); // boutique, salon, restaurant, etc.
            $table->json('template_data'); // design JSON with placeholders
            $table->string('thumbnail_path')->nullable();
            $table->string('preview_path')->nullable();
            $table->integer('width')->default(1080);
            $table->integer('height')->default(1080);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('usage_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index(['industry', 'is_active']);
            $table->index('is_featured');
        });

        // User-created custom templates
        Schema::create('bizboost_custom_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->foreignId('base_template_id')->nullable()->constrained('bizboost_templates')->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->json('template_data');
            $table->string('thumbnail_path')->nullable();
            $table->integer('width')->default(1080);
            $table->integer('height')->default(1080);
            $table->timestamps();
            
            $table->index(['business_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_custom_templates');
        Schema::dropIfExists('bizboost_templates');
    }
};
