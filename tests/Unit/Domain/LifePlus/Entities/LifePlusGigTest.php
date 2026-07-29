<?php

namespace Tests\Unit\Domain\LifePlus\Entities;

use App\Domain\LifePlus\Entities\LifePlusGig;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LifePlusGigTest extends TestCase
{
    #[Test]
    public function reconstitute_sets_all_fields()
    {
        $createdAt = new DateTimeImmutable('2026-08-10 08:00:00');
        $updatedAt = new DateTimeImmutable('2026-08-11 10:00:00');

        $gig = LifePlusGig::reconstitute([
            'id' => 1,
            'user_id' => 42,
            'title' => 'Clean yard',
            'description' => 'Mow lawn and trim hedges',
            'category' => 'yard_work',
            'payment_amount' => '150.00',
            'location' => 'Lusaka',
            'latitude' => '-15.3875',
            'longitude' => '28.3228',
            'status' => 'open',
            'assigned_to' => '55',
            'created_at' => '2026-08-10 08:00:00',
            'updated_at' => '2026-08-11 10:00:00',
        ]);

        $this->assertSame(1, $gig->id);
        $this->assertSame(42, $gig->userId);
        $this->assertSame('Clean yard', $gig->title);
        $this->assertSame('Mow lawn and trim hedges', $gig->description);
        $this->assertSame('yard_work', $gig->category);
        $this->assertSame(150.0, $gig->paymentAmount);
        $this->assertSame('Lusaka', $gig->location);
        $this->assertSame(-15.3875, $gig->latitude);
        $this->assertSame(28.3228, $gig->longitude);
        $this->assertSame('open', $gig->status);
        $this->assertSame(55, $gig->assignedTo);
        $this->assertEquals($createdAt, $gig->createdAt);
        $this->assertEquals($updatedAt, $gig->updatedAt);
    }

    #[Test]
    public function reconstitute_applies_defaults()
    {
        $gig = LifePlusGig::reconstitute([
            'user_id' => 1,
            'title' => 'Babysit',
        ]);

        $this->assertNull($gig->id);
        $this->assertNull($gig->description);
        $this->assertNull($gig->category);
        $this->assertNull($gig->paymentAmount);
        $this->assertNull($gig->location);
        $this->assertNull($gig->latitude);
        $this->assertNull($gig->longitude);
        $this->assertSame('open', $gig->status);
        $this->assertNull($gig->assignedTo);
        $this->assertNull($gig->createdAt);
        $this->assertNull($gig->updatedAt);
    }

    #[Test]
    public function toArray_round_trips_all_fields()
    {
        $data = [
            'id' => 3,
            'user_id' => 7,
            'title' => 'Tutoring',
            'description' => 'Math grade 7',
            'category' => 'tutoring',
            'payment_amount' => 80.0,
            'location' => null,
            'latitude' => null,
            'longitude' => null,
            'status' => 'assigned',
            'assigned_to' => 12,
            'created_at' => '2026-08-12 09:00:00',
            'updated_at' => null,
        ];

        $gig = LifePlusGig::reconstitute($data);
        $result = $gig->toArray();

        $this->assertSame($data['id'], $result['id']);
        $this->assertSame($data['user_id'], $result['user_id']);
        $this->assertSame($data['title'], $result['title']);
        $this->assertSame($data['description'], $result['description']);
        $this->assertSame($data['category'], $result['category']);
        $this->assertSame($data['payment_amount'], $result['payment_amount']);
        $this->assertNull($result['location']);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertSame($data['status'], $result['status']);
        $this->assertSame($data['assigned_to'], $result['assigned_to']);
        $this->assertSame($data['created_at'], $result['created_at']);
        $this->assertNull($result['updated_at']);
    }
}
