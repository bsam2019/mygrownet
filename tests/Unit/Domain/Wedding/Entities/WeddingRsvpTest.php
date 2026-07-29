<?php

namespace Tests\Unit\Domain\Wedding\Entities;

use App\Domain\Wedding\Entities\WeddingRsvp;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WeddingRsvpTest extends TestCase
{
    public function test_create(): void
    {
        $rsvp = WeddingRsvp::create(
            weddingEventId: 1,
            firstName: 'Alice',
            lastName: 'Brown',
            email: 'alice@example.com',
            phone: '+260970000000',
            attending: true,
            guestCount: 2,
            dietaryRestrictions: 'Vegetarian',
            message: 'Can\'t wait!'
        );

        $this->assertNull($rsvp->getId());
        $this->assertEquals(1, $rsvp->getWeddingEventId());
        $this->assertEquals('Alice', $rsvp->getFirstName());
        $this->assertEquals('Brown', $rsvp->getLastName());
        $this->assertEquals('Alice Brown', $rsvp->getFullName());
        $this->assertEquals('alice@example.com', $rsvp->getEmail());
        $this->assertEquals('+260970000000', $rsvp->getPhone());
        $this->assertTrue($rsvp->isAttending());
        $this->assertEquals(2, $rsvp->getGuestCount());
        $this->assertEquals('Vegetarian', $rsvp->getDietaryRestrictions());
        $this->assertEquals('Can\'t wait!', $rsvp->getMessage());
        $this->assertNotNull($rsvp->getSubmittedAt());
    }

    public function test_create_declining(): void
    {
        $rsvp = WeddingRsvp::create(
            weddingEventId: 1,
            firstName: 'Bob',
            lastName: 'Jones',
            email: 'bob@example.com',
            phone: null,
            attending: false,
            guestCount: 0,
            dietaryRestrictions: null,
            message: 'Sorry, can\'t make it'
        );

        $this->assertFalse($rsvp->isAttending());
        $this->assertEquals(0, $rsvp->getGuestCount());
        $this->assertNull($rsvp->getPhone());
        $this->assertNull($rsvp->getDietaryRestrictions());
    }

    public function test_from_array(): void
    {
        $now = new DateTimeImmutable();
        $data = [
            'id' => 10,
            'wedding_event_id' => 1,
            'first_name' => 'Charlie',
            'last_name' => 'Davis',
            'email' => 'charlie@example.com',
            'phone' => '+260972222222',
            'attending' => true,
            'guest_count' => 3,
            'dietary_restrictions' => 'Gluten-free',
            'message' => 'See you there!',
            'submitted_at' => '2026-06-15 14:30:00',
        ];

        $rsvp = WeddingRsvp::fromArray($data);

        $this->assertEquals(10, $rsvp->getId());
        $this->assertEquals(1, $rsvp->getWeddingEventId());
        $this->assertTrue($rsvp->isAttending());
        $this->assertEquals(3, $rsvp->getGuestCount());
        $this->assertEquals('Gluten-free', $rsvp->getDietaryRestrictions());
        $this->assertEquals('2026-06-15 14:30:00', $rsvp->getSubmittedAt()->format('Y-m-d H:i:s'));
    }

    public function test_from_array_with_string_date(): void
    {
        $data = [
            'wedding_event_id' => 1,
            'first_name' => 'Diana',
            'last_name' => 'Eve',
            'email' => 'diana@example.com',
            'attending' => false,
        ];

        $rsvp = WeddingRsvp::fromArray($data);
        $this->assertFalse($rsvp->isAttending());
        $this->assertNotNull($rsvp->getSubmittedAt());
    }

    public function test_to_array(): void
    {
        $rsvp = WeddingRsvp::create(
            weddingEventId: 1,
            firstName: 'Eve',
            lastName: 'Adams',
            email: 'eve@example.com',
            phone: null,
            attending: true,
            guestCount: 1,
            dietaryRestrictions: null,
            message: null
        );

        $result = $rsvp->toArray();

        $this->assertNull($result['id']);
        $this->assertEquals(1, $result['wedding_event_id']);
        $this->assertEquals('Eve', $result['first_name']);
        $this->assertEquals('Adams', $result['last_name']);
        $this->assertEquals('Eve Adams', $result['full_name']);
        $this->assertEquals('eve@example.com', $result['email']);
        $this->assertTrue($result['attending']);
        $this->assertEquals(1, $result['guest_count']);
        $this->assertNotNull($result['submitted_at']);
    }
}
