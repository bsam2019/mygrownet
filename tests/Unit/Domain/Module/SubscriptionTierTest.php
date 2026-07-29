<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\ValueObjects\SubscriptionTier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SubscriptionTierTest extends TestCase
{
    #[Test]
    public function fromString_creates_vo()
    {
        $tier = SubscriptionTier::fromString('premium');

        $this->assertEquals('premium', $tier->value());
    }

    #[Test]
    public function fromString_throws_on_empty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription tier cannot be empty');

        SubscriptionTier::fromString('');
    }

    #[Test]
    public function basic_returns_correct_tier()
    {
        $tier = SubscriptionTier::basic();

        $this->assertEquals('basic', $tier->value());
    }

    #[Test]
    public function pro_returns_correct_tier()
    {
        $tier = SubscriptionTier::pro();

        $this->assertEquals('pro', $tier->value());
    }

    #[Test]
    public function enterprise_returns_correct_tier()
    {
        $tier = SubscriptionTier::enterprise();

        $this->assertEquals('enterprise', $tier->value());
    }

    #[Test]
    public function equals_returns_true_for_same_value()
    {
        $a = SubscriptionTier::fromString('premium');
        $b = SubscriptionTier::fromString('premium');

        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function equals_returns_false_for_different_value()
    {
        $a = SubscriptionTier::basic();
        $b = SubscriptionTier::pro();

        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function toString_returns_value()
    {
        $tier = SubscriptionTier::fromString('enterprise');

        $this->assertEquals('enterprise', (string) $tier);
    }
}
