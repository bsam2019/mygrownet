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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Notification content
            $table->string('type', 50); // 'wallet.topup', 'commission.earned', etc.
            $table->string('category', 50); // 'wallet', 'referral', 'security', etc.
            $table->string('title');
            $table->text('message');
            $table->string('action_url', 500)->nullable();
            $table->string('action_text', 100)->nullable();
            
            // Metadata
            $table->json('data')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Status
            $table->timestamp('read_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            
            // Indexes
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'category']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
