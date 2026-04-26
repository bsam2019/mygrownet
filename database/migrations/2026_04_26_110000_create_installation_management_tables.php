<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Installation Schedules
        Schema::create('cms_installation_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('cms_jobs')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('cms_projects')->cascadeOnDelete();
            $table->string('schedule_number')->unique();
            $table->date('scheduled_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->foreignId('team_leader_id')->nullable()->constrained('users');
            $table->text('site_address')->nullable();
            $table->string('site_contact_name')->nullable();
            $table->string('site_contact_phone')->nullable();
            $table->text('special_instructions')->nullable();
            $table->text('equipment_required')->nullable();
            $table->text('materials_required')->nullable();
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->decimal('actual_hours', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'scheduled_date']);
            $table->index(['job_id']);
            $table->index(['status']);
        });

        // Installation Team Members
        Schema::create('cms_installation_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installation_schedule_id')->constrained('cms_installation_schedules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('role', ['leader', 'technician', 'helper', 'driver'])->default('technician');
            $table->timestamps();
            
            $table->unique(['installation_schedule_id', 'user_id']);
        });

        // Site Visits
        Schema::create('cms_site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('installation_schedule_id')->nullable()->constrained('cms_installation_schedules')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('cms_jobs')->cascadeOnDelete();
            $table->string('visit_number')->unique();
            $table->date('visit_date');
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->enum('visit_type', ['installation', 'inspection', 'repair', 'maintenance', 'measurement'])->default('installation');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('visited_by')->constrained('users');
            $table->text('purpose')->nullable();
            $table->text('findings')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('issues_encountered')->nullable();
            $table->text('next_steps')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'visit_date']);
            $table->index(['job_id']);
        });

        // Installation Photos
        Schema::create('cms_installation_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained('cms_site_visits')->cascadeOnDelete();
            $table->enum('photo_type', ['before', 'during', 'after', 'issue', 'completion'])->default('during');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['site_visit_id', 'photo_type']);
        });

        // Installation Checklists
        Schema::create('cms_installation_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('checklist_name');
            $table->text('description')->nullable();
            $table->enum('checklist_type', ['pre_installation', 'installation', 'post_installation', 'quality_check'])->default('installation');
            $table->boolean('is_template')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['company_id', 'checklist_type']);
        });

        // Checklist Items
        Schema::create('cms_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('cms_installation_checklists')->cascadeOnDelete();
            $table->string('item_text');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['checklist_id']);
        });

        // Installation Checklist Responses
        Schema::create('cms_installation_checklist_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained('cms_site_visits')->cascadeOnDelete();
            $table->foreignId('checklist_id')->constrained('cms_installation_checklists');
            $table->foreignId('checklist_item_id')->constrained('cms_checklist_items');
            $table->enum('status', ['pending', 'passed', 'failed', 'na'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users');
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
            
            $table->index(['site_visit_id']);
            $table->index(['checklist_id']);
        });

        // Customer Sign-offs
        Schema::create('cms_customer_signoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained('cms_site_visits')->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('signature_data')->nullable(); // Base64 signature
            $table->timestamp('signed_at');
            $table->text('comments')->nullable();
            $table->integer('satisfaction_rating')->nullable(); // 1-5
            $table->text('feedback')->nullable();
            $table->timestamps();
            
            $table->index(['site_visit_id']);
        });

        // Defects/Snag List
        Schema::create('cms_defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('cms_jobs')->cascadeOnDelete();
            $table->foreignId('site_visit_id')->nullable()->constrained('cms_site_visits')->cascadeOnDelete();
            $table->string('defect_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['minor', 'moderate', 'major', 'critical'])->default('moderate');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed', 'deferred'])->default('open');
            $table->date('identified_date');
            $table->date('target_resolution_date')->nullable();
            $table->date('actual_resolution_date')->nullable();
            $table->foreignId('identified_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index(['job_id']);
        });

        // Defect Photos
        Schema::create('cms_defect_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('defect_id')->constrained('cms_defects')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->text('caption')->nullable();
            $table->timestamps();
            
            $table->index(['defect_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_defect_photos');
        Schema::dropIfExists('cms_defects');
        Schema::dropIfExists('cms_customer_signoffs');
        Schema::dropIfExists('cms_installation_checklist_responses');
        Schema::dropIfExists('cms_checklist_items');
        Schema::dropIfExists('cms_installation_checklists');
        Schema::dropIfExists('cms_installation_photos');
        Schema::dropIfExists('cms_site_visits');
        Schema::dropIfExists('cms_installation_team_members');
        Schema::dropIfExists('cms_installation_schedules');
    }
};
