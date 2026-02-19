<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Report templates
        if (!Schema::hasTable('cms_report_templates')) {
            Schema::create('cms_report_templates', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                        $table->string('name');
                        $table->string('category'); // hr, payroll, attendance, performance, training
                        $table->text('description')->nullable();
                        $table->json('parameters'); // Report parameters/filters
                        $table->json('columns'); // Columns to display
                        $table->boolean('is_system')->default(false);
                        $table->foreignId('created_by')->nullable()->constrained('cms_users')->nullOnDelete();
                        $table->timestamps();
                        
                        $table->index(['company_id', 'category']);
                    });
        }

        // Saved reports
        if (!Schema::hasTable('cms_saved_reports')) {
            Schema::create('cms_saved_reports', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('template_id')->constrained('cms_report_templates')->cascadeOnDelete();
                        $table->foreignId('generated_by')->constrained('cms_users')->cascadeOnDelete();
                        $table->string('report_name');
                        $table->json('filters_used');
                        $table->date('date_from')->nullable();
                        $table->date('date_to')->nullable();
                        $table->string('file_path')->nullable();
                        $table->string('file_format')->default('pdf'); // pdf, excel, csv
                        $table->timestamps();
                        
                        $table->index(['template_id', 'generated_by']);
                    });
        }

        // Report schedules
        if (!Schema::hasTable('cms_report_schedules')) {
            Schema::create('cms_report_schedules', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('template_id')->constrained('cms_report_templates')->cascadeOnDelete();
                        $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
                        $table->string('schedule_name');
                        $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
                        $table->json('recipients'); // Email addresses
                        $table->json('filters');
                        $table->boolean('is_active')->default(true);
                        $table->timestamp('last_run_at')->nullable();
                        $table->timestamp('next_run_at')->nullable();
                        $table->timestamps();
                        
                        $table->index(['is_active', 'next_run_at']);
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_report_schedules');
        Schema::dropIfExists('cms_saved_reports');
        Schema::dropIfExists('cms_report_templates');
    }
};
