<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_modules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Unique module identifier (e.g., grownet, growbuilder)');
            $table->string('name', 100)->comment('Human-readable module name');
            $table->text('description')->nullable();
            $table->boolean('is_revenue_module')->default(true)->comment('Does this module generate revenue?');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable()->comment('Module-specific settings');
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
        });

        // Seed initial financial modules
        DB::table('financial_modules')->insert([
            [
                'code' => 'platform',
                'name' => 'MyGrowNet Platform',
                'description' => 'Core platform infrastructure',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'wallet',
                'name' => 'Wallet',
                'description' => 'Digital wallet system',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'grownet',
                'name' => 'GrowNet',
                'description' => 'MLM/Network Marketing module',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'growbuilder',
                'name' => 'GrowBuilder',
                'description' => 'Business building tools',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'marketplace',
                'name' => 'Marketplace',
                'description' => 'Product marketplace',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'shop',
                'name' => 'Shop',
                'description' => 'Digital and physical products',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'learning',
                'name' => 'Learning Platform',
                'description' => 'Courses and training',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'workshops',
                'name' => 'Workshops',
                'description' => 'Event registrations',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'coaching',
                'name' => 'Coaching',
                'description' => 'One-on-one coaching sessions',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'starter_kits',
                'name' => 'Starter Kits',
                'description' => 'Onboarding packages',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'lgr',
                'name' => 'LGR',
                'description' => 'Loyalty Growth Rewards',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 90,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'loans',
                'name' => 'Loans',
                'description' => 'Member loans',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'commissions',
                'name' => 'Commissions',
                'description' => 'Referral commissions',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 110,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'profit_share',
                'name' => 'Community Rewards',
                'description' => 'Community profit sharing',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 120,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_modules');
    }
};
