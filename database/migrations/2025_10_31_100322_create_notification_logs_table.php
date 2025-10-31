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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('notification_id')->nullable();
            
            // Delivery details
            $table->enum('channel', ['email', 'sms', 'push', 'in_app']);
            $table->string('type', 50);
            $table->string('recipient'); // email address or phone number
            
            // Content
            $table->string('subject')->nullable();
            $table->text('content');
            
            // Status tracking
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'bounced'])->default('pending');
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            
            // Delivery tracking
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            
            // Provider details
            $table->string('provider', 50)->nullable();
            $table->string('provider_message_id')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['user_id', 'channel']);
            $table->index('status');
            $table->index('created_at');
            
            // Foreign key will be added manually if needed
            // UUID foreign keys can be tricky in some MySQL versions
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
