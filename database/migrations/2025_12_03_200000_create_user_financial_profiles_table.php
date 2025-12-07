<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_financial_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('setup_completed')->default(false);
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('budget_method', 50)->default('50/30/20');
            $table->string('budget_period', 20)->default('monthly');
            $table->string('currency', 3)->default('ZMW');
            $table->json('alert_preferences')->nullable();
            $table->timestamps();
        });

        Schema::create('income_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('source_type', 50);
            $table->decimal('amount', 15, 2);
            $table->string('frequency', 20);
            $table->date('next_expected_date')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        Schema::create('user_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category_name', 100);
            $table->string('category_type', 50);
            $table->decimal('estimated_monthly_amount', 15, 2)->default(0);
            $table->boolean('is_fixed')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('goal_name', 200);
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->string('priority', 20)->default('medium');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
        Schema::dropIfExists('user_expense_categories');
        Schema::dropIfExists('income_sources');
        Schema::dropIfExists('user_financial_profiles');
    }
};
