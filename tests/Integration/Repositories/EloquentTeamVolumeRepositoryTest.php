<?php

namespace Tests\Integration\Repositories;

use Tests\TestCase;
use App\Infrastructure\Persistence\Repositories\EloquentTeamVolumeRepository;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;
use App\Models\User;
use App\Models\TeamVolume;
use App\Models\UserNetwork;
use App\Models\ReferralCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DateTimeImmutable;

class EloquentTeamVolumeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentTeamVolumeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentTeamVolumeRepository();
    }

    public function test_can_get_current_team_volume()
    {
        $user = User::factory()->create();

        TeamVolume::factory()->create([
            'user_id' => $user->id,
            'team_volume' => 15000.00,
            'created_at' => now()->subDays(5)
        ]);

        TeamVolume::factory()->create([
            'user_id' => $user->id,
            'team_volume' => 20000.00,
            'created_at' => now()->subDays(1)
        ]);

        $currentVolume = $this->repository->getCurrentTeamVolume(UserId::fromInt($user->id));

        $this->assertNotNull($currentVolume);
        $this->assertEquals(20000.00, $currentVolume->value());
    }

    public function test_can_get_team_volume_for_specific_period()
    {
        $user = User::factory()->create();
        $startDate = new DateTimeImmutable('2024-01-01');
        $endDate = new DateTimeImmutable('2024-01-31');

        TeamVolume::factory()->create([
            'user_id' => $user->id,
            'team_volume' => 15000.00,
            'period_start' => '2024-01-01',
            'period_end' => '2024-01-31'
        ]);

        TeamVolume::factory()->create([
            'user_id' => $user->id,
            'team_volume' => 25000.00,
            'period_start' => '2024-02-01',
            'period_end' => '2024-02-28'
        ]);

        $periodVolume = $this->repository->getTeamVolumeForPeriod(
            UserId::fromInt($user->id),
            $startDate,
            $endDate
        );

        $this->assertNotNull($periodVolume);
        $this->assertEquals(15000.00, $periodVolume->value());
    }

    public function test_can_calculate_team_volume_rollup()
    {
        $rootUser = User::factory()->create();
        $level1User = User::factory()->create();
        $level2User = User::factory()->create();

        // Create network structure
        UserNetwork::create([
            'user_id' => $level1User->id,
            'referrer_id' => $rootUser->id,
            'level' => 1,
            'path' => $level1User->id
        ]);

        UserNetwork::create([
            'user_id' => $level2User->id,
            'referrer_id' => $rootUser->id,
            'level' => 2,
            'path' => $level1User->id . '.' . $level2User->id
        ]);

        // Create commissions representing package purchases
        ReferralCommission::factory()->create([
            'referrer_id' => $rootUser->id,
            'referred_id' => $level1User->id,
            'package_amount' => 1000.00,
            'status' => 'paid'
        ]);

        ReferralCommission::factory()->create([
            'referrer_id' => $rootUser->id,
            'referred_id' => $level2User->id,
            'package_amount' => 500.00,
            'status' => 'paid'
        ]);

        // Personal purchase
        ReferralCommission::factory()->create([
            'referred_id' => $rootUser->id,
            'package_amount' => 1500.00,
            'status' => 'paid'
        ]);

        $teamVolume = $this->repository->calculateTeamVolumeRollup(UserId::fromInt($rootUser->id));

        $this->assertEquals(3000.00, $teamVolume->value()); // 1000 + 500 + 1500
    }

    public function test_can_get_network_volume_aggregation()
    {
        $rootUser = User::factory()->create();
        $level1Users = User::factory()->count(3)->create();
        $level2Users = User::factory()->count(2)->create();

        // Create level 1 network
        foreach ($level1Users as $index => $user) {
            UserNetwork::create([
                'user_id' => $user->id,
                'referrer_id' => $rootUser->id,
                'level' => 1,
                'path' => $user->id
            ]);

            ReferralCommission::factory()->create([
                'referrer_id' => $rootUser->id,
                'referred_id' => $user->id,
                'package_amount' => 1000.00,
                'status' => 'paid'
            ]);
        }

        // Create level 2 network
        foreach ($level2Users as $index => $user) {
            UserNetwork::create([
                'user_id' => $user->id,
                'referrer_id' => $rootUser->id,
                'level' => 2,
                'path' => $level1Users[0]->id . '.' . $user->id
            ]);

            ReferralCommission::factory()->create([
                'referrer_id' => $rootUser->id,
                'referred_id' => $user->id,
                'package_amount' => 500.00,
                'status' => 'paid'
            ]);
        }

        $aggregation = $this->repository->getNetworkVolumeAggregation(
            UserId::fromInt($rootUser->id),
            5
        );

        $this->assertCount(2, $aggregation);
        
        // Level 1 aggregation
        $this->assertEquals(1, $aggregation[0]['level']);
        $this->assertEquals(3, $aggregation[0]['member_count']);
        $this->assertEquals(3000.00, $aggregation[0]['level_volume']);
        $this->assertEquals(1000.00, $aggregation[0]['avg_package_size']);
        
        // Level 2 aggregation
        $this->assertEquals(2, $aggregation[1]['level']);
        $this->assertEquals(2, $aggregation[1]['member_count']);
        $this->assertEquals(1000.00, $aggregation[1]['level_volume']);
        $this->assertEquals(500.00, $aggregation[1]['avg_package_size']);
    }

    public function test_can_update_team_volume()
    {
        $user = User::factory()->create();
        $startDate = new DateTimeImmutable('2024-01-01');
        $endDate = new DateTimeImmutable('2024-01-31');

        $this->repository->updateTeamVolume(
            UserId::fromInt($user->id),
            TeamVolumeAmount::fromFloat(5000.00),
            TeamVolumeAmount::fromFloat(15000.00),
            10,
            $startDate,
            $endDate
        );

        $this->assertDatabaseHas('team_volumes', [
            'user_id' => $user->id,
            'personal_volume' => 5000.00,
            'team_volume' => 15000.00,
            'active_referrals_count' => 10,
            'period_start' => '2024-01-01',
            'period_end' => '2024-01-31'
        ]);
    }

    public function test_can_get_users_eligible_for_performance_bonuses()
    {
        $user1 = User::factory()->create(['name' => 'High Performer']);
        $user2 = User::factory()->create(['name' => 'Medium Performer']);
        $user3 = User::factory()->create(['name' => 'Low Performer']);

        TeamVolume::factory()->create([
            'user_id' => $user1->id,
            'team_volume' => 50000.00
        ]);

        TeamVolume::factory()->create([
            'user_id' => $user2->id,
            'team_volume' => 25000.00
        ]);

        TeamVolume::factory()->create([
            'user_id' => $user3->id,
            'team_volume' => 5000.00
        ]);

        $eligibleUsers = $this->repository->getUsersEligibleForPerformanceBonuses(
            TeamVolumeAmount::fromFloat(10000.00)
        );

        $this->assertCount(2, $eligibleUsers);
        $this->assertEquals($user1->id, $eligibleUsers[0]['user_id']);
        $this->assertEquals($user2->id, $eligibleUsers[1]['user_id']);
        $this->assertGreaterThan(0, $eligibleUsers[0]['performance_bonus']);
        $this->assertGreaterThan(0, $eligibleUsers[1]['performance_bonus']);
    }

    public function test_can_get_team_volume_stats_for_period()
    {
        $startDate = new DateTimeImmutable('2024-01-01');
        $endDate = new DateTimeImmutable('2024-01-31');

        $users = User::factory()->count(5)->create();

        foreach ($users as $user) {
            TeamVolume::factory()->create([
                'user_id' => $user->id,
                'personal_volume' => 2000.00,
                'team_volume' => 10000.00,
                'active_referrals_count' => 5,
                'period_start' => '2024-01-15'
            ]);
        }

        $stats = $this->repository->getTeamVolumeStats($startDate, $endDate);

        $this->assertEquals(5, $stats['total_users']);
        $this->assertEquals(10000.00, $stats['total_personal_volume']);
        $this->assertEquals(50000.00, $stats['total_team_volume']);
        $this->assertEquals(10000.00, $stats['avg_team_volume']);
        $this->assertEquals(10000.00, $stats['max_team_volume']);
        $this->assertEquals(25, $stats['total_active_referrals']);
    }

    public function test_can_get_team_volume_history()
    {
        $user = User::factory()->create();

        // Create 6 months of history
        for ($i = 0; $i < 6; $i++) {
            TeamVolume::factory()->create([
                'user_id' => $user->id,
                'team_volume' => 10000.00 + ($i * 2000),
                'period_start' => now()->subMonths($i)->startOfMonth(),
                'period_end' => now()->subMonths($i)->endOfMonth(),
                'created_at' => now()->subMonths($i)
            ]);
        }

        $history = $this->repository->getTeamVolumeHistory(UserId::fromInt($user->id), 12);

        $this->assertCount(6, $history);
        $this->assertArrayHasKey('team_volume', $history[0]);
        $this->assertArrayHasKey('performance_bonus', $history[0]);
    }

    public function test_can_calculate_team_depth()
    {
        $rootUser = User::factory()->create();
        $level1User = User::factory()->create();
        $level2User = User::factory()->create();
        $level3User = User::factory()->create();

        UserNetwork::create([
            'user_id' => $level1User->id,
            'referrer_id' => $rootUser->id,
            'level' => 1
        ]);

        UserNetwork::create([
            'user_id' => $level2User->id,
            'referrer_id' => $rootUser->id,
            'level' => 2
        ]);

        UserNetwork::create([
            'user_id' => $level3User->id,
            'referrer_id' => $rootUser->id,
            'level' => 3
        ]);

        $depth = $this->repository->calculateTeamDepth(UserId::fromInt($rootUser->id));

        $this->assertEquals(3, $depth);
    }

    public function test_can_get_top_performers_by_team_volume()
    {
        $users = User::factory()->count(10)->create();

        foreach ($users as $index => $user) {
            TeamVolume::factory()->create([
                'user_id' => $user->id,
                'team_volume' => 10000.00 + ($index * 5000),
                'active_referrals_count' => 5 + $index
            ]);
        }

        $topPerformers = $this->repository->getTopPerformersByTeamVolume(5);

        $this->assertCount(5, $topPerformers);
        $this->assertEquals(1, $topPerformers[0]['rank']);
        $this->assertEquals(55000.00, $topPerformers[0]['team_volume']); // Highest volume
        $this->assertGreaterThan($topPerformers[1]['team_volume'], $topPerformers[0]['team_volume']);
    }

    public function test_can_check_tier_upgrade_qualification()
    {
        $user = User::factory()->create();

        TeamVolume::factory()->create([
            'user_id' => $user->id,
            'team_volume' => 25000.00,
            'active_referrals_count' => 15
        ]);

        $qualifies = $this->repository->checkTierUpgradeQualification(
            UserId::fromInt($user->id),
            TeamVolumeAmount::fromFloat(20000.00),
            10
        );

        $this->assertTrue($qualifies);

        $doesNotQualify = $this->repository->checkTierUpgradeQualification(
            UserId::fromInt($user->id),
            TeamVolumeAmount::fromFloat(30000.00),
            20
        );

        $this->assertFalse($doesNotQualify);
    }

    public function test_repository_performance_with_large_network()
    {
        $rootUser = User::factory()->create();
        $networkUsers = User::factory()->count(500)->create();

        // Create large network structure
        foreach ($networkUsers as $index => $user) {
            UserNetwork::create([
                'user_id' => $user->id,
                'referrer_id' => $rootUser->id,
                'level' => ($index % 5) + 1,
                'path' => $user->id
            ]);

            ReferralCommission::factory()->create([
                'referrer_id' => $rootUser->id,
                'referred_id' => $user->id,
                'package_amount' => rand(500, 2000),
                'status' => 'paid'
            ]);
        }

        $startTime = microtime(true);
        
        $teamVolume = $this->repository->calculateTeamVolumeRollup(UserId::fromInt($rootUser->id));
        $aggregation = $this->repository->getNetworkVolumeAggregation(UserId::fromInt($rootUser->id));
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertGreaterThan(0, $teamVolume->value());
        $this->assertNotEmpty($aggregation);
        $this->assertLessThan(2.0, $executionTime, 'Team volume calculations should complete within 2 seconds');
    }
}