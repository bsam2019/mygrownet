<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstart_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon');
            $table->enum('criteria_type', ['stage_complete', 'tasks_complete', 'streak_days', 'journey_complete']);
            $table->string('criteria_value')->nullable();
            $table->integer('points')->default(10);
            $table->timestamps();
        });

        Schema::create('growstart_user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_journey_id')->constrained('growstart_user_journeys')->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('growstart_badges')->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['user_journey_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstart_user_badges');
        Schema::dropIfExists('growstart_badges');
    }
};
