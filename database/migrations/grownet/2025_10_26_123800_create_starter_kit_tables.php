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
        // Starter Kit Purchases
        Schema::create('starter_kit_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2)->default(500.00);
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('invoice_number', 50)->unique();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
            $table->index('purchased_at');
        });

        // Content Access Tracking
        Schema::create('starter_kit_content_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('content_type', 50); // 'ebook', 'video', 'course', 'webinar', 'template'
            $table->string('content_id', 100);
            $table->string('content_name')->nullable();
            $table->timestamp('first_accessed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->enum('completion_status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamp('completion_date')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('content_type');
            $table->index('completion_status');
            $table->unique(['user_id', 'content_type', 'content_id'], 'sk_content_unique');
        });

        // Progressive Unlocking Schedule
        Schema::create('starter_kit_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('content_item', 100);
            $table->string('content_category', 50); // 'course', 'video', 'tool', 'webinar'
            $table->date('unlock_date');
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->boolean('is_unlocked')->default(false);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('unlock_date');
            $table->index('is_unlocked');
        });

        // Member Achievements and Badges
        Schema::create('member_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('achievement_type', 50); // 'starter_graduate', 'first_referral', 'first_earner', etc.
            $table->string('achievement_name');
            $table->text('description')->nullable();
            $table->string('badge_icon')->nullable();
            $table->string('badge_color', 20)->nullable();
            $table->timestamp('earned_at');
            $table->string('certificate_url')->nullable();
            $table->boolean('is_displayed')->default(true);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('achievement_type');
            $table->index('earned_at');
            $table->unique(['user_id', 'achievement_type'], 'achievement_unique');
        });

        // Add shop credit tracking to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'starter_kit_shop_credit')) {
                $table->decimal('starter_kit_shop_credit', 10, 2)->default(0.00)->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'starter_kit_credit_expiry')) {
                $table->date('starter_kit_credit_expiry')->nullable()->after('starter_kit_shop_credit');
            }
        });

        // Add starter kit fields to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'has_starter_kit')) {
                $table->boolean('has_starter_kit')->default(false)->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'starter_kit_purchased_at')) {
                $table->timestamp('starter_kit_purchased_at')->nullable()->after('has_starter_kit');
            }
            if (!Schema::hasColumn('users', 'starter_kit_terms_accepted')) {
                $table->boolean('starter_kit_terms_accepted')->default(false)->after('starter_kit_purchased_at');
            }
            if (!Schema::hasColumn('users', 'starter_kit_terms_accepted_at')) {
                $table->timestamp('starter_kit_terms_accepted_at')->nullable()->after('starter_kit_terms_accepted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_achievements');
        Schema::dropIfExists('starter_kit_unlocks');
        Schema::dropIfExists('starter_kit_content_access');
        Schema::dropIfExists('starter_kit_purchases');

        Schema::table('users', function (Blueprint $table) {
            $shopCreditColumns = ['starter_kit_shop_credit', 'starter_kit_credit_expiry'];
            foreach ($shopCreditColumns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $columns = ['has_starter_kit', 'starter_kit_purchased_at', 'starter_kit_terms_accepted', 'starter_kit_terms_accepted_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
