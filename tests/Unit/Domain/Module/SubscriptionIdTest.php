<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\ValueObjects\SubscriptionId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SubscriptionIdTest extends TestCase
{
    #[Test]
    public function fromInt_creates_vo()
    {
        $id = SubscriptionId::fromInt(42);

        $this->assertEquals(42, $id->value());
    }

    #[Test]
    public function fromString_creates_vo()
    {
        $id = SubscriptionId::fromString('42');

        $this->assertEquals(42, $id->value());
    }

    #[Test]
    public function fromInt_throws_on_zero()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription ID must be positive');

        SubscriptionId::fromInt(0);
    }

    #[Test]
    public function fromInt_throws_on_negative()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription ID must be positive');

        SubscriptionId::fromInt(-1);
    }

    #[Test]
    public function equals_returns_true_for_same_value()
    {
        $a = SubscriptionId::fromInt(1);
        $b = SubscriptionId::fromInt(1);

        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function equals_returns_false_for_different_value()
    {
        $a = SubscriptionId::fromInt(1);
        $b = SubscriptionId::fromInt(2);

        $this->assertFalse($a->equals($b));
    }
}
