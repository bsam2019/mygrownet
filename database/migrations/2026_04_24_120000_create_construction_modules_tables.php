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
        // ============================================
        // MODULE 1: PROJECT MANAGEMENT
        // ============================================
        
        // Projects table
        Schema::create('cms_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('cms_customers')->onDelete('set null');
            $table->string('project_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('site_location')->nullable();
            $table->string('site_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['planning', 'active', 'on_hold', 'completed', 'cancelled'])->default('planning');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->foreignId('project_manager_id')->nullable()->constrained('cms_employees')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'status']);
            $table->index('project_number');
        });

        // Project milestones
        Schema::create('cms_project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cms_projects')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('target_date');
            $table->date('actual_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'delayed'])->default('pending');
            $table->integer('order')->default(0);
            $table->decimal('payment_percentage', 5, 2)->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
        });

        // Site diary entries
        Schema::create('cms_site_diary_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cms_projects')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('cms_employees')->onDelete('cascade');
            $table->date('entry_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('weather', ['sunny', 'cloudy', 'rainy', 'stormy', 'hot', 'cold'])->nullable();
            $table->integer('workers_on_site')->default(0);
            $table->text('work_completed')->nullable();
            $table->text('materials_delivered')->nullable();
            $table->text('equipment_used')->nullable();
            $table->text('issues_delays')->nullable();
            $table->text('safety_incidents')->nullable();
            $table->text('visitors')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'entry_date']);
        });

        // Project documents
        Schema::create('cms_project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cms_projects')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('cms_employees')->onDelete('cascade');
            $table->string('document_type'); // drawing, contract, permit, photo, report
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->integer('file_size');
            $table->integer('version')->default(1);
            $table->enum('status', ['draft', 'approved', 'superseded'])->default('draft');
            $table->timestamps();
            
            $table->index(['project_id', 'document_type']);
        });

        // ============================================
        // MODULE 2: SUBCONTRACTOR MANAGEMENT
        // ============================================
        
        Schema::create('cms_subcontractors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('trade'); // electrical, plumbing, carpentry, etc.
            $table->text('specialization')->nullable();
            $table->decimal('rating', 3, 2)->default(0); // 0-5 stars
            $table->integer('completed_jobs')->default(0);
            $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active');
            $table->string('tax_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->json('certifications')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'status']);
            $table->index('trade');
        });

        Schema::create('cms_subcontractor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcontractor_id')->constrained('cms_subcontractors')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('cms_projects')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->onDelete('cascade');
            $table->string('work_description');
            $table->decimal('quoted_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['subcontractor_id', 'status']);
            $table->index('project_id');
            $table->index('job_id');
        });

        Schema::create('cms_subcontractor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcontractor_id')->constrained('cms_subcontractors')->onDelete('cascade');
            $table->foreignId('assignment_id')->nullable()->constrained('cms_subcontractor_assignments')->onDelete('set null');
            $table->string('payment_reference')->unique();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method'); // cash, bank_transfer, cheque
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();
            
            $table->index('subcontractor_id');
            $table->index('payment_date');
        });

        Schema::create('cms_subcontractor_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcontractor_id')->constrained('cms_subcontractors')->onDelete('cascade');
            $table->foreignId('assignment_id')->constrained('cms_subcontractor_assignments')->onDelete('cascade');
            $table->foreignId('rated_by')->constrained('cms_employees')->onDelete('cascade');
            $table->integer('quality_rating'); // 1-5
            $table->integer('timeliness_rating'); // 1-5
            $table->integer('communication_rating'); // 1-5
            $table->integer('professionalism_rating'); // 1-5
            $table->decimal('overall_rating', 3, 2); // calculated average
            $table->text('comments')->nullable();
            $table->timestamps();
            
            $table->index('subcontractor_id');
        });

        // ============================================
        // MODULE 3: EQUIPMENT MANAGEMENT
        // ============================================
        
        Schema::create('cms_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('equipment_code')->unique();
            $table->string('name');
            $table->string('category'); // machinery, tools, vehicles, safety_equipment
            $table->text('description')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->enum('ownership', ['owned', 'rented', 'leased'])->default('owned');
            $table->enum('status', ['available', 'in_use', 'maintenance', 'retired'])->default('available');
            $table->string('location')->nullable();
            $table->integer('useful_life_years')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->integer('maintenance_interval_days')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'status']);
            $table->index('category');
        });

        Schema::create('cms_equipment_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('cms_equipment')->onDelete('cascade');
            $table->foreignId('performed_by')->nullable()->constrained('cms_employees')->onDelete('set null');
            $table->date('maintenance_date');
            $table->enum('type', ['routine', 'repair', 'inspection', 'calibration'])->default('routine');
            $table->text('description');
            $table->decimal('cost', 15, 2)->default(0);
            $table->integer('downtime_hours')->default(0);
            $table->string('service_provider')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->timestamps();
            
            $table->index(['equipment_id', 'maintenance_date']);
        });

        Schema::create('cms_equipment_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('cms_equipment')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('cms_projects')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('cms_employees')->onDelete('set null');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('hours_used', 8, 2)->default(0);
            $table->decimal('fuel_cost', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['equipment_id', 'start_date']);
            $table->index('project_id');
            $table->index('job_id');
        });

        Schema::create('cms_equipment_rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('cms_equipment')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('cms_projects')->onDelete('cascade');
            $table->string('rental_company');
            $table->string('rental_agreement_number')->nullable();
            $table->date('rental_start_date');
            $table->date('rental_end_date');
            $table->decimal('daily_rate', 10, 2);
            $table->decimal('total_cost', 15, 2);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->enum('status', ['active', 'returned', 'extended'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['equipment_id', 'status']);
        });

        // ============================================
        // MODULE 4: LABOUR & CREW MANAGEMENT
        // ============================================
        
        Schema::create('cms_crews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('crew_name');
            $table->foreignId('foreman_id')->nullable()->constrained('cms_employees')->onDelete('set null');
            $table->string('specialization')->nullable(); // general, electrical, plumbing, etc.
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
        });

        Schema::create('cms_crew_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained('cms_crews')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('cms_employees')->onDelete('cascade');
            $table->string('role'); // foreman, skilled, semi_skilled, labourer
            $table->date('joined_date');
            $table->date('left_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->unique(['crew_id', 'employee_id']);
            $table->index('crew_id');
        });

        Schema::create('cms_labour_timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('cms_employees')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('cms_projects')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->onDelete('cascade');
            $table->foreignId('crew_id')->nullable()->constrained('cms_crews')->onDelete('set null');
            $table->date('work_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('regular_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->string('work_type')->nullable(); // installation, fabrication, finishing, etc.
            $table->text('work_description')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'paid'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('cms_employees')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'work_date']);
            $table->index('project_id');
            $table->index('job_id');
            $table->index('status');
        });

        Schema::create('cms_skill_matrix', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('cms_employees')->onDelete('cascade');
            $table->string('skill_name');
            $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('beginner');
            $table->date('acquired_date')->nullable();
            $table->date('last_used_date')->nullable();
            $table->string('certification_number')->nullable();
            $table->date('certification_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('employee_id');
            $table->index('skill_name');
        });

        // ============================================
        // MODULE 5: BILL OF QUANTITIES (BOQ)
        // ============================================
        
        Schema::create('cms_boq_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('project_type'); // residential, commercial, industrial
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
        });

        Schema::create('cms_boq_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('company_id');
        });

        Schema::create('cms_boqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('cms_projects')->onDelete('cascade');
            $table->foreignId('quotation_id')->nullable()->constrained('cms_quotations')->onDelete('set null');
            $table->foreignId('template_id')->nullable()->constrained('cms_boq_templates')->onDelete('set null');
            $table->string('boq_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('version')->default(1);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('contingency_percentage', 5, 2)->default(0);
            $table->decimal('contingency_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'submitted', 'approved', 'active', 'completed'])->default('draft');
            $table->date('prepared_date')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('cms_employees')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index('project_id');
        });

        Schema::create('cms_boq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boq_id')->constrained('cms_boqs')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('cms_boq_categories')->onDelete('set null');
            $table->string('item_code');
            $table->string('description');
            $table->string('unit'); // m2, m3, kg, pcs, etc.
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_rate', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->decimal('actual_quantity', 15, 3)->nullable();
            $table->decimal('actual_amount', 15, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('boq_id');
            $table->index('category_id');
        });

        Schema::create('cms_boq_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boq_id')->constrained('cms_boqs')->onDelete('cascade');
            $table->string('variation_number');
            $table->text('description');
            $table->enum('type', ['addition', 'omission', 'substitution'])->default('addition');
            $table->decimal('amount', 15, 2);
            $table->date('date_raised');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('cms_employees')->onDelete('set null');
            $table->date('approved_date')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
            
            $table->index(['boq_id', 'status']);
        });

        // ============================================
        // MODULE 6: PROGRESS BILLING & RETENTION
        // ============================================
        
        Schema::create('cms_billing_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cms_projects')->onDelete('cascade');
            $table->string('stage_name');
            $table->text('description')->nullable();
            $table->decimal('percentage', 5, 2); // percentage of total project value
            $table->decimal('amount', 15, 2);
            $table->integer('order')->default(0);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'billed'])->default('pending');
            $table->date('target_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
        });

        Schema::create('cms_progress_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cms_projects')->onDelete('cascade');
            $table->foreignId('billing_stage_id')->nullable()->constrained('cms_billing_stages')->onDelete('set null');
            $table->string('certificate_number')->unique();
            $table->date('certificate_date');
            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('work_completed_value', 15, 2);
            $table->decimal('materials_on_site_value', 15, 2)->default(0);
            $table->decimal('previous_certificates_total', 15, 2)->default(0);
            $table->decimal('current_certificate_value', 15, 2);
            $table->decimal('retention_percentage', 5, 2)->default(10);
            $table->decimal('retention_amount', 15, 2);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('net_payment_due', 15, 2);
            $table->enum('status', ['draft', 'submitted', 'approved', 'paid'])->default('draft');
            $table->foreignId('prepared_by')->constrained('cms_employees')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('cms_employees')->onDelete('set null');
            $table->date('approved_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
            $table->index('certificate_number');
        });

        Schema::create('cms_retention_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cms_projects')->onDelete('cascade');
            $table->foreignId('progress_certificate_id')->constrained('cms_progress_certificates')->onDelete('cascade');
            $table->decimal('retention_amount', 15, 2);
            $table->decimal('released_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->date('release_due_date')->nullable();
            $table->date('actual_release_date')->nullable();
            $table->enum('status', ['held', 'partially_released', 'fully_released'])->default('held');
            $table->text('release_notes')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
        });

        Schema::create('cms_payment_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cms_projects')->onDelete('cascade');
            $table->foreignId('progress_certificate_id')->constrained('cms_progress_certificates')->onDelete('cascade');
            $table->string('application_number')->unique();
            $table->date('application_date');
            $table->decimal('amount_applied', 15, 2);
            $table->decimal('amount_approved', 15, 2)->nullable();
            $table->enum('status', ['submitted', 'under_review', 'approved', 'rejected', 'paid'])->default('submitted');
            $table->text('supporting_documents')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('cms_employees')->onDelete('set null');
            $table->date('review_date')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
        });

        // Link projects to jobs
        Schema::table('cms_jobs', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('company_id')->constrained('cms_projects')->onDelete('set null');
            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order to respect foreign keys
        Schema::table('cms_jobs', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::dropIfExists('cms_payment_applications');
        Schema::dropIfExists('cms_retention_tracking');
        Schema::dropIfExists('cms_progress_certificates');
        Schema::dropIfExists('cms_billing_stages');
        
        Schema::dropIfExists('cms_boq_variations');
        Schema::dropIfExists('cms_boq_items');
        Schema::dropIfExists('cms_boqs');
        Schema::dropIfExists('cms_boq_categories');
        Schema::dropIfExists('cms_boq_templates');
        
        Schema::dropIfExists('cms_skill_matrix');
        Schema::dropIfExists('cms_labour_timesheets');
        Schema::dropIfExists('cms_crew_members');
        Schema::dropIfExists('cms_crews');
        
        Schema::dropIfExists('cms_equipment_rentals');
        Schema::dropIfExists('cms_equipment_usage');
        Schema::dropIfExists('cms_equipment_maintenance');
        Schema::dropIfExists('cms_equipment');
        
        Schema::dropIfExists('cms_subcontractor_ratings');
        Schema::dropIfExists('cms_subcontractor_payments');
        Schema::dropIfExists('cms_subcontractor_assignments');
        Schema::dropIfExists('cms_subcontractors');
        
        Schema::dropIfExists('cms_project_documents');
        Schema::dropIfExists('cms_site_diary_entries');
        Schema::dropIfExists('cms_project_milestones');
        Schema::dropIfExists('cms_projects');
    }
};
