<?php

namespace Tests\Unit\Domain\Ubumi\Entities;

use App\Domain\Ubumi\Entities\CheckIn;
use App\Domain\Ubumi\ValueObjects\CheckInId;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\CheckInStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CheckInTest extends TestCase
{
    private PersonId $personId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->personId = PersonId::generate();
    }

    #[Test]
    public function create_generates_id_and_uses_current_time()
    {
        $before = new DateTimeImmutable();
        $checkIn = CheckIn::create($this->personId, CheckInStatus::WELL);
        $after = new DateTimeImmutable();

        $this->assertInstanceOf(CheckIn::class, $checkIn);
        $this->assertInstanceOf(CheckInId::class, $checkIn->id());
        $this->assertTrue($checkIn->checkedInAt() >= $before && $checkIn->checkedInAt() <= $after);
        $this->assertTrue($checkIn->createdAt() >= $before && $checkIn->createdAt() <= $after);
        $this->assertTrue($checkIn->updatedAt() >= $before && $checkIn->updatedAt() <= $after);
    }

    #[Test]
    public function create_sets_all_provided_fields()
    {
        $checkedInAt = new DateTimeImmutable('2026-01-15 10:00:00');
        $checkIn = CheckIn::create(
            $this->personId,
            CheckInStatus::UNWELL,
            'Feeling dizzy',
            'Home',
            'https://example.com/photo.jpg',
            $checkedInAt
        );

        $this->assertTrue($this->personId->equals($checkIn->personId()));
        $this->assertSame(CheckInStatus::UNWELL, $checkIn->status());
        $this->assertEquals('Feeling dizzy', $checkIn->note());
        $this->assertEquals('Home', $checkIn->location());
        $this->assertEquals('https://example.com/photo.jpg', $checkIn->photoUrl());
        $this->assertEquals($checkedInAt, $checkIn->checkedInAt());
    }

    #[Test]
    public function create_sets_default_checkedInAt_to_now()
    {
        $before = new DateTimeImmutable();
        $checkIn = CheckIn::create($this->personId, CheckInStatus::WELL);
        $after = new DateTimeImmutable();

        $this->assertTrue($checkIn->checkedInAt() >= $before && $checkIn->checkedInAt() <= $after);
    }

    #[Test]
    public function reconstitute_restores_from_stored_data()
    {
        $id = CheckInId::generate();
        $checkedInAt = new DateTimeImmutable('2026-01-15 08:00:00');
        $createdAt = new DateTimeImmutable('2026-01-15 08:00:00');
        $updatedAt = new DateTimeImmutable('2026-01-15 08:30:00');

        $checkIn = CheckIn::reconstitute(
            $id,
            $this->personId,
            CheckInStatus::NEED_ASSISTANCE,
            'Need help',
            'Clinic',
            'https://example.com/photo.jpg',
            $checkedInAt,
            $createdAt,
            $updatedAt
        );

        $this->assertTrue($id->equals($checkIn->id()));
        $this->assertTrue($this->personId->equals($checkIn->personId()));
        $this->assertSame(CheckInStatus::NEED_ASSISTANCE, $checkIn->status());
        $this->assertEquals('Need help', $checkIn->note());
        $this->assertEquals('Clinic', $checkIn->location());
        $this->assertEquals('https://example.com/photo.jpg', $checkIn->photoUrl());
        $this->assertEquals($checkedInAt, $checkIn->checkedInAt());
        $this->assertEquals($createdAt, $checkIn->createdAt());
        $this->assertEquals($updatedAt, $checkIn->updatedAt());
    }

    #[Test]
    public function reconstitute_with_null_optionals()
    {
        $id = CheckInId::generate();
        $now = new DateTimeImmutable();

        $checkIn = CheckIn::reconstitute(
            $id,
            $this->personId,
            CheckInStatus::WELL,
            null,
            null,
            null,
            $now,
            $now,
            $now
        );

        $this->assertNull($checkIn->note());
        $this->assertNull($checkIn->location());
        $this->assertNull($checkIn->photoUrl());
    }

    #[Test]
    public function requiresAlert_delegates_to_status()
    {
        $wellCheckIn = CheckIn::create($this->personId, CheckInStatus::WELL);
        $this->assertFalse($wellCheckIn->requiresAlert());

        $unwellCheckIn = CheckIn::create($this->personId, CheckInStatus::UNWELL);
        $this->assertTrue($unwellCheckIn->requiresAlert());
    }

    #[Test]
    public function isRecent_returns_true_within_default_hours()
    {
        $checkIn = CheckIn::create($this->personId, CheckInStatus::WELL);
        $this->assertTrue($checkIn->isRecent());
    }

    #[Test]
    public function isRecent_returns_false_for_old_check_in()
    {
        $oldTime = new DateTimeImmutable('-48 hours');
        $checkIn = CheckIn::create(
            $this->personId,
            CheckInStatus::WELL,
            null,
            null,
            null,
            $oldTime
        );
        $this->assertFalse($checkIn->isRecent(24));
    }

    #[Test]
    public function isRecent_respects_custom_hours_threshold()
    {
        $oldTime = new DateTimeImmutable('-36 hours');
        $checkIn = CheckIn::create(
            $this->personId,
            CheckInStatus::WELL,
            null,
            null,
            null,
            $oldTime
        );
        $this->assertTrue($checkIn->isRecent(48));
        $this->assertFalse($checkIn->isRecent(24));
    }

    #[Test]
    public function updateNote_updates_note_and_updatedAt()
    {
        $checkIn = CheckIn::create($this->personId, CheckInStatus::WELL);
        $oldUpdatedAt = $checkIn->updatedAt();

        usleep(1000);
        $checkIn->updateNote('Updated note');

        $this->assertEquals('Updated note', $checkIn->note());
        $this->assertGreaterThan($oldUpdatedAt, $checkIn->updatedAt());
    }
}
