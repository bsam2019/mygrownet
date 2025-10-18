<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('team_volume', 15, 2);
            $table->decimal('bonus_rate', 5, 2); // Percentage rate
            $table->decimal('bonus_amount', 10, 2);
            $table->enum('status', ['eligible', 'paid', 'cancelled'])->default('eligible');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['user_id', 'period_start', 'period_end']);
            $table->index(['status', 'period_start']);
            $table->unique(['user_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_bonuses');
    }
};