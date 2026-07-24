<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enhance existing physical_rewards table for MyGrowNet performance-based allocation
        if (Schema::hasTable('physical_rewards')) {
            Schema::table('physical_rewards', function (Blueprint $table) {
                // Team volume requirements for MyGrowNet
                if (!Schema::hasColumn('physical_rewards', 'required_team_volume')) {
                    $table->decimal('required_team_volume', 15, 2)->default(0)->after('required_referrals');
                }
                if (!Schema::hasColumn('physical_rewards', 'required_team_depth')) {
                    $table->integer('required_team_depth')->default(0)->after('required_team_volume');
                }
                
                // Performance maintenance requirements
                if (!Schema::hasColumn('physical_rewards', 'maintenance_period_months')) {
                    $table->integer('maintenance_period_months')->default(12)->after('required_sustained_months');
                }
                if (!Schema::hasColumn('physical_rewards', 'requires_performance_maintenance')) {
                    $table->boolean('requires_performance_maintenance')->default(true)->after('maintenance_period_months');
                }
                
                // Asset management features
                if (!Schema::hasColumn('physical_rewards', 'income_generating')) {
                    $table->boolean('income_generating')->default(false)->after('requires_performance_maintenance');
                }
                if (!Schema::hasColumn('physical_rewards', 'estimated_monthly_income')) {
                    $table->decimal('estimated_monthly_income', 10, 2)->default(0)->after('income_generating');
                }
                if (!Schema::hasColumn('physical_rewards', 'asset_management_options')) {
                    $table->json('asset_management_options')->nullable()->after('estimated_monthly_income');
                }
                
                // Ownership transfer conditions
                if (!Schema::hasColumn('physical_rewards', 'ownership_type')) {
                    $table->enum('ownership_type', ['conditional', 'immediate', 'gradual'])->default('conditional')->after('asset_management_options');
                }
                if (!Schema::hasColumn('physical_rewards', 'ownership_conditions')) {
                    $table->text('ownership_conditions')->nullable()->after('ownership_type');
                }
            });

            // Add indexes for efficient queries with short names (ignore if already exist)
            try {
                Schema::table('physical_rewards', function (Blueprint $table) {
                    $table->index(['required_team_volume', 'is_active'], 'phys_rewards_rtv_active_idx');
                    $table->index(['category', 'required_team_volume'], 'phys_rewards_cat_rtv_idx');
                });
            } catch (Throwable $e) {
                // ignore if exists
            }
        }

        // Insert MyGrowNet physical rewards only if table exists
        if (Schema::hasTable('physical_rewards')) {
            $this->insertMyGrowNetPhysicalRewards();
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('physical_rewards')) {
            Schema::table('physical_rewards', function (Blueprint $table) {
                // Drop indexes first by name
                try { $table->dropIndex('phys_rewards_rtv_active_idx'); } catch (Throwable $e) {}
                try { $table->dropIndex('phys_rewards_cat_rtv_idx'); } catch (Throwable $e) {}
                
                // Drop MyGrowNet-specific columns if they exist
                foreach ([
                    'required_team_volume',
                    'required_team_depth',
                    'maintenance_period_months',
                    'requires_performance_maintenance',
                    'income_generating',
                    'estimated_monthly_income',
                    'asset_management_options',
                    'ownership_type',
                    'ownership_conditions'
                ] as $col) {
                    if (Schema::hasColumn('physical_rewards', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    private function insertMyGrowNetPhysicalRewards(): void
    {
        if (!Schema::hasTable('physical_rewards')) {
            return;
        }
        $rewards = [
            // Bronze Tier Rewards
            [
                'name' => 'MyGrowNet Starter Kit',
                'description' => 'Branded merchandise, training materials, and business tools for new Bronze members',
                'category' => 'business_kit',
                'estimated_value' => 500,
                'required_membership_tiers' => json_encode(['Bronze']),
                'required_referrals' => 1,
                'required_team_volume' => 0,
                'required_team_depth' => 1,
                'required_subscription_amount' => 150,
                'required_sustained_months' => 1,
                'maintenance_period_months' => 0, // No maintenance required
                'requires_performance_maintenance' => false,
                'income_generating' => false,
                'estimated_monthly_income' => 0,
                'asset_management_options' => null,
                'ownership_type' => 'immediate',
                'ownership_conditions' => 'Immediate ownership upon completion of first month with 1+ active referral',
                'available_quantity' => 1000,
                'allocated_quantity' => 0,
                'specifications' => json_encode([
                    'contents' => ['Branded t-shirt', 'Business cards', 'Training manual', 'Welcome package'],
                    'delivery_method' => 'Local pickup or delivery'
                ]),
                'terms_and_conditions' => 'Must maintain Bronze tier for 1 month with at least 1 active referral',
                'is_active' => true
            ],
            
            // Silver Tier Rewards
            [
                'name' => 'Smartphone or Tablet Package',
                'description' => 'Choice of smartphone or tablet for Silver tier members with sustained performance',
                'category' => 'electronics',
                'estimated_value' => 3000,
                'required_membership_tiers' => json_encode(['Silver']),
                'required_referrals' => 3,
                'required_team_volume' => 15000,
                'required_team_depth' => 2,
                'required_subscription_amount' => 300,
                'required_sustained_months' => 3,
                'maintenance_period_months' => 12,
                'requires_performance_maintenance' => true,
                'income_generating' => false,
                'estimated_monthly_income' => 0,
                'asset_management_options' => null,
                'ownership_type' => 'conditional',
                'ownership_conditions' => 'Full ownership after maintaining Silver tier for 12 months with K15,000+ team volume',
                'available_quantity' => 100,
                'allocated_quantity' => 0,
                'specifications' => json_encode([
                    'options' => ['Samsung Galaxy A54', 'iPhone SE', 'iPad 9th Gen', 'Samsung Tab A8'],
                    'warranty' => '2 years',
                    'accessories' => 'Case, screen protector, charger'
                ]),
                'terms_and_conditions' => 'Must maintain Silver tier for 3 months with K15,000+ team volume. Asset recovery if performance drops below requirements.',
                'is_active' => true
            ],
            
            // Gold Tier Rewards
            [
                'name' => 'Motorbike Package',
                'description' => 'Motorbike for transportation and potential income generation for Gold tier members',
                'category' => 'vehicle',
                'estimated_value' => 12000,
                'required_membership_tiers' => json_encode(['Gold']),
                'required_referrals' => 10,
                'required_team_volume' => 50000,
                'required_team_depth' => 3,
                'required_subscription_amount' => 500,
                'required_sustained_months' => 6,
                'maintenance_period_months' => 12,
                'requires_performance_maintenance' => true,
                'income_generating' => true,
                'estimated_monthly_income' => 800,
                'asset_management_options' => json_encode([
                    'rental_program' => 'Participate in motorbike taxi network',
                    'delivery_services' => 'Food and package delivery partnerships',
                    'maintenance_support' => 'Monthly maintenance allowance'
                ]),
                'ownership_type' => 'conditional',
                'ownership_conditions' => 'Full ownership after maintaining Gold tier for 12 months with K50,000+ team volume',
                'available_quantity' => 50,
                'allocated_quantity' => 0,
                'specifications' => json_encode([
                    'model' => 'Honda CB125F or equivalent',
                    'engine' => '125cc',
                    'fuel_efficiency' => '45km/L',
                    'insurance' => '2 years comprehensive',
                    'registration' => 'Included'
                ]),
                'terms_and_conditions' => 'Must maintain Gold tier for 6 months with K50,000+ team volume. Asset can be used for income generation.',
                'is_active' => true
            ],
            
            [
                'name' => 'Office Equipment Package',
                'description' => 'Complete office setup for Gold tier members building their business',
                'category' => 'business_kit',
                'estimated_value' => 8000,
                'required_membership_tiers' => json_encode(['Gold']),
                'required_referrals' => 10,
                'required_team_volume' => 50000,
                'required_team_depth' => 3,
                'required_subscription_amount' => 500,
                'required_sustained_months' => 6,
                'maintenance_period_months' => 12,
                'requires_performance_maintenance' => true,
                'income_generating' => true,
                'estimated_monthly_income' => 500,
                'asset_management_options' => json_encode([
                    'co_working_space' => 'Rent out desk space',
                    'business_services' => 'Offer printing and admin services',
                    'training_venue' => 'Host MyGrowNet training sessions'
                ]),
                'ownership_type' => 'conditional',
                'ownership_conditions' => 'Full ownership after maintaining Gold tier for 12 months with K50,000+ team volume',
                'available_quantity' => 30,
                'allocated_quantity' => 0,
                'specifications' => json_encode([
                    'contents' => ['Laptop', 'Printer', 'Desk', 'Chair', 'Filing cabinet', 'Projector'],
                    'software' => 'Microsoft Office, accounting software',
                    'setup_support' => 'Professional installation included'
                ]),
                'terms_and_conditions' => 'Must maintain Gold tier for 6 months with K50,000+ team volume. Can be used for income generation.',
                'is_active' => true
            ],
            
            // Diamond Tier Rewards
            [
                'name' => 'Car Package',
                'description' => 'Vehicle for Diamond tier members with exceptional performance',
                'category' => 'vehicle',
                'estimated_value' => 35000,
                'required_membership_tiers' => json_encode(['Diamond']),
                'required_referrals' => 25,
                'required_team_volume' => 150000,
                'required_team_depth' => 4,
                'required_subscription_amount' => 1000,
                'required_sustained_months' => 9,
                'maintenance_period_months' => 12,
                'requires_performance_maintenance' => true,
                'income_generating' => true,
                'estimated_monthly_income' => 2000,
                'asset_management_options' => json_encode([
                    'ride_sharing' => 'Uber/Bolt partnership program',
                    'rental_services' => 'Car rental to other members',
                    'business_transport' => 'Corporate transport services'
                ]),
                'ownership_type' => 'conditional',
                'ownership_conditions' => 'Full ownership after maintaining Diamond tier for 12 months with K150,000+ team volume',
                'available_quantity' => 20,
                'allocated_quantity' => 0,
                'specifications' => json_encode([
                    'model' => 'Toyota Vitz or Honda Fit',
                    'year' => '2020 or newer',
                    'fuel_type' => 'Petrol',
                    'insurance' => '3 years comprehensive',
                    'warranty' => '2 years manufacturer warranty'
                ]),
                'terms_and_conditions' => 'Must maintain Diamond tier for 9 months with K150,000+ team volume. Asset can be used for income generation.',
                'is_active' => true
            ],
            
            [
                'name' => 'Property Down Payment',
                'description' => 'Down payment assistance for small property investment for Diamond tier members',
                'category' => 'property',
                'estimated_value' => 25000,
                'required_membership_tiers' => json_encode(['Diamond']),
                'required_referrals' => 25,
                'required_team_volume' => 150000,
                'required_team_depth' => 4,
                'required_subscription_amount' => 1000,
                'required_sustained_months' => 9,
                'maintenance_period_months' => 12,
                'requires_performance_maintenance' => true,
                'income_generating' => true,
                'estimated_monthly_income' => 1500,
                'asset_management_options' => json_encode([
                    'rental_management' => 'Property management services',
                    'tenant_screening' => 'Tenant vetting and placement',
                    'maintenance_support' => 'Property maintenance coordination'
                ]),
                'ownership_type' => 'gradual',
                'ownership_conditions' => 'Down payment provided after 9 months. Full property ownership through payment plan.',
                'available_quantity' => 15,
                'allocated_quantity' => 0,
                'specifications' => json_encode([
                    'property_type' => 'Residential apartment or small house',
                    'location' => 'Lusaka or Copperbelt',
                    'size' => '2-3 bedrooms',
                    'services' => 'Legal assistance, property search, financing support'
                ]),
                'terms_and_conditions' => 'Must maintain Diamond tier for 9 months with K150,000+ team volume. Property management services included.',
                'is_active' => true
            ],
            
            // Elite Tier Rewards
            [
                'name' => 'Luxury Car Package',
                'description' => 'Premium vehicle for Elite tier members with outstanding performance',
                'category' => 'vehicle',
                'estimated_value' => 75000,
                'required_membership_tiers' => json_encode(['Elite']),
                'required_referrals' => 50,
                'required_team_volume' => 500000,
                'required_team_depth' => 5,
                'required_subscription_amount' => 1500,
                'required_sustained_months' => 12,
                'maintenance_period_months' => 12,
                'requires_performance_maintenance' => true,
                'income_generating' => true,
                'estimated_monthly_income' => 3500,
                'asset_management_options' => json_encode([
                    'luxury_rental' => 'Premium car rental services',
                    'executive_transport' => 'Corporate executive transport',
                    'event_services' => 'Wedding and event car rental'
                ]),
                'ownership_type' => 'conditional',
                'ownership_conditions' => 'Full ownership after maintaining Elite tier for 12 months with K500,000+ team volume',
                'available_quantity' => 10,
                'allocated_quantity' => 0,
                'specifications' => json_encode([
                    'model' => 'Toyota Camry, Honda Accord, or BMW 3 Series',
                    'year' => '2021 or newer',
                    'features' => 'Leather seats, navigation, premium sound system',
                    'insurance' => '3 years comprehensive',
                    'maintenance' => '3 years service plan included'
                ]),
                'terms_and_conditions' => 'Must maintain Elite tier for 12 months with K500,000+ team volume. Premium asset management services included.',
                'is_active' => true
            ],
            
            [
                'name' => 'Property Investment Package',
                'description' => 'Complete property investment opportunity for Elite tier members',
                'category' => 'property',
                'estimated_value' => 100000,
                'required_membership_tiers' => json_encode(['Elite']),
                'required_referrals' => 50,
                'required_team_volume' => 500000,
                'required_team_depth' => 5,
                'required_subscription_amount' => 1500,
                'required_sustained_months' => 12,
                'maintenance_period_months' => 12,
                'requires_performance_maintenance' => true,
                'income_generating' => true,
                'estimated_monthly_income' => 4000,
                'asset_management_options' => json_encode([
                    'full_management' => 'Complete property management service',
                    'tenant_guarantee' => 'Guaranteed rental income program',
                    'appreciation_tracking' => 'Annual property valuation',
                    'exit_strategy' => 'Market-rate buyback option'
                ]),
                'ownership_type' => 'conditional',
                'ownership_conditions' => 'Full ownership after maintaining Elite tier for 12 months with K500,000+ team volume',
                'available_quantity' => 5,
                'allocated_quantity' => 0,
                'specifications' => json_encode([
                    'property_type' => 'Multi-unit residential or commercial property',
                    'location' => 'Prime locations in Lusaka',
                    'size' => '4+ units or commercial space',
                    'services' => 'Full legal, financial, and management support'
                ]),
                'terms_and_conditions' => 'Must maintain Elite tier for 12 months with K500,000+ team volume. Complete investment management included.',
                'is_active' => true
            ]
        ];

        foreach ($rewards as $reward) {
            DB::table('physical_rewards')->updateOrInsert(
                ['name' => $reward['name']],
                array_merge($reward, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }
};