<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investor_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('investor_account_id');
            $table->string('activity_type'); // login, view_document, view_report, view_announcement, send_message
            $table->string('description')->nullable();
            $table->json('metadata')->nullable(); // Additional context data
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->foreign('investor_account_id')
                ->references('id')
                ->on('investor_accounts')
                ->onDelete('cascade');
                
            $table->index(['investor_account_id', 'activity_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_activity_logs');
    }
};
