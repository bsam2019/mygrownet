<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('industry'); // consulting, restaurant, salon, law, gym, etc.
            $table->string('thumbnail')->nullable(); // Preview image path
            $table->json('theme')->nullable(); // Color scheme, fonts, etc.
            $table->json('settings')->nullable(); // Navigation, footer settings
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('usage_count')->default(0); // Track popularity
            $table->timestamps();
        });

        Schema::create('site_template_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_template_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->boolean('is_homepage')->default(false);
            $table->boolean('show_in_nav')->default(true);
            $table->json('content'); // Sections array
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Industry categories for filtering
        Schema::create('site_template_industries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_template_pages');
        Schema::dropIfExists('site_templates');
        Schema::dropIfExists('site_template_industries');
    }
};
