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
        Schema::create('member_analytics_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('metric_type', 50);
            $table->decimal('metric_value', 15, 2)->nullable();
            $table->json('metric_data')->nullable();
            $table->string('period', 20)->nullable(); // 'daily', 'weekly', 'monthly', 'all_time'
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'metric_type', 'period'], 'idx_user_metric');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_analytics_cache');
    }
};
