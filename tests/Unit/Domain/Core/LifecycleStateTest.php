<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Services\LifecycleState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LifecycleStateTest extends TestCase
{
    #[Test]
    public function all_cases_are_strings()
    {
        $this->assertEquals('active', LifecycleState::Active->value);
        $this->assertEquals('maintenance', LifecycleState::Maintenance->value);
        $this->assertEquals('updating', LifecycleState::Updating->value);
        $this->assertEquals('suspended', LifecycleState::Suspended->value);
        $this->assertEquals('archived', LifecycleState::Archived->value);
    }

    #[Test]
    public function active_can_transition()
    {
        $s = LifecycleState::Active;
        $this->assertTrue($s->canTransitionTo(LifecycleState::Maintenance));
        $this->assertTrue($s->canTransitionTo(LifecycleState::Suspended));
        $this->assertTrue($s->canTransitionTo(LifecycleState::Archived));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Updating));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Active));
    }

    #[Test]
    public function maintenance_can_transition()
    {
        $s = LifecycleState::Maintenance;
        $this->assertTrue($s->canTransitionTo(LifecycleState::Updating));
        $this->assertTrue($s->canTransitionTo(LifecycleState::Active));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Suspended));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Archived));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Maintenance));
    }

    #[Test]
    public function updating_can_only_go_to_active()
    {
        $s = LifecycleState::Updating;
        $this->assertTrue($s->canTransitionTo(LifecycleState::Active));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Maintenance));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Suspended));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Archived));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Updating));
    }

    #[Test]
    public function suspended_can_only_go_to_active()
    {
        $s = LifecycleState::Suspended;
        $this->assertTrue($s->canTransitionTo(LifecycleState::Active));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Maintenance));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Updating));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Archived));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Suspended));
    }

    #[Test]
    public function archived_cannot_transition()
    {
        $s = LifecycleState::Archived;
        $this->assertFalse($s->canTransitionTo(LifecycleState::Active));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Maintenance));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Updating));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Suspended));
        $this->assertFalse($s->canTransitionTo(LifecycleState::Archived));
    }

    #[Test]
    public function labels_are_readable()
    {
        $this->assertEquals('Active', LifecycleState::Active->label());
        $this->assertEquals('Maintenance', LifecycleState::Maintenance->label());
        $this->assertEquals('Updating', LifecycleState::Updating->label());
        $this->assertEquals('Suspended', LifecycleState::Suspended->label());
        $this->assertEquals('Archived', LifecycleState::Archived->label());
    }
}
