<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\TeamVolume;
use App\Models\InvestmentTier;
use App\Services\MLMCommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class MLMCommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MLMCommissionService $mlmService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mlmService = new MLMCommissionService();
    }

    public function test_processes_five_level_mlm_commissions()
    {
        // Create a 5-level network structure
        $level0 = User::factory()->create(['subscription_status' => 'active']);
        $level1 = User::factory()->create(['referrer_id' => $level0->id, 'subscription_status' => 'active']);
        $level2 = User::factory()->create(['referrer_id' => $level1->id, 'subscription_status' => 'active']);
        $level3 = User::factory()->create(['referrer_id' => $level2->id, 'subscription_status' => 'active']);
        $level4 = User::factory()->create(['referrer_id' => $level3->id, 'subscription_status' => 'active']);
        $level5 = User::factory()->create(['referrer_id' => $level4->id, 'subscription_status' => 'active']);

        // Build network paths
        $this->mlmService->rebuildNetworkPaths();

        $packageAmount = 1000.00;
        
        // Process commissions for level 5 user purchase
        $commissions = $this->mlmService->processMLMCommissions($level5, $packageAmount, 'subscription');

        // Should create 5 commissions (one for each upline level)
        $this->assertCount(5, $commissions);

        // Verify commission amounts match expected rates
        $expectedRates = [12.0, 6.0, 4.0, 2.0, 1.0]; // Level 1-5 rates
        
        foreach ($commissions as $index => $commission) {
            $expectedAmount = $packageAmount * ($expectedRates[$index] / 100);
            $this->assertEquals($expectedAmount, $commission->amount);
            $this->assertEquals($index + 1, $commission->level);
            $this->assertEquals('REFERRAL', $commission->commission_type);
            $this->assertEquals('paid', $commission->status);
        }
    }

    public function test_skips_inactive_referrers()
    {
        // Create network with inactive referrer
        $level0 = User::factory()->create(['subscription_status' => 'active']);
        $level1 = User::factory()->create(['referrer_id' => $level0->id, 'subscription_status' => 'inactive']);
        $level2 = User::factory()->create(['referrer_id' => $level1->id, 'subscription_status' => 'active']);

        $this->mlmService->rebuildNetworkPaths();

        $commissions = $this->mlmService->processMLMCommissions($level2, 1000.00);

        // Should only create commission for level0 (active), skip level1 (inactive)
        $this->assertCount(1, $commissions);
        $this->assertEquals($level0->id, $commissions[0]->referrer_id);
        $this->assertEquals(2, $commissions[0]->level); // Level 2 because level 1 was skipped
    }

    public function test_updates_team_volumes_correctly()
    {
        $referrer = User::factory()->create(['subscription_status' => 'active']);
        $referee = User::factory()->create(['referrer_id' => $referrer->id, 'subscription_status' => 'active']);

        $this->mlmService->rebuildNetworkPaths();

        $packageAmount = 500.00;
        $this->mlmService->processMLMCommissions($referee, $packageAmount);

        // Check team volume was created and updated
        $teamVolume = TeamVolume::where('user_id', $referrer->id)->first();
        $this->assertNotNull($teamVolume);
        $this->assertEquals($packageAmount, $teamVolume->personal_volume);
        $this->assertEquals($packageAmount, $teamVolume->team_volume);
        $this->assertEquals(1, $teamVolume->active_referrals_count);
    }

    public function test_calculates_performance_bonuses()
    {
        $user = User::factory()->create(['subscription_status' => 'active']);
        
        // Create team volume record with high volume
        TeamVolume::create([
            'user_id' => $user->id,
            'personal_volume' => 25000,
            'team_volume' => 60000, // Should qualify for 7% bonus
            'team_depth' => 3,
            'active_referrals_count' => 15,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);

        $bonus = $this->mlmService->processPerformanceBonuses($user);

        $this->assertNotNull($bonus);
        $this->assertEquals(4200, $bonus->amount); // 60000 * 0.07
        $this->assertEquals('PERFORMANCE', $bonus->commission_type);
        $this->assertEquals('paid', $bonus->status);
    }

    public function test_rebuilds_network_paths_correctly()
    {
        // Create network structure
        $root = User::factory()->create();
        $child1 = User::factory()->create(['referrer_id' => $root->id]);
        $child2 = User::factory()->create(['referrer_id' => $root->id]);
        $grandchild = User::factory()->create(['referrer_id' => $child1->id]);

        $this->mlmService->rebuildNetworkPaths();

        // Refresh models to get updated data
        $root->refresh();
        $child1->refresh();
        $child2->refresh();
        $grandchild->refresh();

        // Verify paths are built correctly
        $this->assertEquals((string)$root->id, $root->network_path);
        $this->assertEquals(0, $root->network_level);

        $this->assertEquals($root->id . '.' . $child1->id, $child1->network_path);
        $this->assertEquals(1, $child1->network_level);

        $this->assertEquals($root->id . '.' . $child2->id, $child2->network_path);
        $this->assertEquals(1, $child2->network_level);

        $this->assertEquals($root->id . '.' . $child1->id . '.' . $grandchild->id, $grandchild->network_path);
        $this->assertEquals(2, $grandchild->network_level);
    }

    public function test_commission_rates_are_correct()
    {
        $rates = ReferralCommission::COMMISSION_RATES;
        
        $this->assertEquals(12.0, $rates[1]);
        $this->assertEquals(6.0, $rates[2]);
        $this->assertEquals(4.0, $rates[3]);
        $this->assertEquals(2.0, $rates[4]);
        $this->assertEquals(1.0, $rates[5]);
    }

    public function test_total_commission_calculation()
    {
        $user = User::factory()->create();
        
        // Create various commission types
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'level' => 1,
            'amount' => 100,
            'commission_type' => 'REFERRAL',
            'created_at' => now()
        ]);
        
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'level' => 2,
            'amount' => 50,
            'commission_type' => 'REFERRAL',
            'created_at' => now()
        ]);
        
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'level' => 0,
            'amount' => 200,
            'commission_type' => 'PERFORMANCE',
            'created_at' => now()
        ]);

        $totals = ReferralCommission::calculateTotalCommissions($user->id, 'month');

        $this->assertEquals(350, $totals['total_amount']);
        $this->assertArrayHasKey('by_level', $totals);
        $this->assertArrayHasKey('by_type', $totals);
        $this->assertEquals(100, $totals['by_level'][1]['amount']);
        $this->assertEquals(50, $totals['by_level'][2]['amount']);
        $this->assertEquals(200, $totals['by_type']['PERFORMANCE']['amount']);
    }
}