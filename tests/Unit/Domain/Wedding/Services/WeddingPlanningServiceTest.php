<?php

namespace Tests\Unit\Domain\Wedding\Services;

use App\Domain\Wedding\Entities\WeddingEvent;
use App\Domain\Wedding\Repositories\WeddingEventRepositoryInterface;
use App\Domain\Wedding\Services\WeddingPlanningService;
use App\Domain\Wedding\ValueObjects\WeddingBudget;
use App\Domain\Wedding\ValueObjects\WeddingStatus;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WeddingPlanningServiceTest extends TestCase
{
    private WeddingEventRepositoryInterface $repository;
    private WeddingPlanningService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createStub(WeddingEventRepositoryInterface::class);
        $this->service = new WeddingPlanningService($this->repository);
    }

    public function test_create_wedding_event(): void
    {
        $this->repository = $this->createMock(WeddingEventRepositoryInterface::class);
        $this->repository
            ->expects($this->once())
            ->method('findUserActiveEvent')
            ->with(1)
            ->willReturn(null);

        $savedEvent = WeddingEvent::create(
            userId: 1,
            partnerName: 'Jane Doe',
            weddingDate: Carbon::tomorrow(),
            budget: WeddingBudget::fromAmount(50000),
            guestCount: 100
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturn($savedEvent);

        $service = new WeddingPlanningService($this->repository);
        $result = $service->createWeddingEvent(1, 'Jane Doe', Carbon::tomorrow(), 50000, 100);

        $this->assertSame($savedEvent, $result);
    }

    public function test_create_wedding_event_with_past_date_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $pastDate = Carbon::yesterday();
        $this->service->createWeddingEvent(1, 'Jane Doe', $pastDate, 50000);
    }

    public function test_create_wedding_event_when_user_has_active_event_throws(): void
    {
        $repository = $this->createMock(WeddingEventRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findUserActiveEvent')
            ->with(1)
            ->willReturn($this->createStub(WeddingEvent::class));

        $repository
            ->expects($this->never())
            ->method('save');

        $service = new WeddingPlanningService($repository);

        $this->expectException(\DomainException::class);
        $service->createWeddingEvent(1, 'Jane Doe', Carbon::tomorrow(), 50000);
    }

    public function test_generate_budget_breakdown(): void
    {
        $budget = WeddingBudget::fromAmount(100000);
        $breakdown = $this->service->generateBudgetBreakdown($budget);

        $this->assertCount(8, $breakdown);
        $this->assertEquals(40, $breakdown['venue']['percentage']);
        $this->assertEquals(40000, $breakdown['venue']['amount']);
        $this->assertEquals(15, $breakdown['photography']['percentage']);
        $this->assertEquals(15000, $breakdown['photography']['amount']);
        $this->assertEquals(10, $breakdown['decoration']['percentage']);
        $this->assertEquals(10000, $breakdown['decoration']['amount']);
        $this->assertEquals(8, $breakdown['music']['percentage']);
        $this->assertEquals(8000, $breakdown['music']['amount']);
        $this->assertEquals(5, $breakdown['transport']['percentage']);
        $this->assertEquals(5000, $breakdown['transport']['amount']);
        $this->assertEquals(5, $breakdown['makeup']['percentage']);
        $this->assertEquals(5000, $breakdown['makeup']['amount']);
        $this->assertEquals(10, $breakdown['attire']['percentage']);
        $this->assertEquals(10000, $breakdown['attire']['amount']);
        $this->assertEquals(7, $breakdown['miscellaneous']['percentage']);
        $this->assertEqualsWithDelta(7000, $breakdown['miscellaneous']['amount'], 0.001);
    }

    public function test_generate_wedding_timeline(): void
    {
        $weddingDate = Carbon::parse('2027-06-15');
        $timeline = $this->service->generateWeddingTimeline($weddingDate);

        $this->assertCount(6, $timeline);
        $this->assertEquals('12 months before', $timeline[0]['period']);
        $this->assertEquals('1 week before', $timeline[5]['period']);
        $this->assertEquals('2027-06-08', $timeline[5]['date']->format('Y-m-d'));
    }

    public function test_calculate_recommended_budget_standard(): void
    {
        $budget = $this->service->calculateRecommendedBudget(100, 'standard');
        $this->assertEquals(145000, $budget->getAmount());
    }

    public function test_calculate_recommended_budget_budget(): void
    {
        $budget = $this->service->calculateRecommendedBudget(50, 'budget');
        $this->assertEquals(55000, $budget->getAmount());
    }

    public function test_calculate_recommended_budget_premium(): void
    {
        $budget = $this->service->calculateRecommendedBudget(100, 'premium');
        $this->assertEquals(250000, $budget->getAmount());
    }

    public function test_calculate_recommended_budget_luxury(): void
    {
        $budget = $this->service->calculateRecommendedBudget(100, 'luxury');
        $this->assertEquals(450000, $budget->getAmount());
    }

    public function test_calculate_recommended_budget_defaults_to_standard(): void
    {
        $budget = $this->service->calculateRecommendedBudget(100, 'unknown');
        $this->assertEquals(145000, $budget->getAmount());
    }

    public function test_get_wedding_progress(): void
    {
        $event = WeddingEvent::create(
            userId: 1,
            partnerName: 'Jane',
            weddingDate: Carbon::tomorrow(),
            budget: WeddingBudget::fromAmount(50000),
            guestCount: 100
        );

        $progress = $this->service->getWeddingProgress($event);

        $this->assertEquals(20, $progress['total_tasks']);
        $this->assertEquals(4, $progress['completed_tasks']);
        $this->assertEquals(20.0, $progress['progress_percentage']);
        $this->assertIsInt($progress['days_until_wedding']);
        $this->assertIsBool($progress['is_on_track']);
    }

    public function test_get_wedding_progress_with_venue_and_confirmed(): void
    {
        $event = WeddingEvent::create(
            userId: 1,
            partnerName: 'Jane',
            weddingDate: Carbon::tomorrow(),
            budget: WeddingBudget::fromAmount(50000),
            guestCount: 100
        );
        $event->updateVenue('Grand Hall', 'Lusaka');
        $event->confirm();

        $progress = $this->service->getWeddingProgress($event);

        $this->assertEquals(12, $progress['completed_tasks']);
        $this->assertEquals(60.0, $progress['progress_percentage']);
    }
}
