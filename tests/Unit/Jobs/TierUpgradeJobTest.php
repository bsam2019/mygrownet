<?php

namespace Tests\Unit\Jobs;

use App\Jobs\TierUpgradeJob;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\Investment;
use App\Notifications\TierUpgradeNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class TierUpgradeJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Notification::fake();
        Log::spy();
        
        // Create investment tiers for testing
        $this->createInvestmentTiers();
    }

    protected function createInvestmentTiers(): void
    {
        InvestmentTier::create([
            'name' => 'Basic',
            'minimum_investment' => 500,
            'fixed_profit_rate' => 3.0,
            'direct_referral_rate' => 5.0,
            'level2_referral_rate' => 0.0,
            'level3_referral_rate' => 0.0,
            'reinvestment_bonus_rate' => 0.0,
            'order' => 1
        ]);

        InvestmentTier::create([
            'name' => 'Starter',
            'minimum_investment' => 1000,
            'fixed_profit_rate' => 5.0,
            'direct_referral_rate' => 7.0,
            'level2_referral_rate' => 2.0,
            'level3_referral_rate' => 0.0,
            'reinvestment_bonus_rate' => 8.0,
            'order' => 2
        ]);

        InvestmentTier::create([
            'name' => 'Builder',
            'minimum_investment' => 2500,
            'fixed_profit_rate' => 7.0,
            'direct_referral_rate' => 10.0,
            'level2_referral_rate' => 3.0,
            'level3_referral_rate' => 1.0,
            'reinvestment_bonus_rate' => 10.0,
            'order' => 3
        ]);

        InvestmentTier::create([
            'name' => 'Leader',
            'minimum_investment' => 5000,
            'fixed_profit_rate' => 10.0,
            'direct_referral_rate' => 12.0,
            'level2_referral_rate' => 5.0,
            'level3_referral_rate' => 2.0,
            'reinvestment_bonus_rate' => 12.0,
            'order' => 4
        ]);

        InvestmentTier::create([
            'name' => 'Elite',
            'minimum_investment' => 10000,
            'fixed_profit_rate' => 15.0,
            'direct_referral_rate' => 15.0,
            'level2_referral_rate' => 7.0,
            'level3_referral_rate' => 3.0,
            'reinvestment_bonus_rate' => 17.0,
            'order' => 5
        ]);
    }

    /** @test */
    public function it_can_be_constructed_with_single_upgrade_parameters()
    {
        $job = new TierUpgradeJob(
            jobType: 'process_single_upgrade',
            userId: 123
        );

        $this->assertEquals('process_single_upgrade', $job->jobType);
        $this->assertEquals(123, $job->userId);
        $this->assertEquals([], $job->additionalData);
    }

    /** @test */
    public function it_can_be_constructed_with_batch_processing_parameters()
    {
        $job = new TierUpgradeJob(
            jobType: 'batch_process_upgrades',
            additionalData: ['batch_size' => 50, 'max_processing_time' => 180]
        );

        $this->assertEquals('batch_process_upgrades', $job->jobType);
        $this->assertEquals(['batch_size' => 50, 'max_processing_time' => 180], $job->additionalData);
        $this->assertNull($job->userId);
    }

    /** @test */
    public function it_can_be_constructed_with_eligibility_check_parameters()
    {
        $job = new TierUpgradeJob(
            jobType: 'check_upgrade_eligibility',
            additionalData: ['batch_size' => 200]
        );

        $this->assertEquals('check_upgrade_eligibility', $job->jobType);
        $this->assertEquals(['batch_size' => 200], $job->additionalData);
    }

    /** @test */
    public function it_has_correct_queue_configuration()
    {
        $job = new TierUpgradeJob('process_single_upgrade', 123);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
        $this->assertEquals([30, 60, 120], $job->backoff);
    }

    /** @test */
    public function it_handles_invalid_job_type()
    {
        $job = new TierUpgradeJob('invalid_job_type');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid job type: invalid_job_type');

        $job->handle();

        Log::shouldHaveReceived('error')
            ->with('Failed to process invalid_job_type tier upgrades', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_processes_single_tier_upgrade_successfully()
    {
        // Create a user with Basic tier and sufficient investment for Starter tier
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $user = User::factory()->create([
            'current_investment_tier_id' => $basicTier->id,
            'total_investment_amount' => 1500.00 // Enough for Starter tier
        ]);

        $job = new TierUpgradeJob('process_single_upgrade', $user->id);
        $job->handle();

        // Verify user was upgraded
        $user->refresh();
        $this->assertEquals('Starter', $user->currentInvestmentTier->name);
        $this->assertNotNull($user->tier_upgraded_at);

        // Verify notification was sent
        Notification::assertSentTo($user, TierUpgradeNotification::class, function ($notification) {
            return $notification->data['type'] === 'tier_upgraded' &&
                   $notification->data['from_tier'] === 'Basic' &&
                   $notification->data['to_tier'] === 'Starter';
        });

        Log::shouldHaveReceived('info')
            ->with('Starting process_single_upgrade tier upgrade processing', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_handles_user_not_found_for_single_upgrade()
    {
        $job = new TierUpgradeJob('process_single_upgrade', 999);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found: 999');

        $job->handle();
    }

    /** @test */
    public function it_handles_user_with_no_upgrade_needed()
    {
        // Create a user with Starter tier and investment amount that doesn't qualify for next tier
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        $user = User::factory()->create([
            'current_investment_tier_id' => $starterTier->id,
            'total_investment_amount' => 1200.00 // Not enough for Builder tier (2500)
        ]);

        $job = new TierUpgradeJob('process_single_upgrade', $user->id);
        $job->handle();

        // Verify user tier remained the same
        $user->refresh();
        $this->assertEquals('Starter', $user->currentInvestmentTier->name);

        // Verify no upgrade notification was sent
        Notification::assertNotSentTo($user, TierUpgradeNotification::class, function ($notification) {
            return $notification->data['type'] === 'tier_upgraded';
        });
    }

    /** @test */
    public function it_upgrades_user_from_no_tier_to_appropriate_tier()
    {
        // Create a user with no tier but sufficient investment
        $user = User::factory()->create([
            'current_investment_tier_id' => null,
            'total_investment_amount' => 3000.00 // Enough for Builder tier
        ]);

        $job = new TierUpgradeJob('process_single_upgrade', $user->id);
        $job->handle();

        // Verify user was upgraded to Builder tier
        $user->refresh();
        $this->assertEquals('Builder', $user->currentInvestmentTier->name);

        // Verify notification was sent
        Notification::assertSentTo($user, TierUpgradeNotification::class, function ($notification) {
            return $notification->data['type'] === 'tier_upgraded' &&
                   $notification->data['from_tier'] === 'None' &&
                   $notification->data['to_tier'] === 'Builder';
        });
    }

    /** @test */
    public function it_processes_batch_tier_upgrades_successfully()
    {
        // Create multiple users eligible for upgrades
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();

        $user1 = User::factory()->create([
            'current_investment_tier_id' => $basicTier->id,
            'total_investment_amount' => 1500.00 // Starter tier
        ]);

        $user2 = User::factory()->create([
            'current_investment_tier_id' => $starterTier->id,
            'total_investment_amount' => 3000.00 // Builder tier
        ]);

        $user3 = User::factory()->create([
            'current_investment_tier_id' => null,
            'total_investment_amount' => 6000.00 // Leader tier
        ]);

        $job = new TierUpgradeJob('batch_process_upgrades', additionalData: ['batch_size' => 10]);
        $job->handle();

        // Verify all users were upgraded
        $user1->refresh();
        $user2->refresh();
        $user3->refresh();

        $this->assertEquals('Starter', $user1->currentInvestmentTier->name);
        $this->assertEquals('Builder', $user2->currentInvestmentTier->name);
        $this->assertEquals('Leader', $user3->currentInvestmentTier->name);

        // Verify notifications were sent to users
        Notification::assertSentTo($user1, TierUpgradeNotification::class);
        Notification::assertSentTo($user2, TierUpgradeNotification::class);
        Notification::assertSentTo($user3, TierUpgradeNotification::class);

        // Verify admin notification was sent
        $admins = User::role('admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::assertSentTo($admins->first(), TierUpgradeNotification::class, function ($notification) {
                return $notification->data['type'] === 'batch_upgrade_complete';
            });
        }
    }

    /** @test */
    public function it_handles_empty_batch_processing()
    {
        // No users eligible for upgrades
        $job = new TierUpgradeJob('batch_process_upgrades', additionalData: ['batch_size' => 10]);
        $job->handle();

        Log::shouldHaveReceived('info')
            ->with('Successfully completed batch_process_upgrades processing', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_checks_upgrade_eligibility_and_sends_notifications()
    {
        // Create users eligible for upgrades
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $user1 = User::factory()->create([
            'current_investment_tier_id' => $basicTier->id,
            'total_investment_amount' => 1200.00 // Eligible for Starter
        ]);

        $user2 = User::factory()->create([
            'current_investment_tier_id' => $basicTier->id,
            'total_investment_amount' => 800.00 // Not eligible for upgrade
        ]);

        $job = new TierUpgradeJob('check_upgrade_eligibility', additionalData: ['batch_size' => 10]);
        $job->handle();

        // Verify eligible user received notification
        Notification::assertSentTo($user1, TierUpgradeNotification::class, function ($notification) {
            return $notification->data['type'] === 'tier_upgrade_available';
        });

        // Verify non-eligible user did not receive notification
        Notification::assertNotSentTo($user2, TierUpgradeNotification::class, function ($notification) {
            return $notification->data['type'] === 'tier_upgrade_available';
        });
    }

    /** @test */
    public function it_finds_appropriate_tier_for_investment_amount()
    {
        $job = new TierUpgradeJob('process_single_upgrade', 1);
        
        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('findAppropriateUserTier');
        $method->setAccessible(true);

        // Test different investment amounts
        $tier = $method->invoke($job, 600.00);
        $this->assertEquals('Basic', $tier->name);

        $tier = $method->invoke($job, 1500.00);
        $this->assertEquals('Starter', $tier->name);

        $tier = $method->invoke($job, 3000.00);
        $this->assertEquals('Builder', $tier->name);

        $tier = $method->invoke($job, 7500.00);
        $this->assertEquals('Leader', $tier->name);

        $tier = $method->invoke($job, 15000.00);
        $this->assertEquals('Elite', $tier->name);

        // Test amount below minimum
        $tier = $method->invoke($job, 100.00);
        $this->assertNull($tier);
    }

    /** @test */
    public function it_calculates_tier_benefits_correctly()
    {
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        $job = new TierUpgradeJob('process_single_upgrade', 1);
        
        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('calculateTierBenefits');
        $method->setAccessible(true);

        $benefits = $method->invoke($job, $starterTier);

        $this->assertEquals(5.0, $benefits['profit_rate']);
        $this->assertEquals(7.0, $benefits['referral_rate_level_1']);
        $this->assertEquals(2.0, $benefits['referral_rate_level_2']);
        $this->assertEquals(0.0, $benefits['referral_rate_level_3']);
        $this->assertEquals(8.0, $benefits['reinvestment_bonus_rate']);
        $this->assertTrue($benefits['matrix_commission_eligible']);
    }

    /** @test */
    public function it_returns_correct_tags_for_single_upgrade()
    {
        $job = new TierUpgradeJob('process_single_upgrade', 123);

        $tags = $job->tags();

        $this->assertEquals([
            'tier-upgrade',
            'process_single_upgrade',
            'user:123'
        ], $tags);
    }

    /** @test */
    public function it_returns_correct_tags_for_batch_processing()
    {
        $job = new TierUpgradeJob('batch_process_upgrades');

        $tags = $job->tags();

        $this->assertEquals([
            'tier-upgrade',
            'batch_process_upgrades'
        ], $tags);
    }

    /** @test */
    public function it_logs_critical_failure_when_job_fails_permanently()
    {
        $exception = new Exception('Critical tier upgrade failure');
        
        $job = new TierUpgradeJob('process_single_upgrade', 123);

        // Mock the attempts method
        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(3);

        $job->failed($exception);

        Log::shouldHaveReceived('critical')
            ->with('TierUpgradeJob failed permanently', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_handles_database_transaction_rollback_on_error()
    {
        // Create a user
        $user = User::factory()->create([
            'total_investment_amount' => 1500.00
        ]);

        // Mock the job to throw an exception during processing
        $job = Mockery::mock(TierUpgradeJob::class, ['process_single_upgrade', $user->id])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $job->shouldReceive('processUserTierUpgrade')
            ->once()
            ->andThrow(new Exception('Database error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database error');

        $job->handle();

        // Verify user was not modified due to rollback
        $user->refresh();
        $this->assertNull($user->current_investment_tier_id);
    }

    /** @test */
    public function it_handles_notification_failures_gracefully()
    {
        $job = new TierUpgradeJob('process_single_upgrade', 123);
        
        // Mock a successful result
        $result = [
            'upgrade_processed' => true,
            'user_id' => 999, // Non-existent user
            'from_tier' => 'Basic',
            'to_tier' => 'Starter',
            'new_benefits' => ['profit_rate' => 5.0, 'referral_rate_level_1' => 7.0]
        ];

        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('sendSuccessNotifications');
        $method->setAccessible(true);

        // Should not throw exception even if notification fails
        $method->invoke($job, $result);

        Log::shouldHaveReceived('warning')
            ->with('Failed to send success notifications for process_single_upgrade', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_records_tier_history_when_upgrading()
    {
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $user = User::factory()->create([
            'current_investment_tier_id' => $basicTier->id,
            'total_investment_amount' => 1500.00,
            'tier_history' => []
        ]);

        $job = new TierUpgradeJob('process_single_upgrade', $user->id);
        $job->handle();

        $user->refresh();
        $tierHistory = $user->getTierHistory();
        
        $this->assertNotEmpty($tierHistory);
        $this->assertCount(1, $tierHistory);
        
        $latestHistory = end($tierHistory);
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        $this->assertEquals($starterTier->id, $latestHistory['tier_id']);
        $this->assertStringContains('Automatic upgrade', $latestHistory['reason']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}