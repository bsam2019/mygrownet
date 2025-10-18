<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create packages table (only if it doesn't exist)
        if (!Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2);
                $table->enum('billing_cycle', ['monthly', 'quarterly', 'annual'])->default('monthly');
                $table->integer('duration_months')->default(1);
                $table->json('features')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Create package_subscriptions table (renamed to avoid conflict with existing subscriptions table)
        if (!Schema::hasTable('package_subscriptions')) {
            Schema::create('package_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('package_id')->constrained()->onDelete('restrict');
                $table->decimal('amount', 10, 2);
                $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])->default('pending');
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->timestamp('renewal_date')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->string('cancellation_reason')->nullable();
                $table->boolean('auto_renew')->default(true);
                $table->boolean('is_trial')->default(false);
                $table->integer('trial_days')->default(0);
                $table->timestamps();
                
                $table->index(['user_id', 'status']);
                $table->index('end_date');
            });
        }

        // Add package_subscription_id to referral_commissions table
        Schema::table('referral_commissions', function (Blueprint $table) {
            if (!Schema::hasColumn('referral_commissions', 'package_subscription_id')) {
                $table->foreignId('package_subscription_id')->nullable()->after('investment_id')->constrained('package_subscriptions')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('referral_commissions', function (Blueprint $table) {
            if (Schema::hasColumn('referral_commissions', 'package_subscription_id')) {
                $table->dropForeign(['package_subscription_id']);
                $table->dropColumn('package_subscription_id');
            }
        });
        
        Schema::dropIfExists('package_subscriptions');
        Schema::dropIfExists('packages');
    }
};
