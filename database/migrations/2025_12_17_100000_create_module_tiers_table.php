<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('module_id', 50);
            $table->string('tier_key', 50);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_annual', 10, 2)->default(0);
            $table->string('currency', 3)->default('ZMW');
            $table->integer('user_limit')->nullable();
            $table->integer('storage_limit_mb')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['module_id', 'tier_key']);
            $table->index('module_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_tiers');
    }
};
