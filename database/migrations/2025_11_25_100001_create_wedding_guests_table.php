<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_event_id')->constrained('wedding_events')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->integer('allowed_guests')->default(1); // How many guests they can bring
            $table->string('group_name')->nullable(); // e.g., "Family", "Friends", "Work"
            $table->text('notes')->nullable();
            $table->boolean('invitation_sent')->default(false);
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['wedding_event_id', 'first_name', 'last_name']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_guests');
    }
};
