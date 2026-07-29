<?php

namespace Tests\Unit\Domain\Wedding\ValueObjects;

use App\Domain\Wedding\ValueObjects\WeddingStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WeddingStatusTest extends TestCase
{
    public function test_planning(): void
    {
        $status = WeddingStatus::planning();
        $this->assertEquals('planning', $status->getValue());
        $this->assertEquals('Planning', $status->getLabel());
        $this->assertEquals('blue', $status->getColor());
        $this->assertTrue($status->isPlanning());
        $this->assertFalse($status->isConfirmed());
        $this->assertFalse($status->isCompleted());
        $this->assertFalse($status->isCancelled());
        $this->assertTrue($status->isActive());
    }

    public function test_confirmed(): void
    {
        $status = WeddingStatus::confirmed();
        $this->assertEquals('confirmed', $status->getValue());
        $this->assertEquals('Confirmed', $status->getLabel());
        $this->assertEquals('green', $status->getColor());
        $this->assertTrue($status->isConfirmed());
        $this->assertTrue($status->isActive());
    }

    public function test_completed(): void
    {
        $status = WeddingStatus::completed();
        $this->assertEquals('completed', $status->getValue());
        $this->assertEquals('Completed', $status->getLabel());
        $this->assertEquals('gray', $status->getColor());
        $this->assertTrue($status->isCompleted());
        $this->assertFalse($status->isActive());
    }

    public function test_cancelled(): void
    {
        $status = WeddingStatus::cancelled();
        $this->assertEquals('cancelled', $status->getValue());
        $this->assertEquals('Cancelled', $status->getLabel());
        $this->assertEquals('red', $status->getColor());
        $this->assertTrue($status->isCancelled());
        $this->assertFalse($status->isActive());
    }

    public function test_from_string(): void
    {
        $status = WeddingStatus::fromString('confirmed');
        $this->assertTrue($status->isConfirmed());
    }

    public function test_from_string_invalid_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        WeddingStatus::fromString('unknown');
    }

    public function test_can_transition_from_planning_to_confirmed(): void
    {
        $status = WeddingStatus::planning();
        $this->assertTrue($status->canTransitionTo(WeddingStatus::confirmed()));
    }

    public function test_can_transition_from_planning_to_cancelled(): void
    {
        $status = WeddingStatus::planning();
        $this->assertTrue($status->canTransitionTo(WeddingStatus::cancelled()));
    }

    public function test_cannot_transition_from_planning_to_completed(): void
    {
        $status = WeddingStatus::planning();
        $this->assertFalse($status->canTransitionTo(WeddingStatus::completed()));
    }

    public function test_can_transition_from_confirmed_to_completed(): void
    {
        $status = WeddingStatus::confirmed();
        $this->assertTrue($status->canTransitionTo(WeddingStatus::completed()));
    }

    public function test_can_transition_from_confirmed_to_cancelled(): void
    {
        $status = WeddingStatus::confirmed();
        $this->assertTrue($status->canTransitionTo(WeddingStatus::cancelled()));
    }

    public function test_cannot_transition_from_completed(): void
    {
        $status = WeddingStatus::completed();
        $this->assertFalse($status->canTransitionTo(WeddingStatus::confirmed()));
        $this->assertFalse($status->canTransitionTo(WeddingStatus::cancelled()));
        $this->assertFalse($status->canTransitionTo(WeddingStatus::planning()));
    }

    public function test_cannot_transition_from_cancelled(): void
    {
        $status = WeddingStatus::cancelled();
        $this->assertFalse($status->canTransitionTo(WeddingStatus::planning()));
        $this->assertFalse($status->canTransitionTo(WeddingStatus::confirmed()));
        $this->assertFalse($status->canTransitionTo(WeddingStatus::completed()));
    }

    public function test_equals(): void
    {
        $a = WeddingStatus::planning();
        $b = WeddingStatus::planning();
        $c = WeddingStatus::confirmed();

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
