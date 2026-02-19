<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Training programs/courses
        if (!Schema::hasTable('cms_training_programs')) {
            Schema::create('cms_training_programs', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                        $table->string('title');
                        $table->text('description')->nullable();
                        $table->enum('type', ['internal', 'external', 'online', 'workshop', 'certification', 'mentorship'])->default('internal');
                        $table->enum('category', ['technical', 'soft_skills', 'leadership', 'compliance', 'safety', 'product', 'sales', 'other'])->default('technical');
                        $table->enum('level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('beginner');
                        $table->integer('duration_hours')->nullable();
                        $table->decimal('cost', 10, 2)->default(0);
                        $table->string('provider')->nullable();
                        $table->string('location')->nullable();
                        $table->integer('max_participants')->nullable();
                        $table->text('prerequisites')->nullable();
                        $table->text('learning_objectives')->nullable();
                        $table->json('materials')->nullable();
                        $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
                        $table->foreignId('created_by')->nullable()->constrained('cms_users')->nullOnDelete();
                        $table->timestamps();
                        $table->softDeletes();
                        
                        $table->index(['company_id', 'status']);
                        $table->index(['type', 'category']);
                    });
        }

        // Training sessions/schedules
        if (!Schema::hasTable('cms_training_sessions')) {
            Schema::create('cms_training_sessions', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('program_id')->constrained('cms_training_programs')->cascadeOnDelete();
                        $table->string('session_name')->nullable();
                        $table->date('start_date');
                        $table->date('end_date')->nullable();
                        $table->time('start_time')->nullable();
                        $table->time('end_time')->nullable();
                        $table->string('venue')->nullable();
                        $table->foreignId('trainer_id')->nullable()->constrained('cms_users')->nullOnDelete();
                        $table->string('trainer_name')->nullable();
                        $table->integer('available_seats')->nullable();
                        $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
                        $table->text('notes')->nullable();
                        $table->timestamps();
                        
                        $table->index(['program_id', 'status']);
                        $table->index('start_date');
                    });
        }

        // Training enrollments
        if (!Schema::hasTable('cms_training_enrollments')) {
            Schema::create('cms_training_enrollments', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('session_id')->constrained('cms_training_sessions')->cascadeOnDelete();
                        $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
                        $table->date('enrolled_date');
                        $table->enum('status', ['enrolled', 'in_progress', 'completed', 'failed', 'withdrawn'])->default('enrolled');
                        $table->integer('attendance_percentage')->default(0);
                        $table->decimal('assessment_score', 5, 2)->nullable();
                        $table->enum('pass_status', ['passed', 'failed', 'pending'])->nullable();
                        $table->date('completion_date')->nullable();
                        $table->text('feedback')->nullable();
                        $table->boolean('certificate_issued')->default(false);
                        $table->string('certificate_number')->nullable();
                        $table->foreignId('enrolled_by')->nullable()->constrained('cms_users')->nullOnDelete();
                        $table->timestamps();
                        
                        $table->unique(['session_id', 'worker_id']);
                        $table->index(['worker_id', 'status']);
                    });
        }

        // Training attendance tracking
        if (!Schema::hasTable('cms_training_attendance')) {
            Schema::create('cms_training_attendance', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('enrollment_id')->constrained('cms_training_enrollments')->cascadeOnDelete();
                        $table->date('date');
                        $table->time('check_in_time')->nullable();
                        $table->time('check_out_time')->nullable();
                        $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
                        $table->text('notes')->nullable();
                        $table->foreignId('marked_by')->nullable()->constrained('cms_users')->nullOnDelete();
                        $table->timestamps();
                        
                        $table->unique(['enrollment_id', 'date']);
                        $table->index('date');
                    });
        }

        // Skills/competencies catalog
        if (!Schema::hasTable('cms_skills')) {
            Schema::create('cms_skills', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                        $table->string('name');
                        $table->text('description')->nullable();
                        $table->enum('category', ['technical', 'soft_skills', 'leadership', 'language', 'certification', 'other'])->default('technical');
                        $table->enum('level_required', ['basic', 'intermediate', 'advanced', 'expert'])->default('basic');
                        $table->boolean('is_core')->default(false);
                        $table->timestamps();
                        
                        $table->index(['company_id', 'category']);
                    });
        }

        // Worker skills/competencies
        if (!Schema::hasTable('cms_worker_skills')) {
            Schema::create('cms_worker_skills', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
                        $table->foreignId('skill_id')->constrained('cms_skills')->cascadeOnDelete();
                        $table->enum('proficiency_level', ['basic', 'intermediate', 'advanced', 'expert'])->default('basic');
                        $table->date('acquired_date')->nullable();
                        $table->date('last_assessed_date')->nullable();
                        $table->foreignId('verified_by')->nullable()->constrained('cms_users')->nullOnDelete();
                        $table->text('notes')->nullable();
                        $table->timestamps();
                        
                        $table->unique(['worker_id', 'skill_id']);
                        $table->index('proficiency_level');
                    });
        }

        // Certifications
        if (!Schema::hasTable('cms_certifications')) {
            Schema::create('cms_certifications', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('worker_id')->constrained('cms_workers')->cascadeOnDelete();
                        $table->string('certification_name');
                        $table->string('issuing_organization');
                        $table->string('certificate_number')->nullable();
                        $table->date('issue_date');
                        $table->date('expiry_date')->nullable();
                        $table->boolean('requires_renewal')->default(false);
                        $table->string('document_path')->nullable();
                        $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
                        $table->text('notes')->nullable();
                        $table->timestamps();
                        
                        $table->index(['worker_id', 'status']);
                        $table->index('expiry_date');
                    });
        }

        // Training budget tracking
        if (!Schema::hasTable('cms_training_budgets')) {
            Schema::create('cms_training_budgets', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                        $table->foreignId('department_id')->nullable()->constrained('cms_departments')->nullOnDelete();
                        $table->integer('year');
                        $table->decimal('allocated_amount', 12, 2);
                        $table->decimal('spent_amount', 12, 2)->default(0);
                        $table->decimal('committed_amount', 12, 2)->default(0);
                        $table->text('notes')->nullable();
                        $table->timestamps();
                        
                        $table->unique(['company_id', 'department_id', 'year']);
                        $table->index('year');
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_training_budgets');
        Schema::dropIfExists('cms_certifications');
        Schema::dropIfExists('cms_worker_skills');
        Schema::dropIfExists('cms_skills');
        Schema::dropIfExists('cms_training_attendance');
        Schema::dropIfExists('cms_training_enrollments');
        Schema::dropIfExists('cms_training_sessions');
        Schema::dropIfExists('cms_training_programs');
    }
};
