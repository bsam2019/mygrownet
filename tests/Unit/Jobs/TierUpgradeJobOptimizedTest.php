<?php

namespace Tests\Unit\Jobs;

use App\Jobs\TierUpgradeJob;
use App\Models\User;
use App\Models\InvestmentTier;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TierUpgradeJobOptimizedTest extends TestCase
{
    use RefreshDatabase;

    protected InvestmentTier $basicTier;
    protected InvestmentTier $starterTier;

    protected function setUp(): void
    {
        parent::setUp();
        
        Notification::fake();
        Log::spy();
        
        // Create minimal tiers for testing
        $this->basicTier = InvestmentTier::create([
            'name' => 'Basic',
            'minimum_investment' => 500,
            'fixed_profit_rate' => 3.0,
            'direct_referral_rate' => 5.0,
            'level2_referral_rate' => 0.0,
            'level3_referral_rate' => 0.0,
            'reinvestment_bonus_rate' => 0.0,
            'order' => 1
        ]);

        $this->starterTier = InvestmentTier::create([
            'name' => 'Starter',
            'minimum_investment' => 1000,
            'fixed_profit_rate' => 5.0,
            'direct_referral_rate' => 7.0,
            'level2_referral_rate' => 2.0,
            'level3_referral_rate' => 0.0,
            'reinvestment_bonus_rate' => 8.0,
            'order' => 2
        ]);
    }

    public function test_can_be_constructed_with_single_upgrade_parameters()
    {
        $job = new TierUpgradeJob(
            jobType: 'process_single_upgrade',
            userId: 123
        );

        $this->assertEquals('process_single_upgrade', $job->jobType);
        $this->assertEquals(123, $job->userId);
        $this->assertEquals([], $job->additionalData);
    }

    public function test_has_correct_queue_configuration()
    {
        $job = new TierUpgradeJob('process_single_upgrade', 123);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
        $this->assertEquals([30, 60, 120], $job->backoff);
    }

    public function test_handles_invalid_job_type()
    {
        $job = new TierUpgradeJob('invalid_job_type');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid job type: invalid_job_type');

        $job->handle();
    }

    public function test_handles_user_not_found_for_single_upgrade()
    {
        $job = new TierUpgradeJob('process_single_upgrade', 999);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not found: 999');

        $job->handle();
    }

    public function test_returns_correct_tags_for_single_upgrade()
    {
        $job = new TierUpgradeJob('process_single_upgrade', 123);

        $tags = $job->tags();

        $this->assertEquals([
            'tier-upgrade',
            'process_single_upgrade',
            'user:123'
        ], $tags);
    }

    public function test_returns_correct_tags_for_batch_processing()
    {
        $job = new TierUpgradeJob('batch_process_upgrades');

        $tags = $job->tags();

        $this->assertEquals([
            'tier-upgrade',
            'batch_process_upgrades'
        ], $tags);
    }
}