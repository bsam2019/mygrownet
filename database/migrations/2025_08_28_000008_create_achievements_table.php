<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('category', ['referral', 'subscription', 'education', 'community', 'mentorship']);
            $table->string('badge_icon')->nullable();
            $table->string('badge_color')->default('#3b82f6');
            $table->integer('points')->default(0);
            $table->json('requirements'); // Conditions to unlock achievement
            $table->boolean('is_repeatable')->default(false);
            $table->integer('max_times')->nullable(); // For repeatable achievements
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};