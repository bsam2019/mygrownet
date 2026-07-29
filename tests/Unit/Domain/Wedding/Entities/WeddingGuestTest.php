<?php

namespace Tests\Unit\Domain\Wedding\Entities;

use App\Domain\Wedding\Entities\WeddingGuest;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WeddingGuestTest extends TestCase
{
    public function test_create(): void
    {
        $guest = WeddingGuest::create(
            weddingEventId: 1,
            firstName: 'John',
            lastName: 'Smith',
            email: 'john@example.com',
            phone: '+260970000000',
            allowedGuests: 2,
            groupName: 'Family',
            notes: 'Vegetarian'
        );

        $this->assertNull($guest->getId());
        $this->assertEquals(1, $guest->getWeddingEventId());
        $this->assertEquals('John', $guest->getFirstName());
        $this->assertEquals('Smith', $guest->getLastName());
        $this->assertEquals('John Smith', $guest->getFullName());
        $this->assertEquals('john@example.com', $guest->getEmail());
        $this->assertEquals('+260970000000', $guest->getPhone());
        $this->assertEquals(2, $guest->getAllowedGuests());
        $this->assertEquals('Family', $guest->getGroupName());
        $this->assertEquals('Vegetarian', $guest->getNotes());
        $this->assertFalse($guest->isInvitationSent());
        $this->assertEquals('pending', $guest->getRsvpStatus());
        $this->assertEquals(0, $guest->getConfirmedGuests());
        $this->assertNull($guest->getDietaryRestrictions());
        $this->assertNull($guest->getRsvpMessage());
        $this->assertNull($guest->getRsvpSubmittedAt());
        $this->assertFalse($guest->isAttending());
        $this->assertFalse($guest->hasResponded());
    }

    public function test_create_with_minimal_fields(): void
    {
        $guest = WeddingGuest::create(
            weddingEventId: 1,
            firstName: 'John',
            lastName: 'Smith'
        );

        $this->assertEquals(1, $guest->getAllowedGuests());
        $this->assertNull($guest->getEmail());
        $this->assertNull($guest->getPhone());
        $this->assertNull($guest->getGroupName());
        $this->assertNull($guest->getNotes());
    }

    public function test_is_attending(): void
    {
        $guest = WeddingGuest::create(1, 'John', 'Smith');
        $this->assertFalse($guest->isAttending());
    }

    public function test_has_not_responded_when_pending(): void
    {
        $guest = WeddingGuest::create(1, 'John', 'Smith');
        $this->assertFalse($guest->hasResponded());
    }

    public function test_from_array(): void
    {
        $now = new DateTimeImmutable();
        $data = [
            'id' => 5,
            'wedding_event_id' => 1,
            'first_name' => 'Alice',
            'last_name' => 'Brown',
            'email' => 'alice@example.com',
            'phone' => '+260971111111',
            'allowed_guests' => 3,
            'group_name' => 'Friends',
            'notes' => 'Allergic to peanuts',
            'invitation_sent' => true,
            'rsvp_status' => 'attending',
            'confirmed_guests' => 2,
            'dietary_restrictions' => 'Peanut allergy',
            'rsvp_message' => 'Looking forward!',
            'rsvp_submitted_at' => $now,
        ];

        $guest = WeddingGuest::fromArray($data);

        $this->assertEquals(5, $guest->getId());
        $this->assertEquals(1, $guest->getWeddingEventId());
        $this->assertEquals('Alice', $guest->getFirstName());
        $this->assertEquals('Brown', $guest->getLastName());
        $this->assertEquals('Alice Brown', $guest->getFullName());
        $this->assertEquals('alice@example.com', $guest->getEmail());
        $this->assertTrue($guest->isInvitationSent());
        $this->assertEquals('attending', $guest->getRsvpStatus());
        $this->assertEquals(2, $guest->getConfirmedGuests());
        $this->assertEquals('Peanut allergy', $guest->getDietaryRestrictions());
        $this->assertEquals('Looking forward!', $guest->getRsvpMessage());
        $this->assertSame($now, $guest->getRsvpSubmittedAt());
        $this->assertTrue($guest->isAttending());
        $this->assertTrue($guest->hasResponded());
    }

    public function test_from_array_with_string_date(): void
    {
        $data = [
            'wedding_event_id' => 1,
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'rsvp_submitted_at' => '2026-06-15 14:30:00',
        ];

        $guest = WeddingGuest::fromArray($data);
        $this->assertNotNull($guest->getRsvpSubmittedAt());
        $this->assertEquals('2026-06-15 14:30:00', $guest->getRsvpSubmittedAt()->format('Y-m-d H:i:s'));
    }

    public function test_to_array(): void
    {
        $guest = WeddingGuest::create(
            weddingEventId: 1,
            firstName: 'John',
            lastName: 'Smith',
            email: 'john@example.com',
            allowedGuests: 2,
        );

        $result = $guest->toArray();

        $this->assertNull($result['id']);
        $this->assertEquals(1, $result['wedding_event_id']);
        $this->assertEquals('John', $result['first_name']);
        $this->assertEquals('Smith', $result['last_name']);
        $this->assertEquals('John Smith', $result['full_name']);
        $this->assertEquals('john@example.com', $result['email']);
        $this->assertEquals(2, $result['allowed_guests']);
        $this->assertEquals('pending', $result['rsvp_status']);
        $this->assertEquals(0, $result['confirmed_guests']);
        $this->assertNull($result['rsvp_submitted_at']);
    }
}
