<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('network_size');
            $table->string('role');
            $table->integer('bp_required');
            $table->integer('lp_required');
            $table->string('min_time');
            $table->text('additional_requirements');
            $table->string('milestone_bonus')->nullable();
            $table->string('profit_share_multiplier');
            $table->string('commission_rate');
            $table->string('color');
            $table->json('benefits');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_levels');
    }
};
