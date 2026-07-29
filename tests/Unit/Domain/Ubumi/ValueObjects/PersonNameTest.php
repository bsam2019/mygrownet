<?php

namespace Tests\Unit\Domain\Ubumi\ValueObjects;

use App\Domain\Ubumi\ValueObjects\PersonName;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PersonNameTest extends TestCase
{
    #[Test]
    public function fromString_creates_with_valid_name()
    {
        $name = PersonName::fromString('Alice');
        $this->assertInstanceOf(PersonName::class, $name);
    }

    #[Test]
    public function fromString_creates_with_full_name()
    {
        $name = PersonName::fromString('Alice Banda');
        $this->assertInstanceOf(PersonName::class, $name);
    }

    #[Test]
    public function fromString_throws_for_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Person name cannot be empty');
        PersonName::fromString('');
    }

    #[Test]
    public function fromString_throws_for_whitespace_only()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Person name cannot be empty');
        PersonName::fromString('   ');
    }

    #[Test]
    public function fromString_throws_for_too_long_name()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Person name cannot exceed 255 characters');
        PersonName::fromString(str_repeat('a', 256));
    }

    #[Test]
    public function fromString_accepts_255_characters()
    {
        $name = PersonName::fromString(str_repeat('a', 255));
        $this->assertInstanceOf(PersonName::class, $name);
    }

    #[Test]
    public function toString_returns_original_value()
    {
        $name = PersonName::fromString('Alice');
        $this->assertEquals('Alice', $name->toString());
    }

    #[Test]
    public function equals_returns_true_for_same_name()
    {
        $name1 = PersonName::fromString('Alice');
        $name2 = PersonName::fromString('Alice');
        $this->assertTrue($name1->equals($name2));
    }

    #[Test]
    public function equals_returns_false_for_different_name()
    {
        $name1 = PersonName::fromString('Alice');
        $name2 = PersonName::fromString('Bob');
        $this->assertFalse($name1->equals($name2));
    }
}
