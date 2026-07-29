<?php

namespace Tests\Unit\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusHabit;
use App\Domain\LifePlus\Repositories\HabitRepositoryInterface;
use App\Domain\LifePlus\Services\HabitService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HabitServiceTest extends TestCase
{
    private HabitRepositoryInterface $habitRepo;
    private HabitService $service;

    protected function setUp(): void
    {
        $this->habitRepo = $this->createMock(HabitRepositoryInterface::class);
        $this->service = new HabitService($this->habitRepo);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    private function mockHabitLogModel(): void
    {
        $logMock = \Mockery::mock('alias:App\Infrastructure\Persistence\Eloquent\LifePlusHabitLogModel');
        $queryMock = \Mockery::mock('stdClass');
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('orderBy')->andReturnSelf();
        $queryMock->shouldReceive('pluck')->andReturn(collect([]));
        $queryMock->shouldReceive('exists')->andReturn(false);

        $logMock->shouldReceive('where')->andReturn($queryMock);
    }

    #[Test]
    public function getHabits_returns_mapped_habits()
    {
        $this->mockHabitLogModel();

        $habit = LifePlusHabit::reconstitute(['id' => 1, 'user_id' => 42, 'name' => 'Exercise', 'frequency' => 'daily']);
        $this->habitRepo->expects($this->once())->method('findByUser')->with(42)->willReturn([$habit]);

        $result = $this->service->getHabits(42);

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('Exercise', $result[0]['name']);
        $this->assertSame('daily', $result[0]['frequency']);
        $this->assertSame(0, $result[0]['streak']);
        $this->assertFalse($result[0]['today_completed']);
    }

    #[Test]
    public function createHabit_saves_and_returns_mapped()
    {
        $this->mockHabitLogModel();

        $saved = LifePlusHabit::reconstitute(['id' => 10, 'user_id' => 42, 'name' => 'Read', 'frequency' => 'daily']);
        $this->habitRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->createHabit(42, ['name' => 'Read']);

        $this->assertSame(10, $result['id']);
        $this->assertSame('Read', $result['name']);
        $this->assertSame('daily', $result['frequency']);
    }

    #[Test]
    public function createHabit_applies_default_icon_color_and_frequency()
    {
        $this->mockHabitLogModel();

        $this->habitRepo->expects($this->once())->method('save')->willReturnCallback(function (LifePlusHabit $habit) {
            $this->assertSame('⭐', $habit->icon);
            $this->assertSame('#10b981', $habit->color);
            $this->assertSame('daily', $habit->frequency);
            $this->assertTrue($habit->isActive);
            return LifePlusHabit::reconstitute(['id' => 99, 'user_id' => 42, 'name' => $habit->name]);
        });

        $this->service->createHabit(42, ['name' => 'Meditate']);
    }

    #[Test]
    public function updateHabit_returns_null_when_not_found()
    {
        $this->habitRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertNull($this->service->updateHabit(1, 42, ['name' => 'Updated']));
    }

    #[Test]
    public function updateHabit_returns_null_on_user_mismatch()
    {
        $habit = LifePlusHabit::reconstitute(['id' => 1, 'user_id' => 99, 'name' => 'Other']);
        $this->habitRepo->expects($this->once())->method('findById')->with(1)->willReturn($habit);

        $this->assertNull($this->service->updateHabit(1, 42, ['name' => 'Updated']));
    }

    #[Test]
    public function updateHabit_merges_and_saves()
    {
        $this->mockHabitLogModel();

        $habit = LifePlusHabit::reconstitute(['id' => 1, 'user_id' => 42, 'name' => 'Old', 'frequency' => 'daily']);
        $updated = LifePlusHabit::reconstitute(['id' => 1, 'user_id' => 42, 'name' => 'Updated', 'frequency' => 'weekly']);

        $this->habitRepo->expects($this->once())->method('findById')->with(1)->willReturn($habit);
        $this->habitRepo->expects($this->once())->method('save')->willReturn($updated);

        $result = $this->service->updateHabit(1, 42, ['name' => 'Updated', 'frequency' => 'weekly']);

        $this->assertSame('Updated', $result['name']);
        $this->assertSame('weekly', $result['frequency']);
    }

    #[Test]
    public function deleteHabit_returns_true_on_success()
    {
        $habit = LifePlusHabit::reconstitute(['id' => 1, 'user_id' => 42, 'name' => 'Delete']);
        $this->habitRepo->expects($this->once())->method('findById')->with(1)->willReturn($habit);
        $this->habitRepo->expects($this->once())->method('delete')->with(1)->willReturn(true);

        $this->assertTrue($this->service->deleteHabit(1, 42));
    }

    #[Test]
    public function deleteHabit_returns_false_on_user_mismatch()
    {
        $habit = LifePlusHabit::reconstitute(['id' => 1, 'user_id' => 99, 'name' => 'Other']);
        $this->habitRepo->expects($this->once())->method('findById')->with(1)->willReturn($habit);

        $this->assertFalse($this->service->deleteHabit(1, 42));
    }

    #[Test]
    public function logHabit_creates_log_when_no_existing()
    {
        $habit = LifePlusHabit::reconstitute(['id' => 1, 'user_id' => 42, 'name' => 'Exercise', 'frequency' => 'daily']);
        $this->habitRepo->expects($this->once())->method('findById')->with(1)->willReturn($habit);

        $queryMock = \Mockery::mock('stdClass');
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('orderBy')->andReturnSelf();
        $queryMock->shouldReceive('pluck')->andReturn(collect([]));
        $queryMock->shouldReceive('first')->andReturn(null);
        $queryMock->shouldReceive('exists')->andReturn(false);

        $logMock = \Mockery::mock('alias:App\Infrastructure\Persistence\Eloquent\LifePlusHabitLogModel');
        $logMock->shouldReceive('where')->andReturn($queryMock);
        $logMock->shouldReceive('create')->andReturn((object) ['id' => 1]);

        $result = $this->service->logHabit(1, 42);

        $this->assertSame(1, $result['id']);
    }

    #[Test]
    public function logHabit_toggles_off_when_already_logged()
    {
        $habit = LifePlusHabit::reconstitute(['id' => 1, 'user_id' => 42, 'name' => 'Exercise', 'frequency' => 'daily']);
        $this->habitRepo->expects($this->once())->method('findById')->with(1)->willReturn($habit);

        $existingLog = \Mockery::mock('stdClass');
        $existingLog->shouldReceive('delete')->once();

        $queryMock = \Mockery::mock('stdClass');
        $queryMock->shouldReceive('where')->andReturnSelf();
        $queryMock->shouldReceive('orderBy')->andReturnSelf();
        $queryMock->shouldReceive('pluck')->andReturn(collect([]));
        $queryMock->shouldReceive('first')->andReturn($existingLog);
        $queryMock->shouldReceive('exists')->andReturn(false);

        $logMock = \Mockery::mock('alias:App\Infrastructure\Persistence\Eloquent\LifePlusHabitLogModel');
        $logMock->shouldReceive('where')->andReturn($queryMock);

        $result = $this->service->logHabit(1, 42);

        $this->assertNotNull($result);
    }

    #[Test]
    public function logHabit_returns_null_on_not_found()
    {
        $this->habitRepo->expects($this->once())->method('findById')->with(99)->willReturn(null);

        $this->assertNull($this->service->logHabit(99, 42));
    }

    #[Test]
    public function logHabit_returns_null_on_user_mismatch()
    {
        $habit = LifePlusHabit::reconstitute(['id' => 1, 'user_id' => 99, 'name' => 'Other']);
        $this->habitRepo->expects($this->once())->method('findById')->with(1)->willReturn($habit);

        $this->assertNull($this->service->logHabit(1, 42));
    }
}
