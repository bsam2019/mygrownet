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
        Schema::create('investor_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20);
            $table->string('investment_range', 50);
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'contacted', 'meeting_scheduled', 'closed'])->default('new');
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('status');
            $table->index('investment_range');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_inquiries');
    }
};
