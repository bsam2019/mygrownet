<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Unique module identifier (e.g., grownet, growbuilder)');
            $table->string('name', 100)->comment('Human-readable module name');
            $table->text('description')->nullable();
            $table->boolean('is_revenue_module')->default(true)->comment('Does this module generate revenue?');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable()->comment('Module-specific settings');
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index(['is_active', 'is_revenue_module']);
        });

        // Seed initial modules
        DB::table('modules')->insert([
            [
                'code' => 'mygrownet',
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
                'name' => 'GrowNet (MLM)',
                'description' => 'Multi-level marketing and network building',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'growbuilder',
                'name' => 'GrowBuilder',
                'description' => 'Business building tools and services',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'marketplace',
                'name' => 'Marketplace',
                'description' => 'Product marketplace for buying and selling',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'shop',
                'name' => 'Shop',
                'description' => 'Digital and physical product shop',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'learning',
                'name' => 'Learning Platform',
                'description' => 'Courses, e-books, and training materials',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'workshop',
                'name' => 'Workshops',
                'description' => 'Event registrations and workshop payments',
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
                'code' => 'starter_kit',
                'name' => 'Starter Kits',
                'description' => 'Onboarding packages (Lite, Basic, Growth Plus, Pro)',
                'is_revenue_module' => true,
                'is_active' => true,
                'display_order' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'lgr',
                'name' => 'Loyalty Growth Rewards',
                'description' => 'LGR credit system',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'loans',
                'name' => 'Loans',
                'description' => 'Member loan system',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 110,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'commissions',
                'name' => 'Commissions',
                'description' => 'Referral and network commissions',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 120,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'profit_share',
                'name' => 'Profit Share',
                'description' => 'Community profit sharing',
                'is_revenue_module' => false,
                'is_active' => true,
                'display_order' => 130,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
