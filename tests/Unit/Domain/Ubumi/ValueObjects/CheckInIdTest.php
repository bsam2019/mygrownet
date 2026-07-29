<?php

namespace Tests\Unit\Domain\Ubumi\ValueObjects;

use App\Domain\Ubumi\ValueObjects\CheckInId;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CheckInIdTest extends TestCase
{
    #[Test]
    public function generate_creates_valid_uuid()
    {
        $id = CheckInId::generate();
        $this->assertInstanceOf(CheckInId::class, $id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id->value());
    }

    #[Test]
    public function generate_creates_unique_ids()
    {
        $id1 = CheckInId::generate();
        $id2 = CheckInId::generate();
        $this->assertNotEquals($id1->value(), $id2->value());
    }

    #[Test]
    public function fromString_creates_from_valid_uuid()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = CheckInId::fromString($uuid);
        $this->assertInstanceOf(CheckInId::class, $id);
        $this->assertEquals($uuid, $id->value());
    }

    #[Test]
    public function fromString_throws_for_invalid_uuid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid CheckIn ID format');
        CheckInId::fromString('not-a-uuid');
    }

    #[Test]
    public function value_returns_string()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = CheckInId::fromString($uuid);
        $this->assertIsString($id->value());
    }

    #[Test]
    public function equals_returns_true_for_same_id()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id1 = CheckInId::fromString($uuid);
        $id2 = CheckInId::fromString($uuid);
        $this->assertTrue($id1->equals($id2));
    }

    #[Test]
    public function equals_returns_false_for_different_id()
    {
        $id1 = CheckInId::generate();
        $id2 = CheckInId::generate();
        $this->assertFalse($id1->equals($id2));
    }

    #[Test]
    public function toString_returns_uuid_string()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id = CheckInId::fromString($uuid);
        $this->assertEquals($uuid, (string) $id);
    }
}
