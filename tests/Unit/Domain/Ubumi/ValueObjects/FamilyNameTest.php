<?php

namespace Tests\Unit\Domain\Ubumi\ValueObjects;

use App\Domain\Ubumi\ValueObjects\FamilyName;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FamilyNameTest extends TestCase
{
    #[Test]
    public function fromString_creates_with_valid_name()
    {
        $name = FamilyName::fromString('Smith');
        $this->assertInstanceOf(FamilyName::class, $name);
    }

    #[Test]
    public function fromString_creates_with_name_with_spaces()
    {
        $name = FamilyName::fromString('Van der Merwe');
        $this->assertInstanceOf(FamilyName::class, $name);
    }

    #[Test]
    public function fromString_throws_for_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Family name cannot be empty');
        FamilyName::fromString('');
    }

    #[Test]
    public function fromString_throws_for_whitespace_only()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Family name cannot be empty');
        FamilyName::fromString('   ');
    }

    #[Test]
    public function fromString_throws_for_too_long_name()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Family name cannot exceed 255 characters');
        FamilyName::fromString(str_repeat('a', 256));
    }

    #[Test]
    public function fromString_accepts_255_characters()
    {
        $name = FamilyName::fromString(str_repeat('a', 255));
        $this->assertInstanceOf(FamilyName::class, $name);
    }

    #[Test]
    public function toString_returns_original_value()
    {
        $name = FamilyName::fromString('Banda');
        $this->assertEquals('Banda', $name->toString());
    }

    #[Test]
    public function equals_returns_true_for_same_name()
    {
        $name1 = FamilyName::fromString('Smith');
        $name2 = FamilyName::fromString('Smith');
        $this->assertTrue($name1->equals($name2));
    }

    #[Test]
    public function equals_returns_false_for_different_name()
    {
        $name1 = FamilyName::fromString('Smith');
        $name2 = FamilyName::fromString('Jones');
        $this->assertFalse($name1->equals($name2));
    }
}
