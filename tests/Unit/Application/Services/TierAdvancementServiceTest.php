<?php

namespace Tests\Unit\Application\Services;

use Tests\TestCase;
use App\Application\Services\TierAdvancementService;
use App\Application\UseCases\MLM\ProcessTierAdvancementUseCase;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\TierQualification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TierAdvancementServiceTest extends TestCase
{
    use RefreshDatabase;

    private TierAdvancementService $service;
    private ProcessTierAdvancementUseCase $processTierAdvancementUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->processTierAdvancementUseCase = $this->createMock(ProcessTierAdvancementUseCase::class);
        $this->service = new TierAdvancementService($this->processTierAdvancementUseCase);
        
        $this->createTiers();
    }

    private function createTiers(): void
    {
        InvestmentTier::factory()->create(['name' => 'Bronze Member']);
        InvestmentTier::factory()->create(['name' => 'Silver Member']);
        InvestmentTier::factory()->create(['name' => 'Gold Member']);
        InvestmentTier::factory()->create(['name' => 'Diamond Member']);
        InvestmentTier::factory()->create(['name' => 'Elite Member']);
    }

    public function test_process_user_tier_advancement()
    {
        // Arrange
        $userId = 1;
        $expectedResult = [
            'qualified' => true,
            'upgraded' => true,
            'old_tier' => 'Bronze Member',
            'new_tier' => 'Silver Member',
            'achievement_bonus' => 500
        ];

        $this->processTierAdvancementUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($userId)
            ->willReturn($expectedResult);

        // Act
        $result = $this->service->processUserTierAdvancement($userId);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function test_process_all_eligible_users()
    {
        // Arrange
        $bronzeTier = InvestmentTier::where('name', 'Bronze Member')->first();
        $silverTier = InvestmentTier::where('name', 'Silver Member')->first();
        
        $user1 = User::factory()->create([
            'investment_tier_id' => $bronzeTier->id,
            'monthly_team_volume' => 6000
        ]);
        
        $user2 = User::factory()->create([
            'investment_tier_id' => $silverTier->id,
            'monthly_team_volume' => 16000
        ]);

        // Create referrals for users to make them eligible
        User::factory()->count(3)->create(['referrer_id' => $user1->id]);
        User::factory()->count(10)->create(['referrer_id' => $user2->id]);

        $this->processTierAdvancementUseCase
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(
                [
                    'qualified' => true,
                    'upgraded' => true,
                    'old_tier' => 'Bronze Member',
                    'new_tier' => 'Silver Member',
                    'achievement_bonus' => 500
                ],
                [
                    'qualified' => false,
                    'current_tier' => 'Silver Member',
                    'next_tier' => 'Gold Member'
                ]
            );

        // Act
        $results = $this->service->processAllEligibleUsers();

        // Assert
        $this->assertEquals(2, $results['processed']);
        $this->assertEquals(1, $results['advanced']);
        $this->assertEquals(0, $results['failed']);
        $this->assertCount(1, $results['advancements']);
        
        $advancement = $results['advancements'][0];
        $this->assertEquals($user1->id, $advancement['user_id']);
        $this->assertEquals('Bronze Member', $advancement['old_tier']);
        $this->assertEquals('Silver Member', $advancement['new_tier']);
        $this->assertEquals(500, $advancement['achievement_bonus']);
    }

    public function test_get_tier_advancement_progress_bronze_to_silver()
    {
        // Arrange
        $bronzeTier = InvestmentTier::where('name', 'Bronze Member')->first();
        $user = User::factory()->create([
            'investment_tier_id' => $bronzeTier->id,
            'monthly_team_volume' => 3000
        ]);

        // Create 2 active referrals (needs 3 for Silver)
        User::factory()->count(2)->create([
            'referrer_id' => $user->id,
            'investment_tier_id' => $bronzeTier->id
        ]);

        // Act
        $progress = $this->service->getTierAdvancementProgress($user->id);

        // Assert
        $this->assertEquals('Bronze Member', $progress['current_tier']);
        $this->assertEquals('Silver Member', $progress['next_tier']);
        $this->assertEquals(2, $progress['current_stats']['active_referrals']);
        $this->assertEquals(3000, $progress['current_stats']['team_volume']);
        $this->assertEquals(3, $progress['requirements']['active_referrals']);
        $this->assertEquals(5000, $progress['requirements']['team_volume']);
        
        // Check progress percentages
        $this->assertEquals(66.67, round($progress['progress']['referrals']['percentage'], 2));
        $this->assertEquals(60.0, $progress['progress']['team_volume']['percentage']);
        $this->assertFalse($progress['qualifies']);
    }

    public function test_get_tier_advancement_progress_elite_tier()
    {
        // Arrange
        $eliteTier = InvestmentTier::where('name', 'Elite Member')->first();
        $user = User::factory()->create(['investment_tier_id' => $eliteTier->id]);

        // Act
        $progress = $this->service->getTierAdvancementProgress($user->id);

        // Assert
        $this->assertEquals('Elite Member', $progress['current_tier']);
        $this->assertTrue($progress['is_highest_tier']);
        $this->assertEquals('User is at the highest tier', $progress['message']);
    }

    public function test_process_monthly_tier_maintenance_maintains_tier()
    {
        // Arrange
        $silverTier = InvestmentTier::where('name', 'Silver Member')->first();
        $user = User::factory()->create([
            'investment_tier_id' => $silverTier->id,
            'monthly_team_volume' => 6000
        ]);

        // Create sufficient referrals
        User::factory()->count(5)->create([
            'referrer_id' => $user->id,
            'investment_tier_id' => $silverTier->id
        ]);

        $qualification = TierQualification::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $silverTier->id,
            'active_referrals' => 5,
            'team_volume' => 6000,
            'consecutive_months' => 1,
            'created_at' => now()->subMonth()
        ]);

        // Act
        $results = $this->service->processMonthlyTierMaintenance();

        // Assert
        $this->assertEquals(1, $results['maintained']);
        $this->assertEquals(0, $results['downgraded']);
        $this->assertEquals(1, $results['consecutive_updated']);
        
        // Check that consecutive months was incremented
        $qualification->refresh();
        $this->assertEquals(2, $qualification->consecutive_months);
    }

    public function test_process_monthly_tier_maintenance_grants_permanent_status()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $user = User::factory()->create([
            'investment_tier_id' => $goldTier->id,
            'monthly_team_volume' => 20000
        ]);

        // Create sufficient referrals
        User::factory()->count(12)->create([
            'referrer_id' => $user->id,
            'investment_tier_id' => $goldTier->id
        ]);

        $qualification = TierQualification::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $goldTier->id,
            'active_referrals' => 12,
            'team_volume' => 20000,
            'consecutive_months' => 2, // Will become 3 after increment
            'is_permanent' => false,
            'created_at' => now()->subMonth()
        ]);

        // Act
        $results = $this->service->processMonthlyTierMaintenance();

        // Assert
        $this->assertEquals(1, $results['maintained']);
        
        // Check that permanent status was granted
        $qualification->refresh();
        $this->assertEquals(3, $qualification->consecutive_months);
        $this->assertTrue($qualification->is_permanent);
    }

    public function test_process_monthly_tier_maintenance_downgrades_tier()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $silverTier = InvestmentTier::where('name', 'Silver Member')->first();
        
        $user = User::factory()->create([
            'investment_tier_id' => $goldTier->id,
            'monthly_team_volume' => 8000 // Below Gold requirement of 15000
        ]);

        // Create insufficient referrals (5 instead of required 10)
        User::factory()->count(5)->create([
            'referrer_id' => $user->id,
            'investment_tier_id' => $silverTier->id
        ]);

        $qualification = TierQualification::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $goldTier->id,
            'active_referrals' => 5,
            'team_volume' => 8000,
            'consecutive_months' => 1,
            'is_permanent' => false,
            'created_at' => now()->subMonth()
        ]);

        // Act
        $results = $this->service->processMonthlyTierMaintenance();

        // Assert
        $this->assertEquals(0, $results['maintained']);
        $this->assertEquals(1, $results['downgraded']);
        
        // Check that user was downgraded
        $user->refresh();
        $this->assertEquals($silverTier->id, $user->investment_tier_id);
    }

    public function test_tier_requirements_mapping()
    {
        $testCases = [
            'Silver Member' => [
                'active_referrals' => 3,
                'team_volume' => 5000,
                'achievement_bonus' => 500,
                'team_volume_bonus' => 2
            ],
            'Gold Member' => [
                'active_referrals' => 10,
                'team_volume' => 15000,
                'achievement_bonus' => 2000,
                'team_volume_bonus' => 5
            ],
            'Diamond Member' => [
                'active_referrals' => 25,
                'team_volume' => 50000,
                'achievement_bonus' => 5000,
                'team_volume_bonus' => 7
            ],
            'Elite Member' => [
                'active_referrals' => 50,
                'team_volume' => 150000,
                'achievement_bonus' => 10000,
                'team_volume_bonus' => 10
            ]
        ];

        foreach ($testCases as $tierName => $expectedRequirements) {
            $tier = InvestmentTier::where('name', $tierName)->first();
            $user = User::factory()->create(['investment_tier_id' => $tier->id]);
            
            $progress = $this->service->getTierAdvancementProgress($user->id);
            
            if (!isset($progress['is_highest_tier'])) {
                $this->assertEquals($expectedRequirements, $progress['requirements']);
            }
        }
    }
}