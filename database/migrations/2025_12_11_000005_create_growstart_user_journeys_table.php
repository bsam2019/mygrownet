<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstart_user_journeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('industry_id')->constrained('growstart_industries');
            $table->foreignId('country_id')->constrained('growstart_countries');
            $table->string('business_name');
            $table->text('business_description')->nullable();
            $table->foreignId('current_stage_id')->constrained('growstart_stages');
            $table->timestamp('started_at');
            $table->date('target_launch_date')->nullable();
            $table->enum('status', ['active', 'paused', 'completed', 'archived'])->default('active');
            $table->boolean('is_premium')->default(false);
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['country_id', 'industry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstart_user_journeys');
    }
};
