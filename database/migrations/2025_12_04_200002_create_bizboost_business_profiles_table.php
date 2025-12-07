<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->string('hero_image_path')->nullable();
            $table->string('banner_image_path')->nullable();
            $table->text('about')->nullable();
            $table->text('tagline')->nullable();
            $table->string('contact_email')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('seo_meta')->nullable(); // title, description, keywords
            $table->json('theme_settings')->nullable(); // colors, fonts, etc.
            $table->boolean('show_products')->default(true);
            $table->boolean('show_contact_form')->default(true);
            $table->boolean('show_whatsapp_button')->default(true);
            $table->boolean('show_social_links')->default(true);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            
            $table->unique('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_business_profiles');
    }
};
