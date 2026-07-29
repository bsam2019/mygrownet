<?php

namespace Tests\Unit\Domain\Ubumi\ValueObjects;

use App\Domain\Ubumi\ValueObjects\PersonId;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PersonIdTest extends TestCase
{
    #[Test]
    public function generate_creates_valid_uuid()
    {
        $id = PersonId::generate();
        $this->assertInstanceOf(PersonId::class, $id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id->toString());
    }

    #[Test]
    public function generate_creates_unique_ids()
    {
        $id1 = PersonId::generate();
        $id2 = PersonId::generate();
        $this->assertNotEquals($id1->toString(), $id2->toString());
    }

    #[Test]
    public function fromString_creates_from_valid_uuid()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = PersonId::fromString($uuid);
        $this->assertInstanceOf(PersonId::class, $id);
        $this->assertEquals($uuid, $id->toString());
    }

    #[Test]
    public function fromString_throws_for_invalid_uuid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Person ID format');
        PersonId::fromString('not-a-uuid');
    }

    #[Test]
    public function toString_returns_uuid_string()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = PersonId::fromString($uuid);
        $this->assertEquals($uuid, $id->toString());
    }

    #[Test]
    public function equals_returns_true_for_same_id()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id1 = PersonId::fromString($uuid);
        $id2 = PersonId::fromString($uuid);
        $this->assertTrue($id1->equals($id2));
    }

    #[Test]
    public function equals_returns_false_for_different_id()
    {
        $id1 = PersonId::generate();
        $id2 = PersonId::generate();
        $this->assertFalse($id1->equals($id2));
    }
}
