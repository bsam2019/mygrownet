<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\ValueObjects;

use App\Domain\GrowStart\ValueObjects\JourneyStatus;
use PHPUnit\Framework\TestCase;

final class JourneyStatusTest extends TestCase
{
    public function test_active_is_active(): void
    {
        $status = JourneyStatus::active();
        $this->assertTrue($status->isActive());
        $this->assertFalse($status->isPaused());
        $this->assertFalse($status->isCompleted());
        $this->assertFalse($status->isArchived());
    }

    public function test_paused_is_paused(): void
    {
        $status = JourneyStatus::paused();
        $this->assertTrue($status->isPaused());
        $this->assertFalse($status->isActive());
    }

    public function test_completed_is_completed(): void
    {
        $status = JourneyStatus::completed();
        $this->assertTrue($status->isCompleted());
        $this->assertFalse($status->isActive());
    }

    public function test_archived_is_archived(): void
    {
        $status = JourneyStatus::archived();
        $this->assertTrue($status->isArchived());
        $this->assertFalse($status->isActive());
    }

    public function test_can_create_from_string(): void
    {
        $status = JourneyStatus::fromString('active');
        $this->assertTrue($status->isActive());
        $this->assertEquals('active', $status->value());
    }

    public function test_cannot_create_from_invalid_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        JourneyStatus::fromString('invalid');
    }

    public function test_value_returns_string(): void
    {
        $this->assertEquals('active', JourneyStatus::active()->value());
        $this->assertEquals('paused', JourneyStatus::paused()->value());
        $this->assertEquals('completed', JourneyStatus::completed()->value());
        $this->assertEquals('archived', JourneyStatus::archived()->value());
    }

    public function test_active_can_transition_to_paused_completed_archived(): void
    {
        $status = JourneyStatus::active();
        $this->assertTrue($status->canTransitionTo(JourneyStatus::paused()));
        $this->assertTrue($status->canTransitionTo(JourneyStatus::completed()));
        $this->assertTrue($status->canTransitionTo(JourneyStatus::archived()));
    }

    public function test_active_cannot_transition_to_active(): void
    {
        $status = JourneyStatus::active();
        $this->assertFalse($status->canTransitionTo(JourneyStatus::active()));
    }

    public function test_paused_can_transition_to_active_or_archived(): void
    {
        $status = JourneyStatus::paused();
        $this->assertTrue($status->canTransitionTo(JourneyStatus::active()));
        $this->assertTrue($status->canTransitionTo(JourneyStatus::archived()));
        $this->assertFalse($status->canTransitionTo(JourneyStatus::completed()));
    }

    public function test_completed_can_transition_to_archived_only(): void
    {
        $status = JourneyStatus::completed();
        $this->assertTrue($status->canTransitionTo(JourneyStatus::archived()));
        $this->assertFalse($status->canTransitionTo(JourneyStatus::active()));
        $this->assertFalse($status->canTransitionTo(JourneyStatus::paused()));
    }

    public function test_archived_cannot_transition_to_anything(): void
    {
        $status = JourneyStatus::archived();
        $this->assertFalse($status->canTransitionTo(JourneyStatus::active()));
        $this->assertFalse($status->canTransitionTo(JourneyStatus::paused()));
        $this->assertFalse($status->canTransitionTo(JourneyStatus::completed()));
        $this->assertFalse($status->canTransitionTo(JourneyStatus::archived()));
    }

    public function test_equals_returns_true_for_same_status(): void
    {
        $this->assertTrue(JourneyStatus::active()->equals(JourneyStatus::active()));
        $this->assertFalse(JourneyStatus::active()->equals(JourneyStatus::paused()));
    }

    public function test_to_string_returns_value(): void
    {
        $this->assertEquals('active', (string) JourneyStatus::active());
        $this->assertEquals('paused', (string) JourneyStatus::paused());
    }
}
