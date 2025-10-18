<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recognition_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('event_type', ['annual_gala', 'quarterly_ceremony', 'exclusive_retreat', 'virtual_celebration']);
            $table->datetime('event_date');
            $table->string('location')->nullable();
            $table->boolean('is_virtual')->default(false);
            $table->integer('max_attendees')->nullable();
            $table->datetime('registration_deadline')->nullable();
            $table->json('eligibility_criteria')->nullable();
            $table->json('awards')->nullable();
            $table->json('agenda')->nullable();
            $table->enum('status', ['planning', 'registration_open', 'registration_closed', 'completed', 'cancelled'])->default('planning');
            $table->decimal('budget', 12, 2)->default(0);
            $table->decimal('spent_amount', 12, 2)->default(0);
            $table->string('celebration_theme')->nullable();
            $table->string('dress_code')->nullable();
            $table->json('special_guests')->nullable();
            $table->timestamps();

            $table->index(['event_date']);
            $table->index(['event_type']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recognition_events');
    }
};