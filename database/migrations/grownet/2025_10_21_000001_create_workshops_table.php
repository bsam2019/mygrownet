<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('category', ['financial_literacy', 'business_skills', 'leadership', 'marketing', 'technology', 'personal_development']);
            $table->enum('delivery_format', ['online', 'physical', 'hybrid']);
            $table->decimal('price', 10, 2);
            $table->integer('max_participants')->nullable();
            $table->integer('lp_reward')->default(0);
            $table->integer('bp_reward')->default(0);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->text('requirements')->nullable();
            $table->text('learning_outcomes')->nullable();
            $table->string('instructor_name')->nullable();
            $table->text('instructor_bio')->nullable();
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('workshop_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained('member_payments');
            $table->enum('status', ['pending_payment', 'registered', 'attended', 'completed', 'cancelled'])->default('pending_payment');
            $table->text('registration_notes')->nullable();
            $table->timestamp('attended_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('certificate_issued')->default(false);
            $table->timestamp('certificate_issued_at')->nullable();
            $table->boolean('points_awarded')->default(false);
            $table->timestamp('points_awarded_at')->nullable();
            $table->timestamps();
            
            $table->unique(['workshop_id', 'user_id']);
        });

        Schema::create('workshop_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('meeting_link')->nullable();
            $table->integer('duration_minutes');
            $table->timestamps();
        });

        Schema::create('workshop_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('checked_in_at');
            $table->string('checked_in_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['workshop_session_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_attendance');
        Schema::dropIfExists('workshop_sessions');
        Schema::dropIfExists('workshop_registrations');
        Schema::dropIfExists('workshops');
    }
};
