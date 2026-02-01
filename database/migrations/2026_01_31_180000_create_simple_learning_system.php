<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Learning Modules (text-based for now, video later)
        Schema::create('learning_modules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content'); // Text content (markdown supported)
            $table->string('content_type')->default('text'); // text, video, pdf
            $table->string('video_url')->nullable(); // For future video integration
            $table->integer('estimated_minutes')->default(10); // Reading/watching time
            $table->string('category')->nullable(); // business, marketing, finance, etc.
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->boolean('is_required')->default(false); // Required for qualification
            $table->timestamps();
        });

        // User Module Completions
        Schema::create('learning_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('learning_module_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at');
            $table->integer('time_spent_seconds')->nullable(); // Track engagement
            $table->timestamps();
            
            $table->unique(['user_id', 'learning_module_id']);
            $table->index(['user_id', 'completed_at']);
        });

        // Live Events (webinars, workshops, meetings)
        Schema::create('live_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('event_type')->default('webinar'); // webinar, workshop, meeting, training
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->string('meeting_link')->nullable(); // Zoom, Google Meet, etc.
            $table->string('meeting_id')->nullable();
            $table->string('meeting_password')->nullable();
            $table->integer('max_attendees')->nullable();
            $table->string('host_name')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('requires_registration')->default(true);
            $table->timestamps();
        });

        // Event Registrations
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('live_event_id')->constrained()->onDelete('cascade');
            $table->timestamp('registered_at');
            $table->timestamps();
            
            $table->unique(['user_id', 'live_event_id']);
        });

        // Event Attendance (check-in system)
        Schema::create('event_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('live_event_id')->constrained()->onDelete('cascade');
            $table->timestamp('checked_in_at');
            $table->timestamp('checked_out_at')->nullable();
            $table->integer('attendance_minutes')->nullable(); // How long they stayed
            $table->string('check_in_method')->default('manual'); // manual, qr_code, auto
            $table->timestamps();
            
            $table->unique(['user_id', 'live_event_id']);
            $table->index(['user_id', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendances');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('live_events');
        Schema::dropIfExists('learning_completions');
        Schema::dropIfExists('learning_modules');
    }
};
