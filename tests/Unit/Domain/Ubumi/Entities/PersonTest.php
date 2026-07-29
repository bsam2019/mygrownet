<?php

namespace Tests\Unit\Domain\Ubumi\Entities;

use App\Domain\Ubumi\Entities\Person;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\PersonName;
use App\Domain\Ubumi\ValueObjects\ApproximateAge;
use App\Domain\Ubumi\ValueObjects\Slug;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PersonTest extends TestCase
{
    private PersonName $name;
    private Slug $slug;

    protected function setUp(): void
    {
        parent::setUp();
        $this->name = PersonName::fromString('Alice');
        $this->slug = Slug::fromString('alice');
    }

    #[Test]
    public function create_generates_id_and_defaults()
    {
        $before = new DateTimeImmutable();
        $person = Person::create('family-1', $this->name, $this->slug, 1);
        $after = new DateTimeImmutable();

        $this->assertInstanceOf(Person::class, $person);
        $this->assertInstanceOf(PersonId::class, $person->getId());
        $this->assertEquals('family-1', $person->getFamilyId());
        $this->assertTrue($person->getCreatedAt() >= $before && $person->getCreatedAt() <= $after);
        $this->assertFalse($person->getIsDeceased());
        $this->assertFalse($person->getIsMerged());
        $this->assertNull($person->getMergedFrom());
        $this->assertEquals(1, $person->getCreatedBy());
        $this->assertNull($person->getUpdatedAt());
        $this->assertNull($person->getDeletedAt());
    }

    #[Test]
    public function create_with_all_optional_fields()
    {
        $dob = new DateTimeImmutable('2000-01-15');
        $approxAge = ApproximateAge::fromInt(26);

        $person = Person::create(
            'family-1',
            $this->name,
            $this->slug,
            1,
            'https://example.com/photo.jpg',
            $dob,
            $approxAge,
            'female'
        );

        $this->assertEquals('https://example.com/photo.jpg', $person->getPhotoUrl());
        $this->assertEquals($dob, $person->getDateOfBirth());
        $this->assertTrue($approxAge->equals($person->getApproximateAge()));
        $this->assertEquals('female', $person->getGender());
    }

    #[Test]
    public function reconstitute_restores_from_stored_data()
    {
        $id = PersonId::generate();
        $dob = new DateTimeImmutable('2000-01-15');
        $approxAge = ApproximateAge::fromInt(26);
        $createdAt = new DateTimeImmutable('2026-01-01');
        $updatedAt = new DateTimeImmutable('2026-01-15');
        $deletedAt = new DateTimeImmutable('2026-02-01');

        $person = Person::reconstitute(
            $id,
            'family-x',
            $this->name,
            $this->slug,
            'https://example.com/pic.jpg',
            $dob,
            $approxAge,
            'male',
            true,
            true,
            ['orig-1', 'orig-2'],
            42,
            $createdAt,
            $updatedAt,
            $deletedAt
        );

        $this->assertTrue($id->equals($person->getId()));
        $this->assertEquals('family-x', $person->getFamilyId());
        $this->assertTrue($this->name->equals($person->getName()));
        $this->assertEquals($this->slug->value(), $person->getSlug()->value());
        $this->assertEquals('https://example.com/pic.jpg', $person->getPhotoUrl());
        $this->assertEquals($dob, $person->getDateOfBirth());
        $this->assertTrue($approxAge->equals($person->getApproximateAge()));
        $this->assertEquals('male', $person->getGender());
        $this->assertTrue($person->getIsDeceased());
        $this->assertTrue($person->getIsMerged());
        $this->assertEquals(['orig-1', 'orig-2'], $person->getMergedFrom());
        $this->assertEquals(42, $person->getCreatedBy());
        $this->assertEquals($createdAt, $person->getCreatedAt());
        $this->assertEquals($updatedAt, $person->getUpdatedAt());
        $this->assertEquals($deletedAt, $person->getDeletedAt());
    }

    #[Test]
    public function updateProfile_updates_all_profile_fields()
    {
        $person = Person::create('family-1', $this->name, $this->slug, 1);
        $newName = PersonName::fromString('Bob');
        $newSlug = Slug::fromString('bob');
        $dob = new DateTimeImmutable('1990-05-20');
        $approxAge = ApproximateAge::fromInt(36);

        $person->updateProfile($newName, $newSlug, 'https://new-photo.jpg', $dob, $approxAge, 'male');

        $this->assertTrue($newName->equals($person->getName()));
        $this->assertEquals($newSlug->value(), $person->getSlug()->value());
        $this->assertEquals('https://new-photo.jpg', $person->getPhotoUrl());
        $this->assertEquals($dob, $person->getDateOfBirth());
        $this->assertTrue($approxAge->equals($person->getApproximateAge()));
        $this->assertEquals('male', $person->getGender());
        $this->assertNotNull($person->getUpdatedAt());
    }

    #[Test]
    public function markAsDeceased_sets_flag()
    {
        $person = Person::create('family-1', $this->name, $this->slug, 1);
        $person->markAsDeceased();

        $this->assertTrue($person->getIsDeceased());
        $this->assertNotNull($person->getUpdatedAt());
    }

    #[Test]
    public function markAsMerged_sets_merged_flag_and_original_ids()
    {
        $person = Person::create('family-1', $this->name, $this->slug, 1);
        $originalIds = ['person-uuid-1', 'person-uuid-2'];

        $person->markAsMerged($originalIds);

        $this->assertTrue($person->getIsMerged());
        $this->assertEquals($originalIds, $person->getMergedFrom());
        $this->assertNotNull($person->getUpdatedAt());
    }

    #[Test]
    public function softDelete_sets_deletedAt()
    {
        $person = Person::create('family-1', $this->name, $this->slug, 1);
        $person->softDelete();

        $this->assertNotNull($person->getDeletedAt());
        $this->assertTrue($person->isDeleted());
    }

    #[Test]
    public function restore_clears_deletedAt()
    {
        $person = Person::create('family-1', $this->name, $this->slug, 1);
        $person->softDelete();
        $person->restore();

        $this->assertNull($person->getDeletedAt());
        $this->assertFalse($person->isDeleted());
        $this->assertNotNull($person->getUpdatedAt());
    }

    #[Test]
    public function getAge_returns_age_from_dateOfBirth()
    {
        $dob = new DateTimeImmutable('-30 years');
        $person = Person::create('family-1', $this->name, $this->slug, 1, null, $dob);

        $this->assertEquals(30, $person->getAge());
    }

    #[Test]
    public function getAge_returns_age_from_approximateAge()
    {
        $approxAge = ApproximateAge::fromInt(45);
        $person = Person::create('family-1', $this->name, $this->slug, 1, null, null, $approxAge);

        $this->assertEquals(45, $person->getAge());
    }

    #[Test]
    public function getAge_prefers_dateOfBirth_over_approximateAge()
    {
        $dob = new DateTimeImmutable('-25 years');
        $approxAge = ApproximateAge::fromInt(45);
        $person = Person::create('family-1', $this->name, $this->slug, 1, null, $dob, $approxAge);

        $this->assertEquals(25, $person->getAge());
    }

    #[Test]
    public function getAge_returns_null_when_no_age_data()
    {
        $person = Person::create('family-1', $this->name, $this->slug, 1);

        $this->assertNull($person->getAge());
    }

    #[Test]
    public function isDeleted_returns_false_when_not_deleted()
    {
        $person = Person::create('family-1', $this->name, $this->slug, 1);
        $this->assertFalse($person->isDeleted());
    }
}
