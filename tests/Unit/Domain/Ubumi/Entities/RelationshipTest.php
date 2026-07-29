<?php

namespace Tests\Unit\Domain\Ubumi\Entities;

use App\Domain\Ubumi\Entities\Relationship;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\RelationshipType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RelationshipTest extends TestCase
{
    private PersonId $personId;
    private PersonId $relatedPersonId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->personId = PersonId::generate();
        $this->relatedPersonId = PersonId::generate();
    }

    #[Test]
    public function create_uses_id_zero_and_sets_createdAt()
    {
        $before = new DateTimeImmutable();
        $relationship = Relationship::create(
            $this->personId,
            $this->relatedPersonId,
            RelationshipType::parent()
        );
        $after = new DateTimeImmutable();

        $this->assertInstanceOf(Relationship::class, $relationship);
        $this->assertEquals(0, $relationship->getId());
        $this->assertTrue($relationship->getCreatedAt() >= $before && $relationship->getCreatedAt() <= $after);
        $this->assertNull($relationship->getUpdatedAt());
    }

    #[Test]
    public function create_sets_all_fields()
    {
        $relationship = Relationship::create(
            $this->personId,
            $this->relatedPersonId,
            RelationshipType::spouse()
        );

        $this->assertTrue($this->personId->equals($relationship->getPersonId()));
        $this->assertTrue($this->relatedPersonId->equals($relationship->getRelatedPersonId()));
        $this->assertTrue(RelationshipType::spouse()->equals($relationship->getRelationshipType()));
    }

    #[Test]
    public function create_throws_for_self_relationship()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A person cannot have a relationship with themselves');

        Relationship::create(
            $this->personId,
            $this->personId,
            RelationshipType::sibling()
        );
    }

    #[Test]
    public function reconstitute_restores_from_stored_data()
    {
        $createdAt = new DateTimeImmutable('2026-01-01');
        $updatedAt = new DateTimeImmutable('2026-01-15');

        $relationship = Relationship::reconstitute(
            42,
            $this->personId,
            $this->relatedPersonId,
            RelationshipType::child(),
            $createdAt,
            $updatedAt
        );

        $this->assertEquals(42, $relationship->getId());
        $this->assertTrue($this->personId->equals($relationship->getPersonId()));
        $this->assertTrue($this->relatedPersonId->equals($relationship->getRelatedPersonId()));
        $this->assertTrue(RelationshipType::child()->equals($relationship->getRelationshipType()));
        $this->assertEquals($createdAt, $relationship->getCreatedAt());
        $this->assertEquals($updatedAt, $relationship->getUpdatedAt());
    }

    #[Test]
    public function reconstitute_with_null_updatedAt()
    {
        $createdAt = new DateTimeImmutable('2026-01-01');

        $relationship = Relationship::reconstitute(
            1,
            $this->personId,
            $this->relatedPersonId,
            RelationshipType::sibling(),
            $createdAt,
            null
        );

        $this->assertNull($relationship->getUpdatedAt());
    }

    #[Test]
    public function updateType_updates_relationship_type()
    {
        $relationship = Relationship::create(
            $this->personId,
            $this->relatedPersonId,
            RelationshipType::parent()
        );

        $relationship->updateType(RelationshipType::guardian());

        $this->assertTrue(RelationshipType::guardian()->equals($relationship->getRelationshipType()));
        $this->assertNotNull($relationship->getUpdatedAt());
    }

    #[Test]
    public function getters_return_correct_values()
    {
        $relationship = Relationship::create(
            $this->personId,
            $this->relatedPersonId,
            RelationshipType::sibling()
        );

        $this->assertEquals(0, $relationship->getId());
        $this->assertTrue($this->personId->equals($relationship->getPersonId()));
        $this->assertTrue($this->relatedPersonId->equals($relationship->getRelatedPersonId()));
        $this->assertTrue(RelationshipType::sibling()->equals($relationship->getRelationshipType()));
        $this->assertInstanceOf(DateTimeImmutable::class, $relationship->getCreatedAt());
    }
}
