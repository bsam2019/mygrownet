<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\ValueObjects;

use App\Domain\GrowStart\ValueObjects\JourneyProgress;
use PHPUnit\Framework\TestCase;

final class JourneyProgressTest extends TestCase
{
    public function test_can_create_progress(): void
    {
        $progress = JourneyProgress::create(50.0, ['stage_1' => ['completed' => 2, 'total' => 4]], 2, 8, 10, 5);

        $this->assertEquals(50.0, $progress->overall());
        $this->assertEquals(2, $progress->tasksCompleted());
        $this->assertEquals(8, $progress->totalTasks());
        $this->assertEquals(10, $progress->daysActive());
        $this->assertEquals(5, $progress->estimatedDaysRemaining());
    }

    public function test_overall_is_clamped_to_100(): void
    {
        $progress = JourneyProgress::create(150.0, [], 10, 10, 5);
        $this->assertEquals(100, $progress->overall());
    }

    public function test_overall_is_clamped_to_0(): void
    {
        $progress = JourneyProgress::create(-50.0, [], 0, 10, 5);
        $this->assertEquals(0, $progress->overall());
    }

    public function test_overall_at_exact_100_is_complete(): void
    {
        $progress = JourneyProgress::create(100.0, [], 8, 8, 20);
        $this->assertTrue($progress->isComplete());
    }

    public function test_overall_below_100_is_not_complete(): void
    {
        $progress = JourneyProgress::create(99.9, [], 7, 8, 20);
        $this->assertFalse($progress->isComplete());
    }

    public function test_estimated_days_remaining_can_be_null(): void
    {
        $progress = JourneyProgress::create(50.0, [], 2, 8, 10);
        $this->assertNull($progress->estimatedDaysRemaining());
    }

    public function test_stage_progress_returns_array(): void
    {
        $stageData = ['stage_1' => ['completed' => 3, 'total' => 5, 'percentage' => 60.0]];
        $progress = JourneyProgress::create(40.0, $stageData, 3, 10, 5, 3);

        $this->assertEquals($stageData, $progress->stageProgress());
    }

    public function test_to_array_returns_expected_structure(): void
    {
        $progress = JourneyProgress::create(75.0, ['s1' => ['pct' => 75]], 3, 4, 15, 3);
        $result = $progress->toArray();

        $this->assertEquals(75, $result['overall']);
        $this->assertEquals(['s1' => ['pct' => 75]], $result['stage_progress']);
        $this->assertEquals(3, $result['tasks_completed']);
        $this->assertEquals(4, $result['total_tasks']);
        $this->assertEquals(15, $result['days_active']);
        $this->assertEquals(3, $result['estimated_days_remaining']);
    }
}
