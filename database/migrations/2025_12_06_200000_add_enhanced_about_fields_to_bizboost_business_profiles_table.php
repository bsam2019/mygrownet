<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bizboost_business_profiles', function (Blueprint $table) {
            // Enhanced About Us fields
            $table->text('business_story')->nullable()->after('about');
            $table->string('mission')->nullable()->after('business_story');
            $table->string('vision')->nullable()->after('mission');
            $table->year('founding_year')->nullable()->after('vision');
            $table->json('business_hours')->nullable()->after('founding_year');
            $table->json('team_members')->nullable()->after('business_hours');
            $table->json('achievements')->nullable()->after('team_members');
            
            // Additional section toggles
            $table->boolean('show_services')->default(true)->after('show_products');
            $table->boolean('show_gallery')->default(false)->after('show_services');
            $table->boolean('show_testimonials')->default(false)->after('show_gallery');
            $table->boolean('show_business_hours')->default(true)->after('show_testimonials');
            
            // Services data
            $table->json('services')->nullable()->after('achievements');
            
            // Testimonials data
            $table->json('testimonials')->nullable()->after('services');
        });
    }

    public function down(): void
    {
        Schema::table('bizboost_business_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'business_story',
                'mission',
                'vision',
                'founding_year',
                'business_hours',
                'team_members',
                'achievements',
                'show_services',
                'show_gallery',
                'show_testimonials',
                'show_business_hours',
                'services',
                'testimonials',
            ]);
        });
    }
};
