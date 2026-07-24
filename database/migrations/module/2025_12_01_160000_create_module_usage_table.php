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
        Schema::create('module_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module_id', 50);
            $table->string('usage_type', 50); // e.g., 'transactions', 'storage', 'api_calls'
            $table->integer('count')->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();

            // Unique constraint for user + module + type + period
            $table->unique(['user_id', 'module_id', 'usage_type', 'period_start'], 'unique_usage');
            
            // Index for efficient queries
            $table->index(['module_id', 'period_start']);
            $table->index(['user_id', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_usage');
    }
};
