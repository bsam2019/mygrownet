<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Events\GenericPlatformEvent;
use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\Services\EventSerializer;
use App\Domain\Core\ValueObjects\PlatformContext;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EventSerializerTest extends TestCase
{
    private EventSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new EventSerializer();
    }

    private function createEvent(): PlatformEvent
    {
        $context = PlatformContext::make(
            userId: 'user-1',
            organizationId: 'org-1',
            applicationId: 'app-1',
        );

        return new PlatformEvent(
            eventId: 'evt-123',
            eventName: 'stock.adjusted',
            eventVersion: '1.0',
            publisher: 'stockflow',
            occurredAt: new \DateTimeImmutable('2026-07-29T12:00:00+00:00'),
            correlationId: 'corr-abc',
            causationId: 'caus-xyz',
            context: $context,
            payload: ['item_id' => 'item-1', 'quantity' => 5],
            entityId: 'item-1',
        );
    }

    #[Test]
    public function serialize_returns_json_string()
    {
        $event = $this->createEvent();
        $json = $this->serializer->serialize($event);

        $this->assertJson($json);

        $data = json_decode($json, true);

        $this->assertEquals('evt-123', $data['event_id']);
        $this->assertEquals('stock.adjusted', $data['event_name']);
        $this->assertEquals('1.0', $data['event_version']);
        $this->assertEquals('stockflow', $data['publisher']);
        $this->assertEquals('2026-07-29T12:00:00+00:00', $data['occurred_at']);
        $this->assertEquals('corr-abc', $data['correlation_id']);
        $this->assertEquals('caus-xyz', $data['causation_id']);
        $this->assertEquals(['item_id' => 'item-1', 'quantity' => 5], $data['payload']);
    }

    #[Test]
    public function serialize_includes_context()
    {
        $event = $this->createEvent();
        $data = json_decode($this->serializer->serialize($event), true);

        $this->assertArrayHasKey('context', $data);
        $this->assertEquals('user-1', $data['context']['user_id']);
        $this->assertEquals('org-1', $data['context']['organization_id']);
    }

    #[Test]
    public function deserialize_restores_event()
    {
        $original = $this->createEvent();
        $json = $this->serializer->serialize($original);

        $restored = $this->serializer->deserialize($json);

        $this->assertEquals($original->eventId, $restored->eventId);
        $this->assertEquals($original->eventName(), $restored->eventName());
        $this->assertEquals($original->eventVersion, $restored->eventVersion);
        $this->assertEquals($original->publisher, $restored->publisher);
        $this->assertEquals($original->occurredAt->format('c'), $restored->occurredAt->format('c'));
        $this->assertEquals($original->correlationId, $restored->correlationId);
        $this->assertEquals($original->causationId, $restored->causationId);
        $this->assertEquals($original->payload, $restored->payload);
    }

    #[Test]
    public function deserialize_restores_context()
    {
        $original = $this->createEvent();
        $json = $this->serializer->serialize($original);

        $restored = $this->serializer->deserialize($json);

        $this->assertEquals(
            $original->context->userId,
            $restored->context->userId,
        );
        $this->assertEquals(
            $original->context->organizationId,
            $restored->context->organizationId,
        );
    }

    #[Test]
    public function deserialize_handles_minimal_payload()
    {
        $json = json_encode([
            'event_id' => 'evt-999',
            'event_name' => 'test.event',
            'publisher' => 'test',
            'occurred_at' => '2026-01-01T00:00:00+00:00',
            'correlation_id' => 'corr-999',
            'causation_id' => null,
            'context' => ['user_id' => '', 'organization_id' => '', 'application_id' => ''],
            'payload' => [],
            'event_version' => '1.0',
        ]);

        $event = $this->serializer->deserialize($json);

        $this->assertEquals('evt-999', $event->eventId);
        $this->assertEquals('test.event', $event->eventName());
    }

    #[Test]
    public function headers_returns_correct_format()
    {
        $event = $this->createEvent();
        $headers = $this->serializer->headers($event);

        $this->assertEquals('evt-123', $headers['X-Event-Id']);
        $this->assertEquals('stock.adjusted', $headers['X-Event-Name']);
        $this->assertEquals('stockflow', $headers['X-Publisher']);
        $this->assertEquals('2026-07-29T12:00:00+00:00', $headers['X-Occurred-At']);
        $this->assertEquals('corr-abc', $headers['X-Correlation-Id']);
        $this->assertEquals('caus-xyz', $headers['X-Causation-Id']);
        $this->assertEquals('application/json', $headers['Content-Type']);
    }

    #[Test]
    public function generic_event_round_trips_through_serializer()
    {
        $context = PlatformContext::make(
            userId: 'u',
            organizationId: 'o',
            applicationId: 'a',
        );

        $event = new GenericPlatformEvent(
            eventId: '',
            eventVersion: '1.0',
            publisher: 'test-module',
            correlationId: '',
            context: $context,
            payload: ['key' => 'value'],
            eventName: 'custom.event',
        );

        $json = $this->serializer->serialize($event);
        $restored = $this->serializer->deserialize($json);

        $this->assertEquals('custom.event', $restored->eventName());
        $this->assertEquals('test-module', $restored->publisher);
        $this->assertEquals(['key' => 'value'], $restored->payload);
    }
}
