<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recognition_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recognition_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->datetime('registration_date');
            $table->enum('attendance_status', ['registered', 'attended', 'no_show', 'cancelled'])->default('registered');
            $table->text('special_recognition')->nullable();
            $table->string('award_received')->nullable();
            $table->timestamps();

            $table->unique(['recognition_event_id', 'user_id']);
            $table->index(['attendance_status']);
            $table->index(['registration_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recognition_event_attendees');
    }
};