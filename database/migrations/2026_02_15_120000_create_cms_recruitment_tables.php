<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Job Postings
        if (!Schema::hasTable('cms_job_postings')) {
            Schema::create('cms_job_postings', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('job_title');
                        $table->foreignId('department_id')->nullable()->constrained('cms_departments')->onDelete('set null');
                        $table->text('job_description');
                        $table->text('requirements')->nullable();
                        $table->decimal('salary_range_min', 15, 2)->nullable();
                        $table->decimal('salary_range_max', 15, 2)->nullable();
                        $table->integer('positions_available')->default(1);
                        $table->date('application_deadline')->nullable();
                        $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
                        $table->foreignId('created_by')->nullable()->constrained('cms_users')->onDelete('set null');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'status']);
                    });
        }

        // Job Applications
        if (!Schema::hasTable('cms_job_applications')) {
            Schema::create('cms_job_applications', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('job_posting_id')->constrained('cms_job_postings')->onDelete('cascade');
                        $table->string('applicant_name');
                        $table->string('email');
                        $table->string('phone', 50);
                        $table->string('cv_path', 500);
                        $table->text('cover_letter')->nullable();
                        $table->enum('status', ['new', 'screening', 'interview', 'offer', 'rejected', 'hired'])->default('new');
                        $table->timestamps();
                        
                        $table->index('status');
                    });
        }

        // Interviews
        if (!Schema::hasTable('cms_interviews')) {
            Schema::create('cms_interviews', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('application_id')->constrained('cms_job_applications')->onDelete('cascade');
                        $table->enum('interview_type', ['phone', 'video', 'in_person', 'technical', 'final']);
                        $table->dateTime('scheduled_date');
                        $table->string('location')->nullable();
                        $table->string('meeting_link', 500)->nullable();
                        $table->json('interviewer_ids')->nullable();
                        $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
                        $table->text('notes')->nullable();
                        $table->timestamps();
                        
                        $table->index(['application_id', 'status']);
                    });
        }

        // Interview Evaluations
        if (!Schema::hasTable('cms_interview_evaluations')) {
            Schema::create('cms_interview_evaluations', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('interview_id')->constrained('cms_interviews')->onDelete('cascade');
                        $table->foreignId('evaluator_id')->constrained('cms_users')->onDelete('cascade');
                        $table->integer('technical_skills_rating')->nullable();
                        $table->integer('communication_rating')->nullable();
                        $table->integer('cultural_fit_rating')->nullable();
                        $table->integer('overall_rating')->nullable();
                        $table->text('comments')->nullable();
                        $table->enum('recommendation', ['strong_yes', 'yes', 'maybe', 'no', 'strong_no'])->nullable();
                        $table->timestamps();
                        
                        $table->index('interview_id');
                    });
        }

        // Offer Letters
        if (!Schema::hasTable('cms_offer_letters')) {
            Schema::create('cms_offer_letters', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('application_id')->constrained('cms_job_applications')->onDelete('cascade');
                        $table->string('job_title');
                        $table->decimal('salary', 15, 2);
                        $table->date('start_date');
                        $table->string('offer_letter_path', 500)->nullable();
                        $table->date('sent_date')->nullable();
                        $table->date('response_deadline')->nullable();
                        $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
                        $table->text('terms')->nullable();
                        $table->timestamps();
                        
                        $table->index(['application_id', 'status']);
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_offer_letters');
        Schema::dropIfExists('cms_interview_evaluations');
        Schema::dropIfExists('cms_interviews');
        Schema::dropIfExists('cms_job_applications');
        Schema::dropIfExists('cms_job_postings');
    }
};
