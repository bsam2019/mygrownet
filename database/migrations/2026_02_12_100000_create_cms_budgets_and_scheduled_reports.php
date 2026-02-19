<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Budgets table
        if (!Schema::hasTable('cms_budgets')) {
            Schema::create('cms_budgets', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('name');
                        $table->string('period_type'); // monthly, quarterly, yearly
                        $table->date('start_date');
                        $table->date('end_date');
                        $table->decimal('total_budget', 15, 2)->default(0);
                        $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft');
                        $table->text('notes')->nullable();
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
            
                        $table->index(['company_id', 'status']);
                        $table->index(['company_id', 'start_date', 'end_date']);
                    });
        }

        // Budget line items
        if (!Schema::hasTable('cms_budget_items')) {
            Schema::create('cms_budget_items', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('budget_id')->constrained('cms_budgets')->onDelete('cascade');
                        $table->string('category'); // revenue, expense_category_name, etc.
                        $table->string('item_type'); // revenue, expense
                        $table->decimal('budgeted_amount', 15, 2);
                        $table->text('notes')->nullable();
                        $table->timestamps();
            
                        $table->index(['budget_id', 'item_type']);
                    });
        }

        // Scheduled reports
        if (!Schema::hasTable('cms_scheduled_reports')) {
            Schema::create('cms_scheduled_reports', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('name');
                        $table->string('report_type'); // sales, payments, expenses, profitLoss, cashbook, tax
                        $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('monthly');
                        $table->string('day_of_week')->nullable(); // For weekly: monday, tuesday, etc.
                        $table->integer('day_of_month')->nullable(); // For monthly: 1-31
                        $table->time('time_of_day')->default('08:00:00');
                        $table->json('recipients'); // Array of email addresses
                        $table->enum('format', ['csv', 'pdf'])->default('csv');
                        $table->boolean('is_active')->default(true);
                        $table->timestamp('last_sent_at')->nullable();
                        $table->timestamp('next_run_at')->nullable();
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
            
                        $table->index(['company_id', 'is_active', 'next_run_at']);
                    });
        }

        // Scheduled report logs
        if (!Schema::hasTable('cms_scheduled_report_logs')) {
            Schema::create('cms_scheduled_report_logs', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('scheduled_report_id')->constrained('cms_scheduled_reports')->onDelete('cascade');
                        $table->enum('status', ['success', 'failed'])->default('success');
                        $table->text('error_message')->nullable();
                        $table->integer('recipients_count')->default(0);
                        $table->timestamp('sent_at');
                        $table->timestamps();
            
                        $table->index(['scheduled_report_id', 'sent_at']);
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_scheduled_report_logs');
        Schema::dropIfExists('cms_scheduled_reports');
        Schema::dropIfExists('cms_budget_items');
        Schema::dropIfExists('cms_budgets');
    }
};
