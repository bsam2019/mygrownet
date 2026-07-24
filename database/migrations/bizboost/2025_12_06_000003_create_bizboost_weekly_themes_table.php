<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bizboost_weekly_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->cascadeOnDelete();
            $table->date('week_start'); // Monday of the week
            $table->string('theme', 255);
            $table->text('description')->nullable();
            $table->string('color', 20)->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'week_start']);
            $table->index(['business_id', 'week_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bizboost_weekly_themes');
    }
};
