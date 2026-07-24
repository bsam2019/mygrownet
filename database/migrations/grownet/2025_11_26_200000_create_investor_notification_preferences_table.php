<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('investor_notification_preferences')) {
            return;
        }
        
        Schema::create('investor_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('investor_account_id');
            
            // Email notification preferences
            $table->boolean('email_announcements')->default(true);
            $table->boolean('email_financial_reports')->default(true);
            $table->boolean('email_dividends')->default(true);
            $table->boolean('email_meetings')->default(true);
            $table->boolean('email_messages')->default(true);
            $table->boolean('email_urgent_only')->default(false);
            
            // Digest preferences
            $table->enum('digest_frequency', ['immediate', 'daily', 'weekly', 'none'])->default('immediate');
            
            $table->timestamps();
            
            $table->foreign('investor_account_id')
                ->references('id')
                ->on('investor_accounts')
                ->onDelete('cascade');
                
            $table->unique('investor_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_notification_preferences');
    }
};
