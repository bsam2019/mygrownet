<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safety Incidents
        Schema::create('cms_safety_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('incident_number')->unique();
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->text('location');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
            $table->foreignId('project_id')->nullable()->constrained('cms_projects');
            $table->enum('incident_type', ['injury', 'near_miss', 'property_damage', 'environmental', 'security'])->default('injury');
            $table->enum('severity', ['minor', 'moderate', 'serious', 'fatal'])->default('minor');
            $table->text('description');
            $table->text('immediate_action_taken')->nullable();
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('investigated_by')->nullable()->constrained('users');
            $table->text('investigation_findings')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_actions')->nullable();
            $table->text('preventive_actions')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->enum('status', ['reported', 'under_investigation', 'action_pending', 'closed'])->default('reported');
            $table->integer('days_lost')->default(0);
            $table->decimal('cost_impact', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'incident_date']);
            $table->index(['incident_type']);
            $table->index(['severity']);
            $table->index(['status']);
        });

        // Incident Involved Persons
        Schema::create('cms_incident_involved_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('cms_safety_incidents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('person_name');
            $table->enum('involvement_type', ['injured', 'witness', 'reporter'])->default('witness');
            $table->text('injury_description')->nullable();
            $table->enum('injury_severity', ['minor', 'moderate', 'serious', 'fatal'])->nullable();
            $table->text('treatment_given')->nullable();
            $table->boolean('hospitalized')->default(false);
            $table->text('statement')->nullable();
            $table->timestamps();
            
            $table->index(['incident_id']);
        });

        // Safety Inspections
        Schema::create('cms_safety_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('inspection_number')->unique();
            $table->date('inspection_date');
            $table->enum('inspection_type', ['routine', 'surprise', 'post_incident', 'pre_project'])->default('routine');
            $table->text('location');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
            $table->foreignId('project_id')->nullable()->constrained('cms_projects');
            $table->foreignId('inspector_id')->constrained('users');
            $table->json('checklist_items')->nullable();
            $table->integer('items_checked')->default(0);
            $table->integer('items_passed')->default(0);
            $table->integer('items_failed')->default(0);
            $table->enum('overall_rating', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->text('observations')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('hazards_identified')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'follow_up_required'])->default('scheduled');
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'inspection_date']);
            $table->index(['status']);
        });

        // PPE (Personal Protective Equipment) Tracking
        Schema::create('cms_ppe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->enum('item_type', ['helmet', 'gloves', 'boots', 'vest', 'goggles', 'mask', 'harness', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->string('size')->nullable();
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('reorder_level')->default(10);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->integer('lifespan_months')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['company_id', 'is_active']);
        });

        // PPE Distribution
        Schema::create('cms_ppe_distribution', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('distribution_number')->unique();
            $table->foreignId('ppe_item_id')->constrained('cms_ppe_items');
            $table->foreignId('user_id')->constrained('users');
            $table->date('issued_date');
            $table->integer('quantity');
            $table->date('expected_return_date')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->enum('condition_on_return', ['good', 'fair', 'damaged', 'lost'])->nullable();
            $table->enum('status', ['issued', 'returned', 'lost', 'damaged'])->default('issued');
            $table->foreignId('issued_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'issued_date']);
            $table->index(['ppe_item_id']);
            $table->index(['user_id']);
        });

        // Safety Training
        Schema::create('cms_safety_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('training_code')->unique();
            $table->string('training_name');
            $table->text('description')->nullable();
            $table->enum('training_type', ['induction', 'refresher', 'specialized', 'certification'])->default('induction');
            $table->integer('duration_hours')->nullable();
            $table->integer('validity_months')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['company_id', 'is_active']);
        });

        // Training Records
        Schema::create('cms_training_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('record_number')->unique();
            $table->foreignId('training_id')->constrained('cms_safety_training');
            $table->foreignId('user_id')->constrained('users');
            $table->date('training_date');
            $table->foreignId('trainer_id')->nullable()->constrained('users');
            $table->string('training_provider')->nullable();
            $table->enum('completion_status', ['completed', 'in_progress', 'failed', 'expired'])->default('completed');
            $table->integer('score')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('certificate_file_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'training_date']);
            $table->index(['training_id']);
            $table->index(['user_id']);
            $table->index(['expiry_date']);
        });

        // Risk Assessments
        Schema::create('cms_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('assessment_number')->unique();
            $table->string('assessment_title');
            $table->text('activity_description');
            $table->text('location');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
            $table->foreignId('project_id')->nullable()->constrained('cms_projects');
            $table->date('assessment_date');
            $table->foreignId('assessed_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['draft', 'approved', 'expired'])->default('draft');
            $table->date('review_date')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'assessment_date']);
            $table->index(['status']);
        });

        // Risk Assessment Hazards
        Schema::create('cms_risk_hazards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_assessment_id')->constrained('cms_risk_assessments')->cascadeOnDelete();
            $table->text('hazard_description');
            $table->enum('hazard_type', ['physical', 'chemical', 'biological', 'ergonomic', 'psychosocial'])->default('physical');
            $table->integer('likelihood')->default(1); // 1-5
            $table->integer('severity')->default(1); // 1-5
            $table->integer('risk_score')->default(1); // likelihood * severity
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->text('existing_controls')->nullable();
            $table->text('additional_controls')->nullable();
            $table->integer('residual_likelihood')->nullable();
            $table->integer('residual_severity')->nullable();
            $table->integer('residual_risk_score')->nullable();
            $table->enum('residual_risk_level', ['low', 'medium', 'high', 'critical'])->nullable();
            $table->timestamps();
            
            $table->index(['risk_assessment_id']);
        });

        // Safety Compliance
        Schema::create('cms_safety_compliance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('compliance_item');
            $table->text('description')->nullable();
            $table->enum('compliance_type', ['permit', 'license', 'certification', 'inspection', 'audit'])->default('permit');
            $table->string('issuing_authority')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['valid', 'expiring_soon', 'expired', 'pending'])->default('valid');
            $table->string('document_file_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'expiry_date']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_safety_compliance');
        Schema::dropIfExists('cms_risk_hazards');
        Schema::dropIfExists('cms_risk_assessments');
        Schema::dropIfExists('cms_training_records');
        Schema::dropIfExists('cms_safety_training');
        Schema::dropIfExists('cms_ppe_distribution');
        Schema::dropIfExists('cms_ppe_items');
        Schema::dropIfExists('cms_safety_inspections');
        Schema::dropIfExists('cms_incident_involved_persons');
        Schema::dropIfExists('cms_safety_incidents');
    }
};
