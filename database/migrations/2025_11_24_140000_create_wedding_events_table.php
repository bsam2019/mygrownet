<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('partner_name');
            $table->string('partner_email')->nullable();
            $table->string('partner_phone')->nullable();
            $table->date('wedding_date');
            $table->string('venue_name')->nullable();
            $table->string('venue_location')->nullable();
            $table->decimal('budget', 15, 2)->default(0);
            $table->integer('guest_count')->default(0);
            $table->enum('status', ['planning', 'confirmed', 'completed', 'cancelled'])->default('planning');
            $table->text('notes')->nullable();
            $table->json('preferences')->nullable(); // Color scheme, style, etc.
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('wedding_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_events');
    }
};