<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AllocateRetroactiveAssetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestTiers();
        $this->createTestAssets();
    }

    public function test_command_runs_successfully_in_dry_run_mode()
    {
        $this->createEligibleUser();

        $exitCode = Artisan::call('mygrownet:allocate-retroactive-assets', ['--dry-run' => true]);

        $this->assertEquals(0, $exitCode);
        
        // Verify no changes were made in dry-run mode
        $this->assertEquals(0, PhysicalRewardAllocation::count());
        $this->assertEquals(0, DB::table('asset_maintenance_schedules')->count());
    }

    public function test_command_allocates_assets_to_qualified_users()
    {
        $user = $this->createEligibleUser();

        $exitCode = Artisan::call('mygrownet:allocate-retroactive-assets');

        $this->assertEquals(0, $exitCode);

        // Verify asset allocation was created
        $this->assertDatabaseHas('physical_reward_allocations', [
            'user_id' => $user->id,
            'allocation_status' => 'allocated'
        ]);

        // Verify maintenance schedule was created
        $allocation = PhysicalRewardAllocation::where('user_id', $user->id)->first();
        $this->assertDatabaseHas('asset_maintenance_schedules', [
            'allocation_id' => $allocation->id,
            'milestone_type' => 'performance_review'
        ]);
    }

    public function test_stats_option_displays_statistics()
    {
        $user = $this->createEligibleUser();
        
        // Create an allocation
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $asset = PhysicalReward::where('type', 'SMARTPHONE')->first();
        
        PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'tier_id' => $silverTier->id,
            'allocation_status' => 'allocated',
            'maintenance_status' => 'pending',
            'allocated_at' => now()
        ]);

        $exitCode = Artisan::call('mygrownet:allocate-retroactive-assets', ['--stats' => true]);

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('Asset Allocation Statistics', $output);
        $this->assertStringContains('Total Users', $output);
        $this->assertStringContains('High-Tier Users', $output);
        $this->assertStringContains('Allocations by Tier', $output);
    }

    public function test_validate_option_validates_allocations()
    {
        $user = $this->createEligibleUser();
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $asset = PhysicalReward::where('type', 'SMARTPHONE')->first();

        // Create valid allocation
        PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'tier_id' => $silverTier->id,
            'allocation_status' => 'allocated',
            'maintenance_status' => 'pending',
            'maintenance_due_at' => now()->addMonths(12),
            'allocated_at' => now()
        ]);

        $exitCode = Artisan::call('mygrownet:allocate-retroactive-assets', ['--validate' => true]);

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('Validating Asset Allocations', $output);
        $this->assertStringContains('All asset allocations are valid', $output);
    }

    public function test_validate_option_detects_issues()
    {
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $asset = PhysicalReward::where('type', 'SMARTPHONE')->first();

        // Create user with no team volume (invalid)
        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id,
            'current_team_volume' => 0
        ]);

        PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'tier_id' => $silverTier->id,
            'allocation_status' => 'allocated',
            'maintenance_status' => 'pending',
            'maintenance_due_at' => now()->subDays(30), // Overdue
            'allocated_at' => now()->subMonths(2)
        ]);

        $exitCode = Artisan::call('mygrownet:allocate-retroactive-assets', ['--validate' => true]);

        $this->assertEquals(1, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('Found issues with asset allocations', $output);
    }

    public function test_maintenance_option_processes_schedules()
    {
        $user = $this->createEligibleUser();
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $asset = PhysicalReward::where('type', 'SMARTPHONE')->first();

        $allocation = PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'tier_id' => $silverTier->id,
            'team_volume_at_allocation' => 15000,
            'allocation_status' => 'allocated',
            'maintenance_status' => 'pending',
            'allocated_at' => now()->subMonths(3)
        ]);

        // Create overdue maintenance item
        DB::table('asset_maintenance_schedules')->insert([
            'allocation_id' => $allocation->id,
            'milestone_type' => 'performance_review',
            'due_date' => now()->subDays(1),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $exitCode = Artisan::call('mygrownet:allocate-retroactive-assets', ['--maintenance' => true]);

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('Processing Asset Maintenance Schedules', $output);
        $this->assertStringContains('Maintenance Items Processed', $output);
    }

    public function test_command_handles_no_eligible_users()
    {
        // Create user that doesn't meet criteria
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        
        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id,
            'tier_upgraded_at' => now()->subMonth() // Only 1 month, needs 3
        ]);

        TeamVolume::create([
            'user_id' => $user->id,
            'team_volume' => 5000, // Below Silver requirement of 15000
            'active_referrals_count' => 1, // Below Silver requirement of 3
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        $exitCode = Artisan::call('mygrownet:allocate-retroactive-assets');

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('No assets were allocated', $output);
        $this->assertStringContains('Review eligibility criteria', $output);
    }

    public function test_command_displays_progress_and_results()
    {
        $this->createEligibleUser();

        $exitCode = Artisan::call('mygrownet:allocate-retroactive-assets');

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('MyGrowNet Retroactive Asset Allocation', $output);
        $this->assertStringContains('Identifying eligible high-tier users', $output);
        $this->assertStringContains('Evaluating historical performance', $output);
        $this->assertStringContains('Allocating assets to qualified users', $output);
        $this->assertStringContains('Creating maintenance schedules', $output);
        $this->assertStringContains('ALLOCATION COMPLETED', $output);
    }

    public function test_command_shows_success_message_with_benefits()
    {
        $this->createEligibleUser();

        Artisan::call('mygrownet:allocate-retroactive-assets');
        
        $output = Artisan::output();
        $this->assertStringContains('Retroactive asset allocation completed successfully', $output);
        $this->assertStringContains('Physical asset rewards based on historical performance', $output);
        $this->assertStringContains('Asset maintenance schedules for ownership transfer', $output);
        $this->assertStringContains('Recognition of past achievements and loyalty', $output);
        $this->assertStringContains('Incentive for continued high performance', $output);
    }

    public function test_command_handles_errors_gracefully()
    {
        // Mock a service that throws an exception
        $this->mock(\App\Services\RetroactiveAssetAllocationService::class)
            ->shouldReceive('allocateRetroactiveAssets')
            ->andThrow(new \Exception('Test error'));

        $exitCode = Artisan::call('mygrownet:allocate-retroactive-assets');

        $this->assertEquals(1, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('Command failed: Test error', $output);
    }

    public function test_command_shows_allocations_by_tier()
    {
        // Create users for different tiers
        $silverUser = $this->createEligibleUser('Silver');
        $goldUser = $this->createEligibleUser('Gold');

        Artisan::call('mygrownet:allocate-retroactive-assets');
        
        $output = Artisan::output();
        $this->assertStringContains('Assets Allocated by Tier', $output);
        $this->assertStringContains('Silver', $output);
        $this->assertStringContains('Gold', $output);
    }

    private function createEligibleUser(string $tierName = 'Silver'): User
    {
        $tier = InvestmentTier::where('name', $tierName)->first();
        
        // Set requirements based on tier
        $requirements = [
            'Silver' => ['months' => 4, 'volume' => 20000, 'referrals' => 5],
            'Gold' => ['months' => 8, 'volume' => 60000, 'referrals' => 15],
            'Diamond' => ['months' => 12, 'volume' => 200000, 'referrals' => 30],
            'Elite' => ['months' => 15, 'volume' => 600000, 'referrals' => 60]
        ];

        $req = $requirements[$tierName];

        $user = User::factory()->create([
            'current_investment_tier_id' => $tier->id,
            'tier_upgraded_at' => now()->subMonths($req['months']),
            'tier_history' => json_encode([
                ['tier_id' => $tier->id, 'date' => now()->subMonths($req['months'])->toDateTimeString(), 'reason' => 'upgrade']
            ])
        ]);

        TeamVolume::create([
            'user_id' => $user->id,
            'personal_volume' => $req['volume'] * 0.3,
            'team_volume' => $req['volume'],
            'active_referrals_count' => $req['referrals'],
            'team_depth' => 3,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        return $user;
    }

    private function createTestTiers(): void
    {
        $tiers = [
            [
                'name' => 'Silver',
                'minimum_investment' => 0,
                'monthly_fee' => 300,
                'monthly_share' => 150,
                'monthly_team_volume_bonus_rate' => 2,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Gold',
                'minimum_investment' => 0,
                'monthly_fee' => 500,
                'monthly_share' => 300,
                'monthly_team_volume_bonus_rate' => 5,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'order' => 3,
                'is_active' => true
            ],
            [
                'name' => 'Diamond',
                'minimum_investment' => 0,
                'monthly_fee' => 1000,
                'monthly_share' => 500,
                'monthly_team_volume_bonus_rate' => 7,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'order' => 4,
                'is_active' => true
            ],
            [
                'name' => 'Elite',
                'minimum_investment' => 0,
                'monthly_fee' => 1500,
                'monthly_share' => 700,
                'monthly_team_volume_bonus_rate' => 10,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'order' => 5,
                'is_active' => true
            ]
        ];

        foreach ($tiers as $tier) {
            InvestmentTier::create($tier);
        }
    }

    private function createTestAssets(): void
    {
        $assets = [
            ['type' => 'SMARTPHONE', 'model' => 'Test Smartphone', 'value' => 3000, 'status' => 'available'],
            ['type' => 'TABLET', 'model' => 'Test Tablet', 'value' => 4000, 'status' => 'available'],
            ['type' => 'MOTORBIKE', 'model' => 'Test Motorbike', 'value' => 12000, 'status' => 'available'],
            ['type' => 'CAR', 'model' => 'Test Car', 'value' => 35000, 'status' => 'available'],
            ['type' => 'LUXURY_CAR', 'model' => 'Test Luxury Car', 'value' => 100000, 'status' => 'available']
        ];

        foreach ($assets as $asset) {
            PhysicalReward::create($asset);
        }
    }
}