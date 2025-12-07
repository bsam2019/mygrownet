<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('industry')->nullable(); // boutique, salon, restaurant, etc.
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->default('Zambia');
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('timezone')->default('Africa/Lusaka');
            $table->string('locale')->default('en');
            $table->string('currency')->default('ZMW');
            $table->json('social_links')->nullable(); // facebook, instagram, twitter, etc.
            $table->json('business_hours')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('onboarding_completed')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index('industry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_businesses');
    }
};
