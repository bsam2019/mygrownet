<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investor_email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('investor_account_id');
            $table->string('email_type'); // announcement, report, dividend, meeting, message
            $table->string('subject');
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of announcement/report/etc
            $table->string('reference_type')->nullable(); // announcement, financial_report, etc
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->foreign('investor_account_id')
                ->references('id')
                ->on('investor_accounts')
                ->onDelete('cascade');
                
            $table->index(['investor_account_id', 'email_type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_email_logs');
    }
};
