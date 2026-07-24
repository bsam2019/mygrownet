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
        Schema::create('company_metrics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date');
            $table->integer('total_members');
            $table->integer('active_members');
            $table->decimal('total_revenue', 15, 2);
            $table->decimal('monthly_revenue', 15, 2);
            $table->decimal('quarterly_revenue', 15, 2);
            $table->decimal('annual_revenue', 15, 2);
            $table->decimal('platform_valuation', 15, 2)->nullable();
            $table->decimal('market_cap', 15, 2)->nullable();
            $table->decimal('revenue_growth_rate', 5, 2)->nullable();
            $table->decimal('member_growth_rate', 5, 2)->nullable();
            $table->decimal('retention_rate', 5, 2)->nullable();
            $table->decimal('churn_rate', 5, 2)->nullable();
            $table->decimal('average_revenue_per_user', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique('snapshot_date');
            $table->index(['snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_metrics_snapshots');
    }
};
