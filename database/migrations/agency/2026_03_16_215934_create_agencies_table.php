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
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('agency_name');
            $table->string('slug')->unique();
            $table->foreignId('owner_user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans');
            
            // Contact Information
            $table->string('business_email');
            $table->string('phone')->nullable();
            $table->string('country')->default('ZM');
            $table->string('currency')->default('ZMW');
            $table->string('timezone')->default('Africa/Lusaka');
            $table->string('locale')->default('en');
            
            // Status & Lifecycle
            $table->enum('status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->boolean('onboarding_completed')->default(false);
            
            // Quotas & Limits
            $table->unsignedBigInteger('storage_limit_mb')->default(20480); // 20GB
            $table->unsignedBigInteger('storage_used_mb')->default(0);
            $table->unsignedInteger('site_limit')->default(10);
            $table->unsignedInteger('sites_used')->default(0);
            $table->unsignedInteger('team_member_limit')->default(3);
            
            // Features
            $table->boolean('allow_white_label')->default(false);
            
            // Growth & Referral
            $table->string('referral_code')->unique()->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('owner_user_id');
            $table->index(['status', 'trial_ends_at']);
            $table->index('referral_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
