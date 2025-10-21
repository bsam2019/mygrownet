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
        Schema::create('life_point_settings', function (Blueprint $table) {
            $table->id();
            $table->string('activity_type')->unique(); // e.g., 'registration', 'referral', 'purchase', 'course_completion'
            $table->string('name'); // Display name
            $table->text('description')->nullable();
            $table->integer('lp_value'); // How many LP this activity earns
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable(); // Additional conditions
            $table->timestamps();
        });

        Schema::create('bonus_point_settings', function (Blueprint $table) {
            $table->id();
            $table->string('activity_type')->unique(); // e.g., 'registration', 'referral', 'purchase', 'course_completion'
            $table->string('name'); // Display name
            $table->text('description')->nullable();
            $table->integer('bp_value'); // How many BP this activity earns
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable(); // Additional conditions (e.g., minimum purchase amount)
            $table->timestamps();
        });

        Schema::create('bp_conversion_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('bp_to_kwacha_rate', 10, 2); // e.g., 1 BP = K0.50
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Seed default LP settings
        DB::table('life_point_settings')->insert([
            [
                'activity_type' => 'registration',
                'name' => 'New Member Registration',
                'description' => 'LP earned when you register (one-time)',
                'lp_value' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'direct_referral',
                'name' => 'Direct Referral (Level 1)',
                'description' => 'LP earned for each direct referral',
                'lp_value' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'indirect_referral',
                'name' => 'Indirect Referral (Level 2-7)',
                'description' => 'LP earned for indirect referrals',
                'lp_value' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'monthly_renewal',
                'name' => 'Monthly Subscription Renewal',
                'description' => 'LP earned each month you renew',
                'lp_value' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'course_completion',
                'name' => 'Course Completion',
                'description' => 'LP earned when you complete a course',
                'lp_value' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'workshop_attendance',
                'name' => 'Workshop Attendance',
                'description' => 'LP earned when you attend a workshop',
                'lp_value' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'downline_activation',
                'name' => 'Downline Member Activation',
                'description' => 'LP earned when you help activate a downline member',
                'lp_value' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'level_upgrade',
                'name' => 'Professional Level Upgrade',
                'description' => 'LP bonus when you upgrade to next level',
                'lp_value' => 200,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed default BP settings
        DB::table('bonus_point_settings')->insert([
            [
                'activity_type' => 'registration',
                'name' => 'New Member Registration',
                'description' => 'BP earned when you register a new member',
                'bp_value' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'direct_referral',
                'name' => 'Direct Referral (Level 1)',
                'description' => 'BP earned for direct referrals',
                'bp_value' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'indirect_referral',
                'name' => 'Indirect Referral (Level 2-7)',
                'description' => 'BP earned for indirect referrals',
                'bp_value' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'monthly_renewal',
                'name' => 'Monthly Subscription Renewal',
                'description' => 'BP earned when you renew your subscription',
                'bp_value' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'course_completion',
                'name' => 'Course Completion',
                'description' => 'BP earned when you complete a course',
                'bp_value' => 25,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'workshop_attendance',
                'name' => 'Workshop Attendance',
                'description' => 'BP earned when you attend a workshop',
                'bp_value' => 15,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'product_purchase',
                'name' => 'Product Purchase (MyGrow Shop)',
                'description' => 'BP earned per K100 spent in MyGrow Shop',
                'bp_value' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'activity_type' => 'downline_activation',
                'name' => 'Downline Member Activation',
                'description' => 'BP earned when you help activate a downline member',
                'bp_value' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed default BP conversion rate
        DB::table('bp_conversion_rates')->insert([
            'bp_to_kwacha_rate' => 0.50, // 1 BP = K0.50
            'effective_from' => now(),
            'is_current' => true,
            'notes' => 'Initial BP conversion rate',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_conversion_rates');
        Schema::dropIfExists('bonus_point_settings');
        Schema::dropIfExists('life_point_settings');
    }
};
