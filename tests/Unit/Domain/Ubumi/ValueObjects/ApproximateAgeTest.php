<?php

namespace Tests\Unit\Domain\Ubumi\ValueObjects;

use App\Domain\Ubumi\ValueObjects\ApproximateAge;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ApproximateAgeTest extends TestCase
{
    #[Test]
    public function fromInt_creates_with_zero()
    {
        $age = ApproximateAge::fromInt(0);
        $this->assertInstanceOf(ApproximateAge::class, $age);
    }

    #[Test]
    public function fromInt_creates_with_valid_age()
    {
        $age = ApproximateAge::fromInt(25);
        $this->assertInstanceOf(ApproximateAge::class, $age);
    }

    #[Test]
    public function fromInt_creates_with_maximum_age()
    {
        $age = ApproximateAge::fromInt(150);
        $this->assertInstanceOf(ApproximateAge::class, $age);
    }

    #[Test]
    public function fromInt_throws_for_negative_age()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Age cannot be negative');
        ApproximateAge::fromInt(-1);
    }

    #[Test]
    public function fromInt_throws_for_age_exceeding_150()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Age cannot exceed 150 years');
        ApproximateAge::fromInt(151);
    }

    #[Test]
    public function getValue_returns_original_value()
    {
        $age = ApproximateAge::fromInt(42);
        $this->assertEquals(42, $age->getValue());
    }

    #[Test]
    public function equals_returns_true_for_same_value()
    {
        $age1 = ApproximateAge::fromInt(30);
        $age2 = ApproximateAge::fromInt(30);
        $this->assertTrue($age1->equals($age2));
    }

    #[Test]
    public function equals_returns_false_for_different_value()
    {
        $age1 = ApproximateAge::fromInt(30);
        $age2 = ApproximateAge::fromInt(31);
        $this->assertFalse($age1->equals($age2));
    }
}
