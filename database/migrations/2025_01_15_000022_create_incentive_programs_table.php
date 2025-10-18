<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incentive_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('type', ['competition', 'raffle', 'bonus_multiplier', 'recognition']);
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->json('eligibility_criteria')->nullable();
            $table->json('rewards')->nullable();
            $table->integer('max_winners')->default(10);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_pattern')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->decimal('total_budget', 12, 2)->default(0);
            $table->decimal('spent_budget', 12, 2)->default(0);
            $table->json('participation_requirements')->nullable();
            $table->json('tier_restrictions')->nullable();
            $table->json('bonus_multipliers')->nullable();
            $table->timestamps();

            $table->index(['type', 'period_type']);
            $table->index(['start_date', 'end_date']);
            $table->index(['is_active', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_programs');
    }
};