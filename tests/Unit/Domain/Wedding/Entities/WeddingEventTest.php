<?php

namespace Tests\Unit\Domain\Wedding\Entities;

use App\Domain\Wedding\Entities\WeddingEvent;
use App\Domain\Wedding\ValueObjects\WeddingBudget;
use App\Domain\Wedding\ValueObjects\WeddingStatus;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WeddingEventTest extends TestCase
{
    public function test_create(): void
    {
        $date = Carbon::tomorrow();
        $budget = WeddingBudget::fromAmount(50000);
        $event = WeddingEvent::create(
            userId: 1,
            partnerName: 'Jane Doe',
            weddingDate: $date,
            budget: $budget,
            guestCount: 100,
            slug: 'john-and-jane',
            accessCode: 'ABC123'
        );

        $this->assertNull($event->getId());
        $this->assertEquals(1, $event->getUserId());
        $this->assertEquals('Jane Doe', $event->getPartnerName());
        $this->assertNull($event->getPartnerEmail());
        $this->assertNull($event->getPartnerPhone());
        $this->assertSame($date, $event->getWeddingDate());
        $this->assertNull($event->getVenueName());
        $this->assertNull($event->getVenueLocation());
        $this->assertTrue($event->getBudget()->equals($budget));
        $this->assertEquals(100, $event->getGuestCount());
        $this->assertTrue($event->getStatus()->isPlanning());
        $this->assertNull($event->getNotes());
        $this->assertNull($event->getPreferences());
        $this->assertEquals('john-and-jane', $event->getSlug());
        $this->assertEquals('ABC123', $event->getAccessCode());
        $this->assertNotNull($event->getCreatedAt());
        $this->assertNotNull($event->getUpdatedAt());
    }

    public function test_update_venue(): void
    {
        $event = $this->createPlanningEvent();
        $event->updateVenue('Grand Hall', 'Lusaka');

        $this->assertEquals('Grand Hall', $event->getVenueName());
        $this->assertEquals('Lusaka', $event->getVenueLocation());
    }

    public function test_update_budget(): void
    {
        $event = $this->createPlanningEvent();
        $newBudget = WeddingBudget::fromAmount(100000);
        $event->updateBudget($newBudget);

        $this->assertTrue($event->getBudget()->equals($newBudget));
    }

    public function test_update_guest_count(): void
    {
        $event = $this->createPlanningEvent();
        $event->updateGuestCount(150);

        $this->assertEquals(150, $event->getGuestCount());
    }

    public function test_update_guest_count_negative_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $event = $this->createPlanningEvent();
        $event->updateGuestCount(-1);
    }

    public function test_confirm_requires_venue(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Wedding cannot be confirmed without venue and date');
        $event = WeddingEvent::create(
            userId: 1,
            partnerName: 'Jane',
            weddingDate: Carbon::tomorrow(),
            budget: WeddingBudget::fromAmount(50000)
        );
        $event->confirm();
    }

    public function test_confirm_succeeds_with_venue(): void
    {
        $event = $this->createPlanningEvent();
        $event->updateVenue('Grand Hall', 'Lusaka');
        $event->confirm();

        $this->assertTrue($event->getStatus()->isConfirmed());
    }

    public function test_complete_requires_confirmed(): void
    {
        $this->expectException(\DomainException::class);
        $event = $this->createPlanningEvent();
        $event->complete();
    }

    public function test_complete_succeeds_when_confirmed(): void
    {
        $event = $this->createPlanningEvent();
        $event->updateVenue('Grand Hall', 'Lusaka');
        $event->confirm();
        $event->complete();

        $this->assertTrue($event->getStatus()->isCompleted());
    }

    public function test_cancel_planning_event(): void
    {
        $event = $this->createPlanningEvent();
        $event->cancel();

        $this->assertTrue($event->getStatus()->isCancelled());
    }

    public function test_cancel_completed_throws(): void
    {
        $this->expectException(\DomainException::class);
        $event = $this->createPlanningEvent();
        $event->updateVenue('Grand Hall', 'Lusaka');
        $event->confirm();
        $event->complete();
        $event->cancel();
    }

    public function test_is_upcoming_with_future_date(): void
    {
        $event = $this->createPlanningEvent();
        $this->assertTrue($event->isUpcoming());

        $event->updateVenue('Grand Hall', 'Lusaka');
        $event->confirm();
        $this->assertTrue($event->isUpcoming());

        $event->cancel();
        $this->assertFalse($event->isUpcoming());
    }

    public function test_regenerate_access_code(): void
    {
        $event = $this->createPlanningEvent();
        $this->assertEquals('ABC123', $event->getAccessCode());

        $event->regenerateAccessCode('NEWCODE');
        $this->assertEquals('NEWCODE', $event->getAccessCode());
    }

    private function createPlanningEvent(): WeddingEvent
    {
        return WeddingEvent::create(
            userId: 1,
            partnerName: 'Jane Doe',
            weddingDate: Carbon::tomorrow(),
            budget: WeddingBudget::fromAmount(50000),
            guestCount: 100,
            slug: 'john-and-jane',
            accessCode: 'ABC123'
        );
    }
}
