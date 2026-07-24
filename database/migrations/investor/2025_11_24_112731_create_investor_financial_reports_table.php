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
        Schema::create('investor_financial_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('report_type', 100); // 'quarterly', 'annual', 'monthly'
            $table->string('report_period', 50); // 'Q1 2025', '2024', 'Jan 2025'
            $table->date('report_date');
            $table->decimal('total_revenue', 15, 2);
            $table->decimal('total_expenses', 15, 2);
            $table->decimal('net_profit', 15, 2);
            $table->decimal('gross_margin', 5, 2)->nullable();
            $table->decimal('operating_margin', 5, 2)->nullable();
            $table->decimal('net_margin', 5, 2)->nullable();
            $table->decimal('cash_flow', 15, 2)->nullable();
            $table->integer('total_members')->nullable();
            $table->integer('active_members')->nullable();
            $table->decimal('monthly_recurring_revenue', 15, 2)->nullable();
            $table->decimal('customer_acquisition_cost', 10, 2)->nullable();
            $table->decimal('lifetime_value', 10, 2)->nullable();
            $table->decimal('churn_rate', 5, 2)->nullable();
            $table->decimal('growth_rate', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['report_type']);
            $table->index(['report_date']);
            $table->index(['published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_financial_reports');
    }
};
