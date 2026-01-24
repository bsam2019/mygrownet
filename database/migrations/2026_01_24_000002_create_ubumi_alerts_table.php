<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubumi_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('family_id');
            $table->uuid('person_id');
            $table->uuid('check_in_id')->nullable();
            $table->enum('alert_type', ['unwell', 'need_assistance', 'missed_checkin'])->default('unwell');
            $table->text('message');
            $table->enum('status', ['pending', 'acknowledged', 'resolved'])->default('pending');
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('ubumi_families')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('ubumi_persons')->onDelete('cascade');
            $table->foreign('check_in_id')->references('id')->on('ubumi_check_ins')->onDelete('set null');
            $table->foreign('acknowledged_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['family_id', 'status']);
            $table->index('alert_type');
        });

        Schema::create('ubumi_check_in_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('person_id')->unique();
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly'])->default('weekly');
            $table->integer('reminder_hours')->default(24); // Hours before sending reminder
            $table->integer('missed_threshold_hours')->default(72); // Hours before alert
            $table->boolean('reminders_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('email_enabled')->default(true);
            $table->timestamps();

            $table->foreign('person_id')->references('id')->on('ubumi_persons')->onDelete('cascade');
        });

        Schema::create('ubumi_caregivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('person_id'); // Person being cared for
            $table->unsignedBigInteger('caregiver_user_id'); // User who is the caregiver
            $table->enum('relationship', ['family_member', 'friend', 'professional', 'other'])->default('family_member');
            $table->boolean('receives_alerts')->default(true);
            $table->timestamps();

            $table->foreign('person_id')->references('id')->on('ubumi_persons')->onDelete('cascade');
            $table->foreign('caregiver_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['person_id', 'caregiver_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubumi_caregivers');
        Schema::dropIfExists('ubumi_check_in_settings');
        Schema::dropIfExists('ubumi_alerts');
    }
};
