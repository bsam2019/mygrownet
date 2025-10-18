<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AchievementBonusPaymentService;
use App\Services\MobileMoneyService;
use App\Models\User;
use App\Models\Achievement;
use App\Models\InvestmentTier;
use App\Models\AchievementBonus;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;

class AchievementBonusPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AchievementBonusPaymentService $bonusService;
    protected $mockMobileMoneyService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockMobileMoneyService = Mockery::mock(MobileMoneyService::class);
        $this->bonusService = new AchievementBonusPaymentService($this->mockMobileMoneyService);
    }

    public function test_processes_achievement_bonus_payment_with_direct_deposit()
    {
        // Arrange
        $user = User::factory()->create([
            'balance' => 100.00,
            'phone_number' => '+260977123456'
        ]);

        $achievement = Achievement::factory()->create([
            'name' => 'Gold Tier Achievement',
            'bonus_amount' => 500.00
        ]);

        $bonus = AchievementBonus::factory()->create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'bonus_type' => 'tier_advancement',
            'amount' => 500.00,
            'status' => 'pending',
            'earned_at' => now()->subHours(25)
        ]);

        // Act
        $result = $this->bonusService->processAchievementBonusPayment($bonus);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(500.00, $result['amount']);
        $this->assertEquals('direct_deposit', $result['payment_method']);

        // Verify bonus is marked as paid
        $bonus->refresh();
        $this->assertEquals('paid', $bonus->status);
        $this->assertNotNull($bonus->paid_at);
        $this->assertEquals('wallet', $bonus->payment_method);

        // Verify user balance is updated
        $user->refresh();
        $this->assertEquals(600.00, $user->balance);
        $this->assertEquals(500.00, $user->total_earnings);

        // Verify payment transaction is created
        $this->assertEquals(1, PaymentTransaction::where('type', 'bonus_payment')->count());
    }

    public function test_processes_tier_advancement_bonuses()
    {
        // Arrange
        $silverTier = InvestmentTier::factory()->create([
            'name' => 'Silver',
            'monthly_fee' => 100.00
        ]);

        $goldTier = InvestmentTier::factory()->create([
            'name' => 'Gold',
            'monthly_fee' => 200.00
        ]);

        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id
        ]);

        $tierAchievement = Achievement::factory()->create([
            'name' => 'Gold Tier Achievement',
            'category' => 'tier_advancement',
            'tier_requirement' => 'Gold',
            'bonus_amount' => 500.00
        ]);

        // Set tier advancement bonus in config
        config(['mygrownet.tier_advancement_bonuses.Gold' => 1000.00]);

        // Act
        $results = $this->bonusService->processTierAdvancementBonuses($user, $goldTier, $silverTier);

        // Assert
        $this->assertEquals(2, $results['bonuses_created']); // Achievement + tier bonus
        $this->assertEquals(1500.00, $results['total_amount']); // 500 + 1000
        $this->assertEmpty($results['errors']);

        // Verify bonuses are created
        $this->assertEquals(2, AchievementBonus::where('user_id', $user->id)->count());
    }

    public function test_processes_performance_bonuses()
    {
        // Arrange
        $user = User::factory()->create();

        $performanceAchievement = Achievement::factory()->create([
            'name' => 'High Volume Achievement',
            'category' => 'performance',
            'volume_requirement' => 50000,
            'bonus_amount' => 1000.00
        ]);

        $teamVolume = 75000.00;

        // Act
        $results = $this->bonusService->processPerformanceBonuses($user, $teamVolume, 'monthly');

        // Assert
        $this->assertEquals(1, $results['bonuses_created']);
        $this->assertGreaterThan(0, $results['total_amount']);
        $this->assertEmpty($results['errors']);

        // Verify performance bonus is created
        $bonus = AchievementBonus::where('user_id', $user->id)
            ->where('bonus_type', 'performance')
            ->first();
        
        $this->assertNotNull($bonus);
        $this->assertEquals($teamVolume, $bonus->team_volume_at_earning);
    }

    public function test_processes_leadership_bonuses()
    {
        // Arrange
        $user = User::factory()->create();

        $leadershipAchievement = Achievement::factory()->create([
            'name' => 'Gold Leader Achievement',
            'category' => 'leadership',
            'leadership_level' => 'gold_leader'
        ]);

        $activeReferrals = 15;
        $teamVolume = 150000.00;

        // Set leadership bonus rates
        config(['mygrownet.leadership_bonuses.gold_leader' => 1.0]);

        // Act
        $results = $this->bonusService->processLeadershipBonuses($user, $activeReferrals, $teamVolume);

        // Assert
        $this->assertEquals(1, $results['bonuses_created']);
        $this->assertEquals(1500.00, $results['total_amount']); // 1% of 150,000
        $this->assertEmpty($results['errors']);

        // Verify leadership bonus is created
        $bonus = AchievementBonus::where('user_id', $user->id)
            ->where('bonus_type', 'leadership')
            ->first();
        
        $this->assertNotNull($bonus);
        $this->assertEquals($activeReferrals, $bonus->active_referrals_at_earning);
    }

    public function test_processes_all_pending_bonuses()
    {
        // Arrange
        $users = User::factory()->count(3)->create([
            'balance' => 100.00
        ]);

        foreach ($users as $user) {
            AchievementBonus::factory()->count(2)->create([
                'user_id' => $user->id,
                'status' => 'pending',
                'amount' => 250.00,
                'earned_at' => now()->subHours(25)
            ]);
        }

        // Act
        $results = $this->bonusService->processAllPendingBonuses();

        // Assert
        $this->assertEquals(6, $results['total_processed']);
        $this->assertEquals(6, $results['successful_payments']);
        $this->assertEquals(0, $results['failed_payments']);
        $this->assertEquals(1500.00, $results['total_amount']);
        $this->assertEmpty($results['errors']);

        // Verify all bonuses are paid
        $this->assertEquals(6, AchievementBonus::where('status', 'paid')->count());
    }

    public function test_validates_bonus_eligibility()
    {
        // Arrange
        $blockedUser = User::factory()->create([
            'is_blocked' => true
        ]);

        $bonus = AchievementBonus::factory()->create([
            'user_id' => $blockedUser->id,
            'status' => 'pending',
            'amount' => 100.00
        ]);

        // Act
        $result = $this->bonusService->processAchievementBonusPayment($bonus);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not eligible', $result['error']);

        // Verify bonus remains pending
        $this->assertEquals('pending', $bonus->fresh()->status);
    }

    public function test_calculates_tier_bonus_multiplier()
    {
        // Arrange
        $bronzeTier = InvestmentTier::factory()->create(['name' => 'Bronze']);
        $goldTier = InvestmentTier::factory()->create(['name' => 'Gold']);
        $eliteTier = InvestmentTier::factory()->create(['name' => 'Elite']);

        $achievement = Achievement::factory()->create([
            'bonus_amount' => 1000.00
        ]);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->bonusService);
        $method = $reflection->getMethod('calculateAchievementBonusAmount');
        $method->setAccessible(true);

        // Act & Assert
        $bronzeUser = User::factory()->create(['current_investment_tier_id' => $bronzeTier->id]);
        $bronzeAmount = $method->invoke($this->bonusService, $achievement, $bronzeUser);
        $this->assertEquals(1000.00, $bronzeAmount); // 1.0x multiplier

        $goldUser = User::factory()->create(['current_investment_tier_id' => $goldTier->id]);
        $goldAmount = $method->invoke($this->bonusService, $achievement, $goldUser);
        $this->assertEquals(1500.00, $goldAmount); // 1.5x multiplier

        $eliteUser = User::factory()->create(['current_investment_tier_id' => $eliteTier->id]);
        $eliteAmount = $method->invoke($this->bonusService, $achievement, $eliteUser);
        $this->assertEquals(2500.00, $eliteAmount); // 2.5x multiplier
    }

    public function test_determines_leadership_level_correctly()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->bonusService);
        $method = $reflection->getMethod('determineLeadershipLevel');
        $method->setAccessible(true);

        // Test different leadership levels
        $this->assertEquals('developing_leader', $method->invoke($this->bonusService, 5, 25000));
        $this->assertEquals('gold_leader', $method->invoke($this->bonusService, 10, 100000));
        $this->assertEquals('diamond_leader', $method->invoke($this->bonusService, 25, 250000));
        $this->assertEquals('elite_leader', $method->invoke($this->bonusService, 50, 500000));
        $this->assertNull($method->invoke($this->bonusService, 2, 10000)); // Below threshold
    }

    public function test_prevents_duplicate_performance_bonuses_in_same_period()
    {
        // Arrange
        $user = User::factory()->create();

        $achievement = Achievement::factory()->create([
            'category' => 'performance',
            'volume_requirement' => 50000,
            'bonus_amount' => 1000.00
        ]);

        // Create existing bonus for this period
        AchievementBonus::factory()->create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'bonus_type' => 'performance',
            'earned_at' => now()->subDays(5) // Within current month
        ]);

        // Act
        $results = $this->bonusService->processPerformanceBonuses($user, 75000, 'monthly');

        // Assert
        $this->assertEquals(0, $results['bonuses_created']); // No new bonus created
        $this->assertEquals(0, $results['total_amount']);
    }

    public function test_generates_unique_bonus_reference()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->bonusService);
        $method = $reflection->getMethod('generateBonusReference');
        $method->setAccessible(true);

        // Act
        $reference1 = $method->invoke($this->bonusService, 123);
        $reference2 = $method->invoke($this->bonusService, 123);

        // Assert
        $this->assertStringStartsWith('MGN-BONUS-123-', $reference1);
        $this->assertStringStartsWith('MGN-BONUS-123-', $reference2);
        $this->assertNotEquals($reference1, $reference2);
    }

    public function test_gets_bonus_statistics()
    {
        // Arrange
        AchievementBonus::factory()->create([
            'bonus_type' => 'tier_advancement',
            'status' => 'paid',
            'amount' => 500.00,
            'earned_at' => now()->subDays(5)
        ]);

        AchievementBonus::factory()->create([
            'bonus_type' => 'performance',
            'status' => 'pending',
            'amount' => 300.00,
            'earned_at' => now()->subDays(3)
        ]);

        // Act
        $stats = $this->bonusService->getBonusStatistics('month');

        // Assert
        $this->assertEquals(2, $stats['total_bonuses']);
        $this->assertEquals(1, $stats['paid_bonuses']);
        $this->assertEquals(1, $stats['pending_bonuses']);
        $this->assertEquals(800.00, $stats['total_amount']);
        $this->assertEquals(50.0, $stats['success_rate']);
        $this->assertArrayHasKey('by_type', $stats);
    }

    public function test_skips_bonuses_within_delay_period()
    {
        // Arrange
        $user = User::factory()->create();

        // Create bonus within 24-hour delay period
        $recentBonus = AchievementBonus::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'amount' => 100.00,
            'earned_at' => now()->subHours(12) // Within 24-hour delay
        ]);

        // Create eligible bonus
        $eligibleBonus = AchievementBonus::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'amount' => 100.00,
            'earned_at' => now()->subHours(25) // Outside 24-hour delay
        ]);

        // Act
        $results = $this->bonusService->processAllPendingBonuses();

        // Assert
        $this->assertEquals(1, $results['total_processed']); // Only eligible bonus
        $this->assertEquals(1, $results['successful_payments']);
        $this->assertEquals(100.00, $results['total_amount']);

        // Verify only eligible bonus is paid
        $this->assertEquals('paid', $eligibleBonus->fresh()->status);
        $this->assertEquals('pending', $recentBonus->fresh()->status);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}