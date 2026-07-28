<?php

namespace Tests\Unit\GrowNet\LoyaltyReward;

use App\Domain\GrowNet\LoyaltyReward\Entities\LgrCycle;
use App\Domain\GrowNet\LoyaltyReward\Entities\RewardPool;
use App\Domain\GrowNet\LoyaltyReward\Entities\LgrQualification;
use App\Domain\GrowNet\LoyaltyReward\Entities\LoyaltyGrowthCycle;
use App\Domain\GrowNet\LoyaltyReward\Entities\LoyaltyActivity;
use App\Domain\GrowNet\LoyaltyReward\ValueObjects\CycleId;
use App\Domain\GrowNet\LoyaltyReward\ValueObjects\LoyaltyAmount;
use App\Domain\GrowNet\LoyaltyReward\ValueObjects\CycleStatus;
use App\Domain\GrowNet\LoyaltyReward\ValueObjects\ActivityType;
use Carbon\Carbon;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    // --- LgrCycle Entity ---

    public function test_lgr_cycle_create(): void
    {
        $startDate = Carbon::now();
        $cycle = LgrCycle::create(userId: 1, startDate: $startDate);

        $this->assertNull($cycle->getId());
        $this->assertEquals(1, $cycle->getUserId());
        $this->assertTrue($cycle->getStartDate()->eq($startDate));
        $this->assertTrue($cycle->getEndDate()->eq($startDate->copy()->addDays(69)));
        $this->assertEquals('active', $cycle->getStatus());
        $this->assertEquals(0, $cycle->getActiveDays());
        $this->assertEquals(0.0, $cycle->getTotalEarnedLgc());
        $this->assertEquals(25.00, $cycle->getDailyRate());
        $this->assertTrue($cycle->isActive());
        $this->assertFalse($cycle->isCompleted());
    }

    public function test_lgr_cycle_duration_is_70_days(): void
    {
        $this->assertEquals(70, LgrCycle::CYCLE_DURATION_DAYS);
    }

    public function test_lgr_cycle_max_earnings(): void
    {
        $this->assertEquals(1750.0, LgrCycle::MAX_EARNINGS);
        $this->assertEquals(70 * 25.0, LgrCycle::MAX_EARNINGS);
    }

    public function test_lgr_cycle_record_activity(): void
    {
        $cycle = LgrCycle::create(1, Carbon::now());
        $cycle->recordActivity(25.00);

        $this->assertEquals(1, $cycle->getActiveDays());
        $this->assertEquals(25.00, $cycle->getTotalEarnedLgc());

        $cycle->recordActivity(25.00);
        $this->assertEquals(2, $cycle->getActiveDays());
        $this->assertEquals(50.00, $cycle->getTotalEarnedLgc());
    }

    public function test_lgr_cycle_complete(): void
    {
        $cycle = LgrCycle::create(1, Carbon::now());
        $this->assertFalse($cycle->isCompleted());

        $cycle->complete();
        $this->assertTrue($cycle->isCompleted());
        $this->assertEquals('completed', $cycle->getStatus());
    }

    public function test_lgr_cycle_suspend(): void
    {
        $cycle = LgrCycle::create(1, Carbon::now());
        $cycle->suspend('Member request');
        $this->assertEquals('suspended', $cycle->getStatus());
        $this->assertFalse($cycle->isActive());
    }

    public function test_lgr_cycle_can_earn_today_when_active(): void
    {
        $now = Carbon::now();
        $cycle = LgrCycle::create(1, $now);
        $this->assertTrue($cycle->canEarnToday($now));
    }

    public function test_lgr_cycle_cannot_earn_when_completed(): void
    {
        $cycle = LgrCycle::create(1, Carbon::now());
        $cycle->complete();
        $this->assertFalse($cycle->canEarnToday(Carbon::now()));
    }

    public function test_lgr_cycle_cannot_earn_beyond_max(): void
    {
        $cycle = LgrCycle::create(1, Carbon::now()->subDays(10));

        // Record 70 days of activity at max
        for ($i = 0; $i < 70; $i++) {
            $cycle->recordActivity(25.00);
        }

        $this->assertEquals(1750.0, $cycle->getTotalEarnedLgc());
        $this->assertFalse($cycle->canEarnToday(Carbon::now()));
    }

    public function test_lgr_cycle_cannot_earn_before_start(): void
    {
        $future = Carbon::now()->addDays(10);
        $cycle = LgrCycle::create(1, $future);

        $this->assertFalse($cycle->canEarnToday(Carbon::now()));
    }

    public function test_lgr_cycle_get_remaining_days(): void
    {
        $cycle = LgrCycle::create(1, Carbon::now());
        $this->assertGreaterThan(0, $cycle->getRemainingDays());
    }

    public function test_lgr_cycle_get_remaining_days_zero_when_completed(): void
    {
        $cycle = LgrCycle::create(1, Carbon::now());
        $cycle->complete();
        $this->assertEquals(0, $cycle->getRemainingDays());
    }

    public function test_lgr_cycle_get_projected_earnings(): void
    {
        $cycle = LgrCycle::create(1, Carbon::now());
        $projected = $cycle->getProjectedEarnings();
        // Should be remaining days * 25, capped at 1750
        $this->assertGreaterThan(0, $projected);
        $this->assertLessThanOrEqual(1750, $projected);
    }

    public function test_lgr_cycle_get_completion_rate(): void
    {
        $cycle = LgrCycle::create(1, Carbon::now());
        $this->assertEquals(0, $cycle->getCompletionRate());

        // 35 active days out of 70 = 50%
        for ($i = 0; $i < 35; $i++) {
            $cycle->recordActivity(25.00);
        }
        $this->assertEquals(50, $cycle->getCompletionRate());
    }

    public function test_lgr_cycle_from_array(): void
    {
        $cycle = LgrCycle::fromArray([
            'id' => 5,
            'user_id' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-11',
            'status' => 'active',
            'active_days' => 30,
            'total_earned_lgc' => 750.00,
            'daily_rate' => 25.00,
        ]);

        $this->assertEquals(5, $cycle->getId());
        $this->assertEquals(1, $cycle->getUserId());
        $this->assertEquals(30, $cycle->getActiveDays());
        $this->assertEquals(750.0, $cycle->getTotalEarnedLgc());
        $this->assertTrue($cycle->isActive());
    }

    public function test_lgr_cycle_to_array(): void
    {
        $cycle = LgrCycle::create(1, Carbon::now());
        $arr = $cycle->toArray();

        $this->assertNull($arr['id']);
        $this->assertEquals(1, $arr['user_id']);
        $this->assertEquals('active', $arr['status']);
        $this->assertEquals(0, $arr['active_days']);
        $this->assertEquals(25.00, $arr['daily_rate']);
        $this->assertArrayHasKey('remaining_days', $arr);
        $this->assertArrayHasKey('projected_earnings', $arr);
        $this->assertArrayHasKey('completion_rate', $arr);
    }

    // --- RewardPool Entity ---

    public function test_reward_pool_create(): void
    {
        $pool = RewardPool::create();
        $this->assertEquals(0, $pool->getId());
        $this->assertTrue($pool->getTotalBalance()->equals(LoyaltyAmount::zero()));
        $this->assertTrue($pool->getAvailableBalance()->equals(LoyaltyAmount::zero()));
        $this->assertTrue($pool->getReservedBalance()->equals(LoyaltyAmount::zero()));
        $this->assertInstanceOf(DateTimeImmutable::class, $pool->getLastUpdated());
    }

    public function test_reward_pool_add_funds(): void
    {
        $pool = RewardPool::create();
        $pool->addFunds(LoyaltyAmount::fromKwacha(1000), 'revenue');

        // 30% reserve = 300, 70% available = 700
        $this->assertEquals(1000, $pool->getTotalBalance()->toKwacha());
        $this->assertEquals(700, $pool->getAvailableBalance()->toKwacha());
        $this->assertEquals(300, $pool->getReservedBalance()->toKwacha());
    }

    public function test_reward_pool_add_funds_multiple(): void
    {
        $pool = RewardPool::create();
        $pool->addFunds(LoyaltyAmount::fromKwacha(1000), 'revenue');
        $pool->addFunds(LoyaltyAmount::fromKwacha(500), 'donation');

        // Total: 1500, Reserve: 30% of 1500 = 450, Available: 1050
        $this->assertEquals(1500, $pool->getTotalBalance()->toKwacha());
        $this->assertEquals(1050, $pool->getAvailableBalance()->toKwacha());
        $this->assertEquals(450, $pool->getReservedBalance()->toKwacha());
    }

    public function test_reward_pool_allocate_rewards(): void
    {
        $pool = RewardPool::create();
        $pool->addFunds(LoyaltyAmount::fromKwacha(1000), 'revenue');

        $pool->allocateRewards(LoyaltyAmount::fromKwacha(300));
        // Available: 700 - 300 = 400
        $this->assertEquals(400, $pool->getAvailableBalance()->toKwacha());
    }

    public function test_reward_pool_allocate_insufficient_throws(): void
    {
        $pool = RewardPool::create();
        $pool->addFunds(LoyaltyAmount::fromKwacha(100), 'revenue');

        $this->expectException(\DomainException::class);
        // Available is 70, trying to allocate 200
        $pool->allocateRewards(LoyaltyAmount::fromKwacha(200));
    }

    public function test_reward_pool_can_allocate(): void
    {
        $pool = RewardPool::create();
        $pool->addFunds(LoyaltyAmount::fromKwacha(1000), 'revenue');

        $this->assertTrue($pool->canAllocate(LoyaltyAmount::fromKwacha(500)));
        $this->assertTrue($pool->canAllocate(LoyaltyAmount::fromKwacha(700)));
        $this->assertFalse($pool->canAllocate(LoyaltyAmount::fromKwacha(701)));
    }

    public function test_reward_pool_calculate_proportional_amount_when_sufficient(): void
    {
        $pool = RewardPool::create();
        $pool->addFunds(LoyaltyAmount::fromKwacha(2000), 'revenue');

        // Available: 1400, Requested: 500, Total requested: 500
        $result = $pool->calculateProportionalAmount(
            LoyaltyAmount::fromKwacha(500),
            LoyaltyAmount::fromKwacha(500)
        );
        $this->assertEquals(500, $result->toKwacha());
    }

    public function test_reward_pool_calculate_proportional_amount_when_insufficient(): void
    {
        $pool = RewardPool::create();
        $pool->addFunds(LoyaltyAmount::fromKwacha(1000), 'revenue');

        // Available: 700, Total requested across all: 1400, My request: 500
        // Proportion: 700/1400 = 0.5, My share: 500 * 0.5 = 250
        $result = $pool->calculateProportionalAmount(
            LoyaltyAmount::fromKwacha(500),
            LoyaltyAmount::fromKwacha(1400)
        );
        $this->assertEquals(250, $result->toKwacha());
    }

    public function test_reward_pool_has_minimum_reserve(): void
    {
        $pool = RewardPool::create();
        $pool->addFunds(LoyaltyAmount::fromKwacha(1000), 'revenue');

        // Reserve is exactly 30% of total = 300, which meets the 30% requirement
        $this->assertTrue($pool->hasMinimumReserve());
    }

    // --- LgrQualification Entity ---

    public function test_lgr_qualification_create(): void
    {
        $qual = LgrQualification::create(1);
        $this->assertNull($qual->getId());
        $this->assertEquals(1, $qual->getUserId());
        $this->assertFalse($qual->isStarterPackageCompleted());
        $this->assertFalse($qual->isTrainingCompleted());
        $this->assertEquals(0, $qual->getFirstLevelMembers());
        $this->assertFalse($qual->isNetworkRequirementMet());
        $this->assertEquals(0, $qual->getActivitiesCompleted());
        $this->assertFalse($qual->isActivityRequirementMet());
        $this->assertFalse($qual->isFullyQualified());
    }

    public function test_lgr_qualification_complete_starter_package(): void
    {
        $qual = LgrQualification::create(1);
        $qual->completeStarterPackage();
        $this->assertTrue($qual->isStarterPackageCompleted());
        // Not fully qualified because other requirements not met
        $this->assertFalse($qual->isFullyQualified());
    }

    public function test_lgr_qualification_complete_training(): void
    {
        $qual = LgrQualification::create(1);
        $qual->completeTraining();
        $this->assertTrue($qual->isTrainingCompleted());
        $this->assertFalse($qual->isFullyQualified());
    }

    public function test_lgr_qualification_update_first_level_members(): void
    {
        $qual = LgrQualification::create(1);
        $qual->updateFirstLevelMembers(3);
        $this->assertEquals(3, $qual->getFirstLevelMembers());
        $this->assertTrue($qual->isNetworkRequirementMet());
    }

    public function test_lgr_qualification_update_first_level_members_below_requirement(): void
    {
        $qual = LgrQualification::create(1);
        $qual->updateFirstLevelMembers(2);
        $this->assertFalse($qual->isNetworkRequirementMet());
    }

    public function test_lgr_qualification_increment_activity(): void
    {
        $qual = LgrQualification::create(1);
        $qual->incrementActivity();
        $this->assertEquals(1, $qual->getActivitiesCompleted());
        $this->assertFalse($qual->isActivityRequirementMet());

        $qual->incrementActivity();
        $this->assertEquals(2, $qual->getActivitiesCompleted());
        $this->assertTrue($qual->isActivityRequirementMet());
    }

    public function test_lgr_qualification_fully_qualified_when_all_met(): void
    {
        $qual = LgrQualification::create(1);
        $this->assertFalse($qual->isFullyQualified());

        $qual->completeStarterPackage();
        $qual->completeTraining();
        $qual->updateFirstLevelMembers(3);
        $qual->incrementActivity();
        $qual->incrementActivity();

        $this->assertTrue($qual->isFullyQualified());
    }

    public function test_lgr_qualification_get_progress(): void
    {
        $qual = LgrQualification::create(1);
        $progress = $qual->getQualificationProgress();

        $this->assertFalse($progress['starter_package']['completed']);
        $this->assertTrue($progress['starter_package']['required']);
        $this->assertEquals(0, $progress['network']['current']);
        $this->assertEquals(3, $progress['network']['required']);
        $this->assertEquals(0, $progress['activities']['current']);
        $this->assertEquals(2, $progress['activities']['required']);
        $this->assertFalse($progress['fully_qualified']);
    }

    public function test_lgr_qualification_from_array(): void
    {
        $qual = LgrQualification::fromArray([
            'id' => 1,
            'user_id' => 5,
            'starter_package_completed' => true,
            'training_completed' => true,
            'first_level_members' => 4,
            'network_requirement_met' => true,
            'activities_completed' => 3,
            'activity_requirement_met' => true,
            'fully_qualified' => true,
        ]);

        $this->assertEquals(1, $qual->getId());
        $this->assertEquals(5, $qual->getUserId());
        $this->assertTrue($qual->isStarterPackageCompleted());
        $this->assertTrue($qual->isTrainingCompleted());
        $this->assertEquals(4, $qual->getFirstLevelMembers());
        $this->assertTrue($qual->isFullyQualified());
    }

    public function test_lgr_qualification_to_array(): void
    {
        $qual = LgrQualification::create(1);
        $qual->completeStarterPackage();
        $qual->updateFirstLevelMembers(3);

        $arr = $qual->toArray();
        $this->assertNull($arr['id']);
        $this->assertTrue($arr['starter_package_completed']);
        $this->assertEquals(3, $arr['first_level_members']);
        $this->assertFalse($arr['fully_qualified']);
        $this->assertIsArray($arr['progress']);
    }

    // --- LoyaltyGrowthCycle Entity ---

    public function test_loyalty_growth_cycle_start(): void
    {
        $startDate = new DateTimeImmutable('2026-01-01');
        $cycle = LoyaltyGrowthCycle::start(1, $startDate);

        $this->assertInstanceOf(CycleId::class, $cycle->getId());
        $this->assertEquals(1, $cycle->getUserId());
        $this->assertTrue($cycle->getStartDate() === $startDate);
        $this->assertEquals('2026-03-12', $cycle->getEndDate()->format('Y-m-d')); // +70 days
        $this->assertTrue($cycle->getStatus()->isActive());
        $this->assertEquals(0, $cycle->getActiveDays());
        $this->assertTrue($cycle->getEarnedAmount()->equals(LoyaltyAmount::zero()));
        $this->assertNull($cycle->getCompletedAt());
    }

    public function test_loyalty_growth_cycle_record_active_day(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));
        $cycle->recordActiveDay();

        $this->assertEquals(1, $cycle->getActiveDays());
        $this->assertEquals(25, $cycle->getEarnedAmount()->toKwacha());
    }

    public function test_loyalty_growth_cycle_record_multiple_days(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));
        for ($i = 0; $i < 5; $i++) {
            $cycle->recordActiveDay();
        }

        $this->assertEquals(5, $cycle->getActiveDays());
        $this->assertEquals(125, $cycle->getEarnedAmount()->toKwacha());
    }

    public function test_loyalty_growth_cycle_record_active_day_when_inactive_throws(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));
        $cycle->complete();

        $this->expectException(\DomainException::class);
        $cycle->recordActiveDay();
    }

    public function test_loyalty_growth_cycle_complete(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));
        $cycle->complete();

        $this->assertTrue($cycle->getStatus()->isCompleted());
        $this->assertInstanceOf(DateTimeImmutable::class, $cycle->getCompletedAt());
    }

    public function test_loyalty_growth_cycle_complete_when_inactive_throws(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));
        $cycle->complete();

        $this->expectException(\DomainException::class);
        $cycle->complete();
    }

    public function test_loyalty_growth_cycle_suspend(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));
        $cycle->suspend('Policy violation');

        $this->assertTrue($cycle->getStatus()->isSuspended());
    }

    public function test_loyalty_growth_cycle_terminate(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));
        $cycle->terminate('Fraud detected');

        $this->assertTrue($cycle->getStatus()->isTerminated());
    }

    public function test_loyalty_growth_cycle_can_record_activity_when_active(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));
        $this->assertTrue($cycle->canRecordActivity());
    }

    public function test_loyalty_growth_cycle_cannot_record_activity_when_completed(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));
        $cycle->complete();
        $this->assertFalse($cycle->canRecordActivity());
    }

    public function test_loyalty_growth_cycle_get_progress_percentage(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));

        $this->assertEquals(0, $cycle->getProgressPercentage());

        for ($i = 0; $i < 35; $i++) {
            $cycle->recordActiveDay();
        }

        $this->assertEquals(50, $cycle->getProgressPercentage());
    }

    public function test_loyalty_growth_cycle_get_remaining_days(): void
    {
        $cycle = LoyaltyGrowthCycle::start(1, new DateTimeImmutable('now'));
        $this->assertGreaterThan(0, $cycle->getRemainingDays());
    }

    public function test_loyalty_growth_cycle_constants(): void
    {
        $this->assertEquals(70, LoyaltyGrowthCycle::getCycleDurationDays());
        $this->assertEquals(25, LoyaltyGrowthCycle::getDailyRate());
        $this->assertEquals(1750, LoyaltyGrowthCycle::getMaxTotal());
    }

    // --- LoyaltyActivity Entity ---

    public function test_loyalty_activity_record(): void
    {
        $activity = LoyaltyActivity::record(
            userId: 1,
            cycleId: 5,
            type: ActivityType::LEARNING_MODULE,
            description: 'Completed Business Fundamentals',
        );

        $this->assertEquals(0, $activity->getId()); // Will be set by repo
        $this->assertEquals(1, $activity->getUserId());
        $this->assertEquals(5, $activity->getCycleId());
        $this->assertEquals(ActivityType::LEARNING_MODULE, $activity->getType());
        $this->assertEquals('Completed Business Fundamentals', $activity->getDescription());
        $this->assertInstanceOf(DateTimeImmutable::class, $activity->getPerformedAt());
        $this->assertFalse($activity->isVerified());
    }

    public function test_loyalty_activity_verify(): void
    {
        $activity = LoyaltyActivity::record(1, 1, ActivityType::QUIZ_COMPLETION, 'Quiz passed');
        $this->assertFalse($activity->isVerified());

        $activity->verify();
        $this->assertTrue($activity->isVerified());
    }

    public function test_loyalty_activity_all_types_record(): void
    {
        foreach (ActivityType::cases() as $type) {
            $activity = LoyaltyActivity::record(1, 1, $type, 'Test ' . $type->value);
            $this->assertEquals($type, $activity->getType());
        }
    }
}
