<?php

namespace Tests\Unit\Domain\Ubumi\Services;

use App\Domain\Ubumi\Services\RelationshipService;
use App\Domain\Ubumi\Entities\Relationship;
use App\Domain\Ubumi\Entities\Person;
use App\Domain\Ubumi\Repositories\RelationshipRepositoryInterface;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\PersonName;
use App\Domain\Ubumi\ValueObjects\RelationshipType;
use App\Domain\Ubumi\ValueObjects\ApproximateAge;
use App\Domain\Ubumi\ValueObjects\Slug;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RelationshipServiceTest extends TestCase
{
    private RelationshipRepositoryInterface $relationshipRepo;
    private PersonRepositoryInterface $personRepo;
    private RelationshipService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->relationshipRepo = $this->createMock(RelationshipRepositoryInterface::class);
        $this->personRepo = $this->createMock(PersonRepositoryInterface::class);
        $this->service = new RelationshipService($this->relationshipRepo, $this->personRepo);
    }

    #[Test]
    public function createRelationship_saves_both_directions()
    {
        $personId = PersonId::generate();
        $relatedPersonId = PersonId::generate();
        $type = RelationshipType::parent();

        $this->relationshipRepo
            ->expects($this->once())
            ->method('exists')
            ->with($personId, $relatedPersonId, $type)
            ->willReturn(false);

        $this->relationshipRepo
            ->expects($this->exactly(2))
            ->method('save');

        $this->service->createRelationship($personId, $relatedPersonId, $type);
    }

    #[Test]
    public function createRelationship_throws_when_already_exists()
    {
        $personId = PersonId::generate();
        $relatedPersonId = PersonId::generate();
        $type = RelationshipType::sibling();

        $this->relationshipRepo
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('This relationship already exists');

        $this->service->createRelationship($personId, $relatedPersonId, $type);
    }

    #[Test]
    public function updateRelationship_updates_both_directions()
    {
        $personId = PersonId::generate();
        $relatedPersonId = PersonId::generate();
        $existingRel = Relationship::reconstitute(
            1,
            $personId,
            $relatedPersonId,
            RelationshipType::parent(),
            new \DateTimeImmutable(),
            null
        );
        $inverseRel = Relationship::reconstitute(
            2,
            $relatedPersonId,
            $personId,
            RelationshipType::child(),
            new \DateTimeImmutable(),
            null
        );

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($existingRel);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findRelationship')
            ->with($relatedPersonId, $personId)
            ->willReturn($inverseRel);

        $this->relationshipRepo
            ->expects($this->exactly(2))
            ->method('save');

        $this->service->updateRelationship(1, RelationshipType::guardian());
    }

    #[Test]
    public function updateRelationship_throws_when_not_found()
    {
        $this->relationshipRepo
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Relationship not found');

        $this->service->updateRelationship(999, RelationshipType::spouse());
    }

    #[Test]
    public function deleteRelationship_deletes_both_directions()
    {
        $personId = PersonId::generate();
        $relatedPersonId = PersonId::generate();
        $existingRel = Relationship::reconstitute(
            1,
            $personId,
            $relatedPersonId,
            RelationshipType::spouse(),
            new \DateTimeImmutable(),
            null
        );
        $inverseRel = Relationship::reconstitute(
            2,
            $relatedPersonId,
            $personId,
            RelationshipType::spouse(),
            new \DateTimeImmutable(),
            null
        );

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($existingRel);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findRelationship')
            ->with($relatedPersonId, $personId)
            ->willReturn($inverseRel);

        $this->relationshipRepo
            ->expects($this->exactly(2))
            ->method('delete');

        $this->service->deleteRelationship(1);
    }

    #[Test]
    public function deleteRelationship_throws_when_not_found()
    {
        $this->relationshipRepo
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Relationship not found');

        $this->service->deleteRelationship(999);
    }

    #[Test]
    public function getPersonRelationships_returns_from_repository()
    {
        $personId = PersonId::generate();
        $expected = [Relationship::create($personId, PersonId::generate(), RelationshipType::sibling())];

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findByPersonId')
            ->with($personId)
            ->willReturn($expected);

        $result = $this->service->getPersonRelationships($personId);

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function validateRelationship_accepts_valid_parent_child()
    {
        $parentId = PersonId::generate();
        $childId = PersonId::generate();
        $parent = $this->createPersonWithAge(35);
        $child = $this->createPersonWithAge(5);

        $this->personRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [$parentId, $parent],
                [$childId, $child],
            ]);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findRelationship')
            ->willReturn(null);

        $result = $this->service->validateRelationship($parentId, $childId, RelationshipType::parent());

        $this->assertTrue($result);
    }

    #[Test]
    public function validateRelationship_throws_when_parent_too_young()
    {
        $parentId = PersonId::generate();
        $childId = PersonId::generate();
        $parent = $this->createPersonWithAge(15);
        $child = $this->createPersonWithAge(5);

        $this->personRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [$parentId, $parent],
                [$childId, $child],
            ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A parent must be at least 12 years older');

        $this->service->validateRelationship($parentId, $childId, RelationshipType::parent());
    }

    #[Test]
    public function validateRelationship_throws_for_self_relationship()
    {
        $personId = PersonId::generate();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A person cannot have a relationship with themselves');

        $this->service->validateRelationship($personId, $personId, RelationshipType::sibling());
    }

    #[Test]
    public function validateRelationship_throws_when_person_not_found()
    {
        $personId = PersonId::generate();
        $relatedId = PersonId::generate();

        $this->personRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('One or both persons not found');

        $this->service->validateRelationship($personId, $relatedId, RelationshipType::spouse());
    }

    #[Test]
    public function validateRelationship_validates_grandparent_age_gap()
    {
        $grandparentId = PersonId::generate();
        $grandchildId = PersonId::generate();
        $grandparent = $this->createPersonWithAge(60);
        $grandchild = $this->createPersonWithAge(10);

        $this->personRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [$grandparentId, $grandparent],
                [$grandchildId, $grandchild],
            ]);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findRelationship')
            ->willReturn(null);

        $result = $this->service->validateRelationship($grandparentId, $grandchildId, RelationshipType::grandparent());

        $this->assertTrue($result);
    }

    #[Test]
    public function validateRelationship_throws_when_grandparent_too_young()
    {
        $grandparentId = PersonId::generate();
        $grandchildId = PersonId::generate();
        $grandparent = $this->createPersonWithAge(30);
        $grandchild = $this->createPersonWithAge(10);

        $this->personRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [$grandparentId, $grandparent],
                [$grandchildId, $grandchild],
            ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A grandparent must be at least 24 years older');

        $this->service->validateRelationship($grandparentId, $grandchildId, RelationshipType::grandparent());
    }

    #[Test]
    public function validateRelationship_validates_spouse_minimum_age()
    {
        $person1Id = PersonId::generate();
        $person2Id = PersonId::generate();
        $person1 = $this->createPersonWithAge(25);
        $person2 = $this->createPersonWithAge(30);

        $this->personRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [$person1Id, $person1],
                [$person2Id, $person2],
            ]);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findRelationship')
            ->willReturn(null);

        $result = $this->service->validateRelationship($person1Id, $person2Id, RelationshipType::spouse());

        $this->assertTrue($result);
    }

    #[Test]
    public function validateRelationship_throws_when_spouse_underage()
    {
        $person1Id = PersonId::generate();
        $person2Id = PersonId::generate();
        $person1 = $this->createPersonWithAge(15);
        $person2 = $this->createPersonWithAge(20);

        $this->personRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [$person1Id, $person1],
                [$person2Id, $person2],
            ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Both persons must be at least 16 years old');

        $this->service->validateRelationship($person1Id, $person2Id, RelationshipType::spouse());
    }

    #[Test]
    public function validateRelationship_throws_for_circular_relationship()
    {
        $personId = PersonId::generate();
        $relatedId = PersonId::generate();
        $person = $this->createPersonWithAge(30);
        $related = $this->createPersonWithAge(5);
        $existingRel = Relationship::reconstitute(
            1,
            $relatedId,
            $personId,
            RelationshipType::parent(),
            new \DateTimeImmutable(),
            null
        );

        $this->personRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [$personId, $person],
                [$relatedId, $related],
            ]);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findRelationship')
            ->with($relatedId, $personId)
            ->willReturn($existingRel);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('This relationship would create a circular family tree');

        $this->service->validateRelationship($personId, $relatedId, RelationshipType::parent());
    }

    #[Test]
    public function validateRelationship_skips_age_validation_when_ages_unknown()
    {
        $personId = PersonId::generate();
        $relatedId = PersonId::generate();
        $person = $this->createPersonWithAge(null);
        $related = $this->createPersonWithAge(null);

        $this->personRepo
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnMap([
                [$personId, $person],
                [$relatedId, $related],
            ]);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findRelationship')
            ->willReturn(null);

        $result = $this->service->validateRelationship($personId, $relatedId, RelationshipType::parent());

        $this->assertTrue($result);
    }

    #[Test]
    public function updateRelationship_handles_missing_inverse()
    {
        $personId = PersonId::generate();
        $relatedPersonId = PersonId::generate();
        $existingRel = Relationship::reconstitute(
            1,
            $personId,
            $relatedPersonId,
            RelationshipType::child(),
            new \DateTimeImmutable(),
            null
        );

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($existingRel);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findRelationship')
            ->willReturn(null);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('save');

        $this->service->updateRelationship(1, RelationshipType::ward());
    }

    #[Test]
    public function deleteRelationship_handles_missing_inverse()
    {
        $personId = PersonId::generate();
        $relatedPersonId = PersonId::generate();
        $existingRel = Relationship::reconstitute(
            1,
            $personId,
            $relatedPersonId,
            RelationshipType::sibling(),
            new \DateTimeImmutable(),
            null
        );

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($existingRel);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('findRelationship')
            ->willReturn(null);

        $this->relationshipRepo
            ->expects($this->once())
            ->method('delete');

        $this->service->deleteRelationship(1);
    }

    private function createPersonWithAge(?int $age): Person
    {
        $approxAge = $age !== null ? ApproximateAge::fromInt($age) : null;
        return Person::create(
            'family-1',
            PersonName::fromString('Test'),
            Slug::fromString('test'),
            1,
            null,
            null,
            $approxAge
        );
    }
}
