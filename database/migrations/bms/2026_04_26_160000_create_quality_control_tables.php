<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quality Inspection Checklists
        Schema::create('cms_quality_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('checklist_name');
            $table->text('description')->nullable();
            $table->enum('checklist_type', ['incoming', 'in_process', 'final', 'customer_acceptance'])->default('final');
            $table->boolean('is_template')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['company_id', 'is_active']);
        });

        // Quality Checklist Items
        Schema::create('cms_quality_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('cms_quality_checklists')->cascadeOnDelete();
            $table->string('item_description');
            $table->text('acceptance_criteria')->nullable();
            $table->enum('measurement_type', ['pass_fail', 'measurement', 'visual', 'count'])->default('pass_fail');
            $table->string('unit_of_measure')->nullable();
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->boolean('is_critical')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['checklist_id']);
        });

        // Quality Inspections
        Schema::create('cms_quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('inspection_number')->unique();
            $table->foreignId('checklist_id')->constrained('cms_quality_checklists');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
            $table->foreignId('production_order_id')->nullable()->constrained('cms_production_orders');
            $table->date('inspection_date');
            $table->foreignId('inspector_id')->constrained('users');
            $table->enum('inspection_stage', ['incoming', 'in_process', 'final', 'customer'])->default('final');
            $table->enum('overall_result', ['passed', 'failed', 'conditional'])->default('passed');
            $table->integer('items_checked')->default(0);
            $table->integer('items_passed')->default(0);
            $table->integer('items_failed')->default(0);
            $table->text('general_observations')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'inspection_date']);
            $table->index(['overall_result']);
        });

        // Quality Inspection Results
        Schema::create('cms_quality_inspection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('cms_quality_inspections')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('cms_quality_checklist_items');
            $table->enum('result', ['pass', 'fail', 'na'])->default('pass');
            $table->decimal('measured_value', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['inspection_id']);
        });

        // Non-Conformance Reports (NCR)
        Schema::create('cms_non_conformances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('ncr_number')->unique();
            $table->date('reported_date');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
            $table->foreignId('production_order_id')->nullable()->constrained('cms_production_orders');
            $table->foreignId('inspection_id')->nullable()->constrained('cms_quality_inspections');
            $table->enum('ncr_type', ['material', 'workmanship', 'design', 'process', 'documentation'])->default('workmanship');
            $table->enum('severity', ['minor', 'major', 'critical'])->default('minor');
            $table->text('description');
            $table->text('root_cause')->nullable();
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->enum('status', ['open', 'under_investigation', 'action_pending', 'resolved', 'closed'])->default('open');
            $table->date('target_closure_date')->nullable();
            $table->date('actual_closure_date')->nullable();
            $table->decimal('cost_impact', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'reported_date']);
            $table->index(['status']);
            $table->index(['severity']);
        });

        // Corrective Actions
        Schema::create('cms_corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('non_conformance_id')->constrained('cms_non_conformances')->cascadeOnDelete();
            $table->text('action_description');
            $table->enum('action_type', ['immediate', 'corrective', 'preventive'])->default('corrective');
            $table->foreignId('responsible_person_id')->constrained('users');
            $table->date('target_date');
            $table->date('completion_date')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'verified'])->default('planned');
            $table->text('verification_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->index(['non_conformance_id']);
            $table->index(['status']);
        });

        // Customer Complaints
        Schema::create('cms_customer_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('complaint_number')->unique();
            $table->date('complaint_date');
            $table->foreignId('customer_id')->constrained('cms_customers');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
            $table->enum('complaint_type', ['quality', 'delivery', 'service', 'pricing', 'other'])->default('quality');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('complaint_description');
            $table->text('customer_expectation')->nullable();
            $table->foreignId('received_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->text('investigation_findings')->nullable();
            $table->text('resolution_action')->nullable();
            $table->enum('status', ['received', 'investigating', 'resolved', 'closed'])->default('received');
            $table->date('target_resolution_date')->nullable();
            $table->date('actual_resolution_date')->nullable();
            $table->enum('customer_satisfaction', ['satisfied', 'partially_satisfied', 'unsatisfied'])->nullable();
            $table->text('customer_feedback')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'complaint_date']);
            $table->index(['customer_id']);
            $table->index(['status']);
        });

        // Rework Tracking
        Schema::create('cms_rework_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('rework_number')->unique();
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
            $table->foreignId('production_order_id')->nullable()->constrained('cms_production_orders');
            $table->foreignId('non_conformance_id')->nullable()->constrained('cms_non_conformances');
            $table->date('rework_date');
            $table->text('rework_description');
            $table->text('reason');
            $table->decimal('rework_hours', 10, 2)->nullable();
            $table->decimal('rework_cost', 10, 2)->nullable();
            $table->foreignId('performed_by')->constrained('users');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'verified'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'rework_date']);
            $table->index(['status']);
        });

        // Quality Metrics
        Schema::create('cms_quality_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->date('metric_date');
            $table->enum('metric_period', ['daily', 'weekly', 'monthly', 'quarterly'])->default('monthly');
            $table->integer('total_inspections')->default(0);
            $table->integer('passed_inspections')->default(0);
            $table->integer('failed_inspections')->default(0);
            $table->decimal('pass_rate', 5, 2)->default(0);
            $table->integer('total_ncrs')->default(0);
            $table->integer('open_ncrs')->default(0);
            $table->integer('closed_ncrs')->default(0);
            $table->integer('total_complaints')->default(0);
            $table->integer('resolved_complaints')->default(0);
            $table->decimal('rework_hours', 10, 2)->default(0);
            $table->decimal('rework_cost', 10, 2)->default(0);
            $table->decimal('defect_rate', 5, 2)->default(0);
            $table->decimal('customer_satisfaction_score', 5, 2)->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'metric_date', 'metric_period']);
            $table->index(['company_id', 'metric_date']);
        });

        // Quality Certifications
        Schema::create('cms_quality_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('certification_name');
            $table->string('certification_number')->nullable();
            $table->enum('certification_type', ['iso', 'industry_specific', 'product', 'other'])->default('iso');
            $table->string('issuing_body')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'expired', 'suspended', 'pending'])->default('active');
            $table->string('certificate_file_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'expiry_date']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_quality_certifications');
        Schema::dropIfExists('cms_quality_metrics');
        Schema::dropIfExists('cms_rework_records');
        Schema::dropIfExists('cms_customer_complaints');
        Schema::dropIfExists('cms_corrective_actions');
        Schema::dropIfExists('cms_non_conformances');
        Schema::dropIfExists('cms_quality_inspection_results');
        Schema::dropIfExists('cms_quality_inspections');
        Schema::dropIfExists('cms_quality_checklist_items');
        Schema::dropIfExists('cms_quality_checklists');
    }
};
