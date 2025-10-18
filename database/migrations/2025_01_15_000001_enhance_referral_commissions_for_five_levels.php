<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_commissions', function (Blueprint $table) {
            // Add team volume tracking fields if missing
            if (!Schema::hasColumn('referral_commissions', 'team_volume')) {
                $table->decimal('team_volume', 15, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('referral_commissions', 'personal_volume')) {
                $table->decimal('personal_volume', 15, 2)->default(0)->after('team_volume');
            }

            // Add commission type to distinguish different types
            if (!Schema::hasColumn('referral_commissions', 'commission_type')) {
                $table->enum('commission_type', ['REFERRAL', 'TEAM_VOLUME', 'PERFORMANCE'])->default('REFERRAL')->after('level');
            }

            // Add package/subscription reference for MyGrowNet
            if (!Schema::hasColumn('referral_commissions', 'package_type')) {
                $table->string('package_type')->nullable()->after('investment_id');
            }
            if (!Schema::hasColumn('referral_commissions', 'package_amount')) {
                $table->decimal('package_amount', 15, 2)->nullable()->after('package_type');
            }
        });

        // Add indexes for efficient queries (ignore if they already exist)
        try {
            Schema::table('referral_commissions', function (Blueprint $table) {
                $table->index(['level', 'commission_type']);
                $table->index(['referrer_id', 'commission_type', 'status']);
            });
        } catch (Throwable $e) {
            // Indexes may already exist; ignore
        }

        // Create team_volumes table for tracking team performance (if missing)
        if (!Schema::hasTable('team_volumes')) {
            Schema::create('team_volumes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->decimal('personal_volume', 15, 2)->default(0);
                $table->decimal('team_volume', 15, 2)->default(0);
                $table->integer('team_depth')->default(0);
                $table->integer('active_referrals_count')->default(0);
                $table->date('period_start');
                $table->date('period_end');
                $table->timestamps();
                
                $table->index(['user_id', 'period_start', 'period_end']);
                $table->index(['team_volume', 'period_start']);
            });
        }

        // Create user_networks table for efficient network traversal (if missing)
        if (!Schema::hasTable('user_networks')) {
            Schema::create('user_networks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
                $table->integer('level'); // 1-5 levels deep
                $table->string('path', 255); // Materialized path (e.g., "1.5.23.45"). String to allow indexing on MySQL
                $table->timestamps();
                
                $table->index(['user_id', 'level']);
                $table->index(['referrer_id', 'level']);
                $table->index('path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('referral_commissions', function (Blueprint $table) {
            $table->dropIndex(['level', 'commission_type']);
            $table->dropIndex(['referrer_id', 'commission_type', 'status']);
            
            $table->dropColumn([
                'team_volume',
                'personal_volume', 
                'commission_type',
                'package_type',
                'package_amount'
            ]);
        });

        Schema::dropIfExists('user_networks');
        Schema::dropIfExists('team_volumes');
    }
};