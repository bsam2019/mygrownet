<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bronze, Silver, Gold, Diamond, Elite
            $table->string('slug')->unique();
            $table->text('description');
            $table->integer('referral_requirement')->default(0); // Required referrals
            $table->decimal('subscription_requirement', 10, 2)->default(0); // Required subscription level
            $table->integer('months_sustained_requirement')->default(0); // Required sustained activity
            
            // Digital/Monetary Incentives
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->json('digital_benefits')->nullable();
            
            // Physical/Asset Incentives
            $table->json('physical_rewards')->nullable(); // Tablet, land, motorbike, etc.
            $table->decimal('asset_value_range_min', 10, 2)->default(0);
            $table->decimal('asset_value_range_max', 10, 2)->default(0);
            
            // Community & Growth Perks
            $table->boolean('mentorship_access')->default(false);
            $table->boolean('project_priority')->default(false);
            $table->boolean('voting_rights')->default(false);
            $table->boolean('leaderboard_eligible')->default(false);
            $table->boolean('innovation_lab_access')->default(false);
            $table->boolean('annual_gala_invite')->default(false);
            
            $table->string('badge_color')->default('#gray');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_tiers');
    }
};