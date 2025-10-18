<?php

namespace Tests\Unit\Application\Services;

use Tests\TestCase;
use App\Application\Services\CommissionProcessingService;
use App\Application\UseCases\MLM\ProcessCommissionsUseCase;
use App\Application\UseCases\MLM\ProcessTierAdvancementUseCase;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CommissionProcessingServiceTest extends TestCase
{
    use RefreshDatabase;

    private CommissionProcessingService $service;
    private ProcessCommissionsUseCase $processCommissionsUseCase;
    private ProcessTierAdvancementUseCase $processTierAdvancementUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->processCommissionsUseCase = $this->createMock(ProcessCommissionsUseCase::class);
        $this->processTierAdvancementUseCase = $this->createMock(ProcessTierAdvancementUseCase::class);
        
        $this->service = new CommissionProcessingService(
            $this->processCommissionsUseCase,
            $this->processTierAdvancementUseCase
        );
    }

    public function test_process_package_purchase_commissions_success()
    {
        // Arrange
        $userId = 1;
        $packageAmount = 1000.0;
        $packageType = 'membership';
        
        $mockCommissions = collect([
            (object)['amount' => 120.0], // 12% Level 1
            (object)['amount' => 60.0],  // 6% Level 2
            (object)['amount' => 40.0],  // 4% Level 3
        ]);

        $this->processCommissionsUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($userId, $packageAmount, $packageType)
            ->willReturn($mockCommissions);

        $this->processTierAdvancementUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($userId);

        // Act
        $result = $this->service->processPackagePurchaseCommissions($userId, $packageAmount, $packageType);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['commissions_created']);
        $this->assertEquals(220.0, $result['total_commission_amount']);
        $this->assertEquals($mockCommissions, $result['commissions']);
    }

    public function test_process_package_purchase_commissions_handles_exception()
    {
        // Arrange
        $userId = 1;
        $packageAmount = 1000.0;
        $errorMessage = 'Database error';

        $this->processCommissionsUseCase
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception($errorMessage));

        // Act
        $result = $this->service->processPackagePurchaseCommissions($userId, $packageAmount);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals($errorMessage, $result['error']);
    }

    public function test_process_monthly_team_volume_bonuses()
    {
        // Arrange
        $tier = InvestmentTier::factory()->create(['name' => 'Gold Member']);
        $user = User::factory()->create([
            'investment_tier_id' => $tier->id,
            'monthly_team_volume' => 20000
        ]);

        // Act
        $results = $this->service->processMonthlyTeamVolumeBonuses();

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals($user->id, $results[0]['user_id']);
        $this->assertEquals(1000.0, $results[0]['bonus_amount']); // 20000 * 5%
        
        // Verify commission was created
        $this->assertDatabaseHas('referral_commissions', [
            'user_id' => $user->id,
            'commission_type' => 'TEAM_VOLUME',
            'amount' => 1000.0
        ]);
    }

    public function test_process_pending_payments()
    {
        // Arrange
        $user = User::factory()->create();
        $commission1 = ReferralCommission::factory()->create([
            'user_id' => $user->id,
            'amount' => 100.0,
            'status' => 'pending',
            'earned_at' => now()->subDays(2)
        ]);
        
        $commission2 = ReferralCommission::factory()->create([
            'user_id' => $user->id,
            'amount' => 50.0,
            'status' => 'pending',
            'earned_at' => now()->subDays(1)
        ]);

        // Act
        $results = $this->service->processPendingPayments();

        // Assert
        $this->assertEquals(2, $results['processed']);
        $this->assertEquals(0, $results['failed']);
        $this->assertEquals(150.0, $results['total_amount']);
        
        // Verify commissions were marked as paid
        $this->assertDatabaseHas('referral_commissions', [
            'id' => $commission1->id,
            'status' => 'paid'
        ]);
        
        $this->assertDatabaseHas('referral_commissions', [
            'id' => $commission2->id,
            'status' => 'paid'
        ]);
    }

    public function test_get_commission_stats()
    {
        // Arrange
        $user = User::factory()->create();
        
        // Paid commissions
        ReferralCommission::factory()->create([
            'user_id' => $user->id,
            'amount' => 100.0,
            'status' => 'paid',
            'commission_type' => 'REFERRAL',
            'level' => 1,
            'paid_at' => now()
        ]);
        
        ReferralCommission::factory()->create([
            'user_id' => $user->id,
            'amount' => 50.0,
            'status' => 'paid',
            'commission_type' => 'TEAM_VOLUME',
            'level' => 0,
            'paid_at' => now()
        ]);
        
        // Pending commission
        ReferralCommission::factory()->create([
            'user_id' => $user->id,
            'amount' => 25.0,
            'status' => 'pending',
            'commission_type' => 'REFERRAL',
            'level' => 2
        ]);

        // Act
        $stats = $this->service->getCommissionStats($user->id);

        // Assert
        $this->assertEquals(150.0, $stats['total_earned']);
        $this->assertEquals(25.0, $stats['pending_amount']);
        $this->assertEquals(150.0, $stats['this_month']);
        
        $this->assertEquals([
            'REFERRAL' => 100.0,
            'TEAM_VOLUME' => 50.0
        ], $stats['commission_breakdown']);
        
        $this->assertEquals([
            1 => 100.0,
            0 => 50.0
        ], $stats['level_breakdown']);
    }

    public function test_calculate_team_volume_bonus_rates()
    {
        // Test different tier bonus rates
        $testCases = [
            ['Silver Member', 10000, 200.0], // 2%
            ['Gold Member', 10000, 500.0],   // 5%
            ['Diamond Member', 10000, 700.0], // 7%
            ['Elite Member', 10000, 1000.0], // 10%
            ['Bronze Member', 10000, 0.0],   // 0%
        ];

        foreach ($testCases as [$tierName, $teamVolume, $expectedBonus]) {
            $tier = InvestmentTier::factory()->create(['name' => $tierName]);
            $user = User::factory()->create([
                'investment_tier_id' => $tier->id,
                'monthly_team_volume' => $teamVolume
            ]);

            $results = $this->service->processMonthlyTeamVolumeBonuses();
            
            if ($expectedBonus > 0) {
                $this->assertCount(1, $results);
                $this->assertEquals($expectedBonus, $results[0]['bonus_amount']);
            } else {
                $this->assertCount(0, $results);
            }
            
            // Clean up for next iteration
            $user->delete();
            $tier->delete();
        }
    }
}