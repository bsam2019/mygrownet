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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Channel preferences
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('push_enabled')->default(false);
            $table->boolean('in_app_enabled')->default(true);
            
            // Category preferences
            $table->boolean('notify_wallet')->default(true);
            $table->boolean('notify_commissions')->default(true);
            $table->boolean('notify_withdrawals')->default(true);
            $table->boolean('notify_subscriptions')->default(true);
            $table->boolean('notify_referrals')->default(true);
            $table->boolean('notify_workshops')->default(true);
            $table->boolean('notify_ventures')->default(true);
            $table->boolean('notify_bgf')->default(true);
            $table->boolean('notify_points')->default(true);
            $table->boolean('notify_security')->default(true);
            $table->boolean('notify_marketing')->default(false);
            
            // Frequency settings
            $table->enum('digest_frequency', ['instant', 'daily', 'weekly'])->default('instant');
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
