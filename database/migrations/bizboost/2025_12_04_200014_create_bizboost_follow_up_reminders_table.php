<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_follow_up_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('bizboost_customers')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->time('due_time')->default('09:00');
            $table->enum('reminder_type', ['follow_up', 'payment', 'delivery', 'appointment', 'custom'])->default('follow_up');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            $table->integer('snoozed_count')->default(0);
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();

            $table->index(['business_id', 'status', 'due_date']);
            $table->index(['business_id', 'customer_id']);
        });

        // Add WhatsApp broadcasts table if not exists
        if (!Schema::hasTable('bizboost_whatsapp_broadcasts')) {
            Schema::create('bizboost_whatsapp_broadcasts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('bizboost_businesses')->cascadeOnDelete();
                $table->string('name');
                $table->text('message');
                $table->enum('recipient_type', ['all', 'selected', 'tagged'])->default('all');
                $table->json('recipient_filter')->nullable();
                $table->integer('recipient_count')->default(0);
                $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_follow_up_reminders');
        Schema::dropIfExists('bizboost_whatsapp_broadcasts');
    }
};
