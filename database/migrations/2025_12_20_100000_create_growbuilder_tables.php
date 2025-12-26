<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Templates
        Schema::create('growbuilder_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category'); // business, restaurant, church, tutor, portfolio
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('structure_json'); // Default page structure
            $table->json('default_styles')->nullable(); // Default colors, fonts
            $table->boolean('is_premium')->default(false);
            $table->integer('price')->default(0); // In Kwacha
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });

        // Sites
        Schema::create('growbuilder_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('growbuilder_templates')->nullOnDelete();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->json('settings')->nullable(); // Site-wide settings
            $table->json('theme')->nullable(); // Colors, fonts, etc.
            $table->json('social_links')->nullable();
            $table->json('contact_info')->nullable(); // Phone, email, address
            $table->json('business_hours')->nullable();
            $table->json('seo_settings')->nullable(); // Global SEO
            $table->enum('status', ['draft', 'published', 'suspended'])->default('draft');
            $table->enum('plan', ['starter', 'business', 'pro'])->default('starter');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('subdomain');
        });

        // Pages
        Schema::create('growbuilder_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->json('content_json'); // Page sections/blocks
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('is_homepage')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('show_in_nav')->default(true);
            $table->integer('nav_order')->default(0);
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
            $table->index(['site_id', 'is_published']);
        });

        // Section Types (reusable section definitions)
        Schema::create('growbuilder_section_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->string('category'); // hero, content, gallery, contact, etc.
            $table->json('default_content'); // Default structure
            $table->json('schema'); // Editable fields definition
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Media Library
        Schema::create('growbuilder_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->string('filename');
            $table->string('original_name');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('mime_type');
            $table->integer('size'); // bytes
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->json('variants')->nullable(); // Thumbnail, medium, etc.
            $table->timestamps();

            $table->index('site_id');
        });

        // Forms & Submissions
        Schema::create('growbuilder_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->json('fields'); // Form field definitions
            $table->string('submit_button_text')->default('Submit');
            $table->text('success_message')->nullable();
            $table->string('notification_email')->nullable();
            $table->boolean('send_whatsapp')->default(false);
            $table->string('whatsapp_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
        });

        Schema::create('growbuilder_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('growbuilder_forms')->onDelete('cascade');
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->json('data');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_spam')->default(false);
            $table->timestamps();

            $table->index(['site_id', 'is_read']);
        });

        // Analytics (basic)
        Schema::create('growbuilder_page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->onDelete('cascade');
            $table->foreignId('page_id')->nullable()->constrained('growbuilder_pages')->onDelete('cascade');
            $table->string('path');
            $table->string('referrer')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->string('device_type')->nullable(); // mobile, tablet, desktop
            $table->date('viewed_date');
            $table->timestamps();

            $table->index(['site_id', 'viewed_date']);
            $table->index(['site_id', 'page_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growbuilder_page_views');
        Schema::dropIfExists('growbuilder_form_submissions');
        Schema::dropIfExists('growbuilder_forms');
        Schema::dropIfExists('growbuilder_media');
        Schema::dropIfExists('growbuilder_section_types');
        Schema::dropIfExists('growbuilder_pages');
        Schema::dropIfExists('growbuilder_sites');
        Schema::dropIfExists('growbuilder_templates');
    }
};
