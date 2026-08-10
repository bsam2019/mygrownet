<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Education Curricula Table
        if (!Schema::hasTable('education_curricula')) {
            Schema::create('education_curricula', function (Blueprint $table) {
                $table->id();
                $table->integer('level')->default(1);
                $table->string('module_title');
                $table->string('lesson_title');
                $table->text('description')->nullable();
                $table->enum('content_type', ['video', 'audio', 'text', 'pdf', 'workshop', 'practical'])->default('video');
                $table->string('video_url')->nullable();
                $table->string('audio_url')->nullable();
                $table->string('pdf_url')->nullable();
                $table->integer('duration_minutes')->default(15);
                $table->boolean('is_required')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['level', 'is_required']);
            });
        }

        // Assessment Attempts Table (Supporting Oral, Written, Practical & Voice-Note Recordings)
        if (!Schema::hasTable('assessment_attempts')) {
            Schema::create('assessment_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->integer('level')->default(1);
                $table->enum('assessment_method', ['quiz', 'written', 'oral', 'practical', 'facilitator_observation', 'voice_note'])->default('quiz');
                $table->string('voice_note_url')->nullable();
                $table->integer('score')->default(0);
                $table->boolean('passed')->default(false);
                $table->foreignId('assessor_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('assessor_notes')->nullable();
                $table->timestamp('evaluated_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'level', 'passed']);
            });
        }

        // Practical Task Submissions Table
        if (!Schema::hasTable('practical_task_submissions')) {
            Schema::create('practical_task_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->integer('level')->default(1);
                $table->string('task_title');
                $table->text('submission_text')->nullable();
                $table->string('attachment_url')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'resubmit_requested'])->default('pending');
                $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('evaluation_feedback')->nullable();
                $table->timestamp('evaluated_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'level', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('practical_task_submissions');
        Schema::dropIfExists('assessment_attempts');
        Schema::dropIfExists('education_curricula');
    }
};
