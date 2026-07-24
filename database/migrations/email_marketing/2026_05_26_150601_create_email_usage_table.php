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
        Schema::create('email_usage', function (Blueprint $table) {
            $table->id();
            $table->string('provider'); // resend, brevo, ses
            $table->string('type'); // transactional, marketing, bulk
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->string('status'); // sent, failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at');
            $table->date('date')->index(); // For daily tracking
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->index(['provider', 'date']);
            $table->index(['type', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_usage');
    }
};
