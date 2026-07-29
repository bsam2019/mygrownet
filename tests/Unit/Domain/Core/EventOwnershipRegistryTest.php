<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Services\EventOwnershipRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventOwnershipRegistryTest extends TestCase
{
    private EventOwnershipRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new EventOwnershipRegistry();
        $this->registry->register('user.created', 'platform-core');
        $this->registry->register('stock.adjusted', 'stockflow');
    }

    #[Test]
    public function canPublish_returns_true_for_registered()
    {
        $this->assertTrue($this->registry->canPublish('user.created'));
    }

    #[Test]
    public function canPublish_returns_false_for_unregistered()
    {
        $this->assertFalse($this->registry->canPublish('unknown.event'));
    }

    #[Test]
    public function owner_returns_registered_owner()
    {
        $this->assertEquals('platform-core', $this->registry->owner('user.created'));
        $this->assertEquals('stockflow', $this->registry->owner('stock.adjusted'));
    }

    #[Test]
    public function owner_returns_null_for_unregistered()
    {
        $this->assertNull($this->registry->owner('unknown'));
    }

    #[Test]
    public function all_returns_all_registrations()
    {
        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertEquals('platform-core', $all['user.created']);
        $this->assertEquals('stockflow', $all['stock.adjusted']);
    }

    #[Test]
    public function eventsOwnedBy_returns_matching_events()
    {
        $events = $this->registry->eventsOwnedBy('stockflow');

        $this->assertEquals(['stock.adjusted'], $events);
    }

    #[Test]
    public function eventsOwnedBy_returns_empty_for_none()
    {
        $this->assertEquals([], $this->registry->eventsOwnedBy('unknown'));
    }

    #[Test]
    public function duplicate_register_with_same_owner_succeeds()
    {
        $this->registry->register('user.created', 'platform-core');

        $this->assertTrue($this->registry->canPublish('user.created'));
    }

    #[Test]
    public function duplicate_register_with_different_owner_throws()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Event 'user.created' is already registered to 'platform-core', cannot register to 'stockflow'");

        $this->registry->register('user.created', 'stockflow');
    }
}
