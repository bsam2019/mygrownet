<?php

namespace Tests\Unit\Domain\Ubumi\ValueObjects;

use App\Domain\Ubumi\ValueObjects\FamilyId;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FamilyIdTest extends TestCase
{
    #[Test]
    public function generate_creates_valid_uuid()
    {
        $id = FamilyId::generate();
        $this->assertInstanceOf(FamilyId::class, $id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id->toString());
    }

    #[Test]
    public function generate_creates_unique_ids()
    {
        $id1 = FamilyId::generate();
        $id2 = FamilyId::generate();
        $this->assertNotEquals($id1->toString(), $id2->toString());
    }

    #[Test]
    public function fromString_creates_from_valid_uuid()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = FamilyId::fromString($uuid);
        $this->assertInstanceOf(FamilyId::class, $id);
        $this->assertEquals($uuid, $id->toString());
    }

    #[Test]
    public function fromString_throws_for_invalid_uuid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Family ID format');
        FamilyId::fromString('not-a-uuid');
    }

    #[Test]
    public function toString_returns_uuid_string()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = FamilyId::fromString($uuid);
        $this->assertEquals($uuid, $id->toString());
    }

    #[Test]
    public function equals_returns_true_for_same_id()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id1 = FamilyId::fromString($uuid);
        $id2 = FamilyId::fromString($uuid);
        $this->assertTrue($id1->equals($id2));
    }

    #[Test]
    public function equals_returns_false_for_different_id()
    {
        $id1 = FamilyId::generate();
        $id2 = FamilyId::generate();
        $this->assertFalse($id1->equals($id2));
    }
}
