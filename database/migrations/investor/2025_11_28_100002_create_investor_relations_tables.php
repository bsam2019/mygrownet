<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quarterly Reports
        if (!Schema::hasTable('quarterly_reports')) {
        Schema::create('quarterly_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->enum('quarter', ['Q1', 'Q2', 'Q3', 'Q4']);
            $table->string('title');
            $table->text('executive_summary')->nullable();
            $table->string('pdf_path')->nullable();
            $table->date('report_date');
            $table->date('published_at')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            
            // Key metrics snapshot
            $table->decimal('revenue', 15, 2)->nullable();
            $table->decimal('profit', 15, 2)->nullable();
            $table->decimal('company_valuation', 15, 2)->nullable();
            $table->integer('total_members')->nullable();
            $table->json('highlights')->nullable(); // Key achievements
            
            $table->timestamps();
            
            $table->unique(['year', 'quarter']);
            $table->index('published_at');
        });
        }

        // Meeting Notices (AGM, EGM, Board Meetings)
        if (!Schema::hasTable('investor_meetings')) {
        Schema::create('investor_meetings', function (Blueprint $table) {
            $table->id();
            $table->enum('meeting_type', ['agm', 'egm', 'board', 'investor_update']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('agenda')->nullable();
            $table->datetime('meeting_datetime');
            $table->string('location')->nullable();
            $table->string('virtual_link')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            
            // Documents
            $table->string('notice_pdf')->nullable();
            $table->string('minutes_pdf')->nullable();
            $table->string('presentation_pdf')->nullable();
            
            $table->date('notice_sent_at')->nullable();
            $table->timestamps();
            
            $table->index('meeting_datetime');
            $table->index('status');
        });
        }

        // Meeting Attendance
        if (!Schema::hasTable('investor_meeting_attendance')) {
        Schema::create('investor_meeting_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_meeting_id')->constrained('investor_meetings')->onDelete('cascade');
            $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
            $table->enum('rsvp_status', ['pending', 'attending', 'not_attending', 'maybe'])->default('pending');
            $table->enum('attendance_type', ['in_person', 'virtual'])->nullable();
            $table->boolean('attended')->default(false);
            $table->timestamp('rsvp_at')->nullable();
            $table->timestamps();
            
            $table->unique(['investor_meeting_id', 'investor_account_id'], 'inv_meeting_attend_unique');
        });
        }

        // Board Updates / CEO Letters
        if (!Schema::hasTable('board_updates')) {
        Schema::create('board_updates', function (Blueprint $table) {
            $table->id();
            $table->enum('update_type', ['board_minutes', 'ceo_letter', 'strategic_update', 'milestone']);
            $table->string('title');
            $table->text('content');
            $table->string('author_name')->nullable();
            $table->string('author_title')->nullable();
            $table->date('update_date');
            $table->date('published_at')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('featured_image')->nullable();
            $table->json('attachments')->nullable(); // Array of file paths
            $table->timestamps();
            
            $table->index('published_at');
            $table->index('update_type');
        });
        }

        // Update Read Status
        if (!Schema::hasTable('board_update_reads')) {
        Schema::create('board_update_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_update_id')->constrained('board_updates')->onDelete('cascade');
            $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
            $table->timestamp('read_at');
            
            $table->unique(['board_update_id', 'investor_account_id']);
        });
        }

        // Company Milestones
        if (!Schema::hasTable('company_milestones')) {
        Schema::create('company_milestones', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->date('milestone_date');
            $table->enum('category', ['revenue', 'product', 'team', 'funding', 'partnership', 'other']);
            $table->string('icon')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            
            $table->index('milestone_date');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_milestones');
        Schema::dropIfExists('board_update_reads');
        Schema::dropIfExists('board_updates');
        Schema::dropIfExists('investor_meeting_attendance');
        Schema::dropIfExists('investor_meetings');
        Schema::dropIfExists('quarterly_reports');
    }
};
