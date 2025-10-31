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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            
            // Template identification
            $table->string('type', 50)->unique(); // 'wallet.topup', 'commission.earned'
            $table->string('category', 50);
            $table->string('name');
            $table->text('description')->nullable();
            
            // Channel-specific templates
            $table->string('email_subject')->nullable();
            $table->text('email_body')->nullable();
            $table->string('sms_body', 500)->nullable();
            $table->string('push_title', 100)->nullable();
            $table->string('push_body')->nullable();
            $table->string('in_app_title')->nullable();
            $table->text('in_app_body')->nullable();
            
            // Template variables (JSON array of available variables)
            $table->json('variables')->nullable();
            
            // Settings
            $table->boolean('is_active')->default(true);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            $table->timestamps();
            
            $table->index('type');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
