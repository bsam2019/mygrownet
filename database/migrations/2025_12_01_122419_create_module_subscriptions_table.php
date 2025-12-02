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
        Schema::create('module_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module_id', 50);
            $table->string('subscription_tier', 50);
            
            // Status
            $table->enum('status', ['active', 'trial', 'suspended', 'cancelled'])->default('active');
            
            // Dates
            $table->timestamp('started_at');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Billing
            $table->boolean('auto_renew')->default(true);
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('ZMW');
            
            // Limits (for SME apps)
            $table->integer('user_limit')->nullable();
            $table->integer('storage_limit_mb')->nullable();
            
            $table->timestamps();
            
            $table->foreign('module_id')->references('id')->on('modules');
            $table->unique(['user_id', 'module_id']);
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_subscriptions');
    }
};
