<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wedding_rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_event_id')->constrained('wedding_events')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->boolean('attending')->default(false);
            $table->integer('guest_count')->default(0);
            $table->text('dietary_restrictions')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            // Indexes
            $table->index(['wedding_event_id', 'attending']);
            $table->index('email');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_rsvps');
    }
};