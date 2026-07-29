<?php

namespace Tests\Unit\Marketplace;

use App\Domain\Marketplace\ValueObjects\TrustLevel;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrustLevelTest extends TestCase
{
    #[Test]
    public function creates_via_named_constructors(): void
    {
        $this->assertTrue(TrustLevel::new()->isNew());
        $this->assertTrue(TrustLevel::verified()->isVerified());
        $this->assertTrue(TrustLevel::trusted()->isTrusted());
        $this->assertTrue(TrustLevel::top()->isTop());
    }

    #[Test]
    public function creates_from_string(): void
    {
        $this->assertTrue(TrustLevel::fromString('new')->isNew());
        $this->assertTrue(TrustLevel::fromString('verified')->isVerified());
        $this->assertTrue(TrustLevel::fromString('trusted')->isTrusted());
        $this->assertTrue(TrustLevel::fromString('top')->isTop());
    }

    #[Test]
    public function value_returns_raw_string(): void
    {
        $this->assertEquals('verified', TrustLevel::verified()->value());
    }

    #[Test]
    public function labels_are_correct(): void
    {
        $this->assertEquals('New Seller', TrustLevel::new()->label());
        $this->assertEquals('Verified Seller', TrustLevel::verified()->label());
        $this->assertEquals('Trusted Seller', TrustLevel::trusted()->label());
        $this->assertEquals('Top Seller', TrustLevel::top()->label());
    }

    #[Test]
    public function badges_are_correct(): void
    {
        $this->assertEquals('🆕', TrustLevel::new()->badge());
        $this->assertEquals('✓', TrustLevel::verified()->badge());
        $this->assertEquals('⭐', TrustLevel::trusted()->badge());
        $this->assertEquals('👑', TrustLevel::top()->badge());
    }

    #[Test]
    public function colors_are_correct(): void
    {
        $this->assertEquals('gray', TrustLevel::new()->color());
        $this->assertEquals('blue', TrustLevel::verified()->color());
        $this->assertEquals('green', TrustLevel::trusted()->color());
        $this->assertEquals('amber', TrustLevel::top()->color());
    }

    #[Test]
    public function rejects_invalid_level(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TrustLevel::fromString('invalid');
    }
}
