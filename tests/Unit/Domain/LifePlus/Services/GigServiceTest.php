<?php

namespace Tests\Unit\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusGig;
use App\Domain\LifePlus\Repositories\GigRepositoryInterface;
use App\Domain\LifePlus\Services\GigService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GigServiceTest extends TestCase
{
    private GigRepositoryInterface $gigRepo;
    private GigService $service;

    protected function setUp(): void
    {
        $this->gigRepo = $this->createMock(GigRepositoryInterface::class);
        $this->service = new GigService($this->gigRepo);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    #[Test]
    public function getGigs_returns_mapped_gigs()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Clean yard', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findOpen')->with([])->willReturn([$gig]);

        $result = $this->service->getGigs();

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('Clean yard', $result[0]['title']);
        $this->assertSame('open', $result[0]['status']);
    }

    #[Test]
    public function getGig_returns_null_on_not_found()
    {
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertNull($this->service->getGig(1));
    }

    #[Test]
    public function getGig_returns_mapped_gig()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Babysit', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $result = $this->service->getGig(1);

        $this->assertSame('Babysit', $result['title']);
    }

    #[Test]
    public function getMyGigs_returns_mapped_gigs_for_user()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'My gig', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findByUser')->with(42)->willReturn([$gig]);

        $result = $this->service->getMyGigs(42);

        $this->assertCount(1, $result);
        $this->assertSame('My gig', $result[0]['title']);
    }

    #[Test]
    public function createGig_saves_and_returns_mapped()
    {
        $saved = LifePlusGig::reconstitute(['id' => 10, 'user_id' => 42, 'title' => 'New gig', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->createGig(42, ['title' => 'New gig']);

        $this->assertSame(10, $result['id']);
        $this->assertSame('New gig', $result['title']);
        $this->assertSame('open', $result['status']);
    }

    #[Test]
    public function updateGig_returns_null_when_not_found()
    {
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertNull($this->service->updateGig(1, 42, ['title' => 'Updated']));
    }

    #[Test]
    public function updateGig_returns_null_on_user_mismatch()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 99, 'title' => 'Other', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $this->assertNull($this->service->updateGig(1, 42, ['title' => 'Updated']));
    }

    #[Test]
    public function updateGig_merges_and_saves()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Old', 'status' => 'open']);
        $updated = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Updated', 'status' => 'open']);

        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);
        $this->gigRepo->expects($this->once())->method('save')->willReturn($updated);

        $result = $this->service->updateGig(1, 42, ['title' => 'Updated']);

        $this->assertSame('Updated', $result['title']);
    }

    #[Test]
    public function deleteGig_returns_true_on_success()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Delete', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);
        $this->gigRepo->expects($this->once())->method('delete')->with(1)->willReturn(true);

        $this->assertTrue($this->service->deleteGig(1, 42));
    }

    #[Test]
    public function deleteGig_returns_false_on_user_mismatch()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 99, 'title' => 'Other', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $this->assertFalse($this->service->deleteGig(1, 42));
    }

    #[Test]
    public function applyForGig_returns_null_when_not_found()
    {
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertNull($this->service->applyForGig(1, 42));
    }

    #[Test]
    public function applyForGig_returns_null_when_status_not_open()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 10, 'title' => 'Assigned gig', 'status' => 'assigned']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $this->assertNull($this->service->applyForGig(1, 42));
    }

    #[Test]
    public function applyForGig_returns_null_when_own_gig()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'My gig', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $this->assertNull($this->service->applyForGig(1, 42));
    }

    #[Test]
    public function applyForGig_returns_null_when_already_applied()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 10, 'title' => 'Gig', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $applicationMock = \Mockery::mock('alias:App\Infrastructure\Persistence\Eloquent\LifePlusGigApplicationModel');
        $queryMock = \Mockery::mock('stdClass');
        $queryMock->shouldReceive('where')->with('gig_id', 1)->andReturnSelf();
        $queryMock->shouldReceive('where')->with('user_id', 42)->andReturnSelf();
        $queryMock->shouldReceive('first')->andReturn((object) ['id' => 5]);
        $applicationMock->shouldReceive('where')->andReturn($queryMock);

        $this->assertNull($this->service->applyForGig(1, 42));
    }

    #[Test]
    public function applyForGig_creates_application()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 10, 'title' => 'Gig', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $applicationMock = \Mockery::mock('alias:App\Infrastructure\Persistence\Eloquent\LifePlusGigApplicationModel');
        $queryMock = \Mockery::mock('stdClass');
        $queryMock->shouldReceive('where')->with('gig_id', 1)->andReturnSelf();
        $queryMock->shouldReceive('where')->with('user_id', 42)->andReturnSelf();
        $queryMock->shouldReceive('first')->andReturn(null);
        $applicationMock->shouldReceive('where')->andReturn($queryMock);
        $applicationMock->shouldReceive('create')->andReturn(true);

        $result = $this->service->applyForGig(1, 42, 'I can help');

        $this->assertSame('Application submitted successfully', $result['message']);
    }

    #[Test]
    public function assignGig_returns_null_when_not_owner()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 99, 'title' => 'Gig', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $this->assertNull($this->service->assignGig(1, 42, 55));
    }

    #[Test]
    public function assignGig_returns_null_when_not_open()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Gig', 'status' => 'assigned']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $this->assertNull($this->service->assignGig(1, 42, 55));
    }

    #[Test]
    public function assignGig_updates_status_and_applications()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Gig', 'status' => 'open']);
        $updated = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Gig', 'status' => 'assigned', 'assigned_to' => 55]);

        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);
        $this->gigRepo->expects($this->once())->method('save')->willReturn($updated);

        $appMock = \Mockery::mock('alias:App\Infrastructure\Persistence\Eloquent\LifePlusGigApplicationModel');
        $acceptedQuery = \Mockery::mock('stdClass');
        $acceptedQuery->shouldReceive('where')->with('gig_id', 1)->andReturnSelf();
        $acceptedQuery->shouldReceive('where')->with('user_id', 55)->andReturnSelf();
        $acceptedQuery->shouldReceive('update')->with(['status' => 'accepted'])->once();

        $rejectedQuery = \Mockery::mock('stdClass');
        $rejectedQuery->shouldReceive('where')->with('gig_id', 1)->andReturnSelf();
        $rejectedQuery->shouldReceive('where')->with('user_id', '!=', 55)->andReturnSelf();
        $rejectedQuery->shouldReceive('update')->with(['status' => 'rejected'])->once();

        $appMock->shouldReceive('where')->andReturn($acceptedQuery, $rejectedQuery);

        $result = $this->service->assignGig(1, 42, 55);

        $this->assertSame('assigned', $result['status']);
    }

    #[Test]
    public function completeGig_returns_null_when_not_found()
    {
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertNull($this->service->completeGig(1, 42));
    }

    #[Test]
    public function completeGig_returns_null_when_not_assigned()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Gig', 'status' => 'open']);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $this->assertNull($this->service->completeGig(1, 42));
    }

    #[Test]
    public function completeGig_returns_null_when_not_owner_or_assignee()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Gig', 'status' => 'assigned', 'assigned_to' => 55]);
        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);

        $this->assertNull($this->service->completeGig(1, 99));
    }

    #[Test]
    public function completeGig_updates_status_to_completed()
    {
        $gig = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Gig', 'status' => 'assigned', 'assigned_to' => 55]);
        $completed = LifePlusGig::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Gig', 'status' => 'completed', 'assigned_to' => 55]);

        $this->gigRepo->expects($this->once())->method('findById')->with(1)->willReturn($gig);
        $this->gigRepo->expects($this->once())->method('save')->willReturn($completed);

        $result = $this->service->completeGig(1, 42);

        $this->assertSame('completed', $result['status']);
    }

    #[Test]
    public function getCategories_returns_predefined_list()
    {
        $categories = $this->service->getCategories();

        $this->assertCount(9, $categories);
        $this->assertSame('cleaning', $categories[0]['id']);
        $this->assertSame('other', $categories[8]['id']);
    }
}
