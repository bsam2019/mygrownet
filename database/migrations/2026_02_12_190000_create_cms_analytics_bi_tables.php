<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Custom dashboards table
        if (!Schema::hasTable('cms_dashboards')) {
            Schema::create('cms_dashboards', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('name');
                        $table->text('description')->nullable();
                        $table->json('layout')->nullable(); // Widget positions and sizes
                        $table->boolean('is_default')->default(false);
                        $table->boolean('is_shared')->default(false);
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'created_by']);
                    });
        }

        // Dashboard widgets table
        if (!Schema::hasTable('cms_dashboard_widgets')) {
            Schema::create('cms_dashboard_widgets', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('dashboard_id')->constrained('cms_dashboards')->onDelete('cascade');
                        $table->string('widget_type'); // chart, kpi, table, etc.
                        $table->string('title');
                        $table->json('config'); // Chart type, data source, filters
                        $table->integer('position_x')->default(0);
                        $table->integer('position_y')->default(0);
                        $table->integer('width')->default(4);
                        $table->integer('height')->default(3);
                        $table->timestamps();
                        
                        $table->index('dashboard_id');
                    });
        }

        // KPIs table
        if (!Schema::hasTable('cms_kpis')) {
            Schema::create('cms_kpis', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('name');
                        $table->text('description')->nullable();
                        $table->string('metric_type'); // revenue, profit, customer_count, etc.
                        $table->string('calculation_method'); // sum, average, count, etc.
                        $table->json('data_source'); // Table and field configuration
                        $table->string('unit')->nullable(); // K, %, count
                        $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
                        $table->boolean('is_active')->default(true);
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'is_active']);
                    });
        }

        // KPI values table (historical tracking)
        if (!Schema::hasTable('cms_kpi_values')) {
            Schema::create('cms_kpi_values', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('kpi_id')->constrained('cms_kpis')->onDelete('cascade');
                        $table->date('period_date');
                        $table->decimal('value', 15, 2);
                        $table->decimal('target_value', 15, 2)->nullable();
                        $table->decimal('variance', 15, 2)->nullable();
                        $table->decimal('variance_percentage', 5, 2)->nullable();
                        $table->timestamps();
                        
                        $table->index(['kpi_id', 'period_date']);
                        $table->unique(['kpi_id', 'period_date']);
                    });
        }

        // Goals table
        if (!Schema::hasTable('cms_goals')) {
            Schema::create('cms_goals', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('name');
                        $table->text('description')->nullable();
                        $table->enum('goal_type', ['revenue', 'profit', 'customers', 'jobs', 'custom'])->default('custom');
                        $table->decimal('target_value', 15, 2);
                        $table->decimal('current_value', 15, 2)->default(0);
                        $table->string('unit')->default('K');
                        $table->date('start_date');
                        $table->date('end_date');
                        $table->enum('status', ['not_started', 'in_progress', 'achieved', 'failed'])->default('not_started');
                        $table->foreignId('assigned_to')->nullable()->constrained('cms_users')->onDelete('set null');
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'status']);
                        $table->index(['company_id', 'end_date']);
                    });
        }

        // Goal milestones table
        if (!Schema::hasTable('cms_goal_milestones')) {
            Schema::create('cms_goal_milestones', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('goal_id')->constrained('cms_goals')->onDelete('cascade');
                        $table->string('name');
                        $table->decimal('target_value', 15, 2);
                        $table->date('target_date');
                        $table->boolean('is_completed')->default(false);
                        $table->timestamp('completed_at')->nullable();
                        $table->timestamps();
                        
                        $table->index('goal_id');
                    });
        }

        // Trend analysis table
        if (!Schema::hasTable('cms_trend_analysis')) {
            Schema::create('cms_trend_analysis', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('metric_name');
                        $table->string('period_type'); // daily, weekly, monthly
                        $table->date('period_start');
                        $table->date('period_end');
                        $table->decimal('value', 15, 2);
                        $table->decimal('previous_value', 15, 2)->nullable();
                        $table->decimal('change_amount', 15, 2)->nullable();
                        $table->decimal('change_percentage', 5, 2)->nullable();
                        $table->string('trend_direction')->nullable(); // up, down, stable
                        $table->timestamps();
                        
                        $table->index(['company_id', 'metric_name', 'period_start']);
                    });
        }

        // Forecasts table
        if (!Schema::hasTable('cms_forecasts')) {
            Schema::create('cms_forecasts', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('forecast_type'); // revenue, expenses, profit
                        $table->string('model_type')->default('linear'); // linear, exponential, moving_average
                        $table->date('forecast_date');
                        $table->decimal('forecasted_value', 15, 2);
                        $table->decimal('confidence_lower', 15, 2)->nullable();
                        $table->decimal('confidence_upper', 15, 2)->nullable();
                        $table->decimal('actual_value', 15, 2)->nullable();
                        $table->decimal('accuracy_percentage', 5, 2)->nullable();
                        $table->json('model_parameters')->nullable();
                        $table->timestamp('calculated_at');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'forecast_type', 'forecast_date']);
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_forecasts');
        Schema::dropIfExists('cms_trend_analysis');
        Schema::dropIfExists('cms_goal_milestones');
        Schema::dropIfExists('cms_goals');
        Schema::dropIfExists('cms_kpi_values');
        Schema::dropIfExists('cms_kpis');
        Schema::dropIfExists('cms_dashboard_widgets');
        Schema::dropIfExists('cms_dashboards');
    }
};
