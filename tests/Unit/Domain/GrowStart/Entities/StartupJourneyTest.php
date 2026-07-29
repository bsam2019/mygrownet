<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\Entities;

use App\Domain\GrowStart\Entities\StartupJourney;
use App\Domain\GrowStart\ValueObjects\JourneyId;
use App\Domain\GrowStart\ValueObjects\JourneyStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class StartupJourneyTest extends TestCase
{
    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = new DateTimeImmutable();
    }

    public function test_can_create_journey(): void
    {
        $journey = StartupJourney::create(
            userId: 42,
            industryId: 5,
            countryId: 1,
            businessName: 'Tech Startup',
            initialStageId: 1,
            businessDescription: 'A tech company',
        );

        $this->assertInstanceOf(JourneyId::class, $journey->getId());
        $this->assertEquals(42, $journey->getUserId());
        $this->assertEquals(5, $journey->getIndustryId());
        $this->assertEquals(1, $journey->getCountryId());
        $this->assertEquals('Tech Startup', $journey->getBusinessName());
        $this->assertEquals('A tech company', $journey->getBusinessDescription());
        $this->assertEquals(1, $journey->getCurrentStageId());
        $this->assertTrue($journey->getStatus()->isActive());
        $this->assertFalse($journey->isPremium());
        $this->assertNull($journey->getProvince());
        $this->assertNull($journey->getCity());
    }

    public function test_create_with_optional_fields(): void
    {
        $targetDate = new DateTimeImmutable('2026-12-31');
        $journey = StartupJourney::create(
            userId: 1,
            industryId: 3,
            countryId: 2,
            businessName: 'My Biz',
            initialStageId: 2,
            businessDescription: null,
            targetLaunchDate: $targetDate,
            province: 'Lusaka',
            city: 'Lusaka',
        );

        $this->assertNull($journey->getBusinessDescription());
        $this->assertEquals($targetDate, $journey->getTargetLaunchDate());
        $this->assertEquals('Lusaka', $journey->getProvince());
        $this->assertEquals('Lusaka', $journey->getCity());
    }

    public function test_can_reconstitute_journey(): void
    {
        $startedAt = new DateTimeImmutable('2026-01-15');
        $createdAt = new DateTimeImmutable('2026-01-15');
        $updatedAt = new DateTimeImmutable('2026-03-01');

        $journey = StartupJourney::reconstitute(
            id: JourneyId::fromInt(10),
            userId: 42,
            industryId: 5,
            countryId: 1,
            businessName: 'Recon Biz',
            businessDescription: 'Reconstituted',
            currentStageId: 3,
            startedAt: $startedAt,
            targetLaunchDate: null,
            status: JourneyStatus::paused(),
            isPremium: true,
            province: 'Copperbelt',
            city: 'Ndola',
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertEquals(10, $journey->id());
        $this->assertTrue($journey->getStatus()->isPaused());
        $this->assertTrue($journey->isPremium());
        $this->assertEquals($startedAt, $journey->getStartedAt());
    }

    public function test_update_business_info(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Old Name', 1);

        $journey->updateBusinessInfo('New Name', 'New description', 'Central', 'Kabwe');

        $this->assertEquals('New Name', $journey->getBusinessName());
        $this->assertEquals('New description', $journey->getBusinessDescription());
        $this->assertEquals('Central', $journey->getProvince());
        $this->assertEquals('Kabwe', $journey->getCity());
    }

    public function test_set_target_launch_date(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $this->assertNull($journey->getTargetLaunchDate());

        $date = new DateTimeImmutable('2026-12-31');
        $journey->setTargetLaunchDate($date);
        $this->assertEquals($date, $journey->getTargetLaunchDate());
    }

    public function test_advance_to_stage(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $journey->advanceToStage(2);
        $this->assertEquals(2, $journey->getCurrentStageId());
    }

    public function test_pause_transitions_from_active(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $journey->pause();
        $this->assertTrue($journey->getStatus()->isPaused());
    }

    public function test_can_resume_from_paused(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $journey->pause();
        $journey->resume();
        $this->assertTrue($journey->getStatus()->isActive());
    }

    public function test_cannot_resume_from_completed(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $journey->complete();

        $this->expectException(\DomainException::class);
        $journey->resume();
    }

    public function test_can_complete_from_active(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $journey->complete();
        $this->assertTrue($journey->getStatus()->isCompleted());
    }

    public function test_cannot_complete_from_archived(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $journey->archive();

        $this->expectException(\DomainException::class);
        $journey->complete();
    }

    public function test_can_archive_from_active(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $journey->archive();
        $this->assertTrue($journey->getStatus()->isArchived());
    }

    public function test_can_archive_from_completed(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $journey->complete();
        $journey->archive();
        $this->assertTrue($journey->getStatus()->isArchived());
    }

    public function test_upgrade_to_premium(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $this->assertFalse($journey->isPremium());
        $journey->upgradeToPremium();
        $this->assertTrue($journey->isPremium());
    }

    public function test_get_days_active_returns_non_negative(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $this->assertGreaterThanOrEqual(0, $journey->getDaysActive());
    }

    public function test_is_on_track_returns_true_without_target_date(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $this->assertTrue($journey->isOnTrack());
    }

    public function test_is_on_track_returns_true_when_before_target(): void
    {
        $future = new DateTimeImmutable('+1 year');
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1, targetLaunchDate: $future);
        $this->assertTrue($journey->isOnTrack());
    }

    public function test_to_array_returns_expected_keys(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'MyBiz', 1);
        $result = $journey->toArray();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('business_name', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('days_active', $result);
        $this->assertArrayHasKey('is_on_track', $result);
        $this->assertEquals('MyBiz', $result['business_name']);
        $this->assertEquals('active', $result['status']);
    }

    public function test_cannot_pause_from_completed(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $journey->complete();

        $this->expectException(\DomainException::class);
        $journey->pause();
    }

    public function test_cannot_pause_from_archived(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Biz', 1);
        $journey->archive();

        $this->expectException(\DomainException::class);
        $journey->pause();
    }
}
