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
        Schema::create('bizboost_posting_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->cascadeOnDelete();
            $table->string('day_type', 20); // 'weekday', 'weekend', or specific day like 'monday'
            $table->json('times'); // Array of times like ["09:00", "12:00", "18:00"]
            $table->timestamps();

            $table->unique(['business_id', 'day_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bizboost_posting_times');
    }
};
