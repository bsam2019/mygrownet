<?php

namespace Tests\Unit\Marketplace;

use App\Domain\Marketplace\ValueObjects\EscrowStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EscrowStatusTest extends TestCase
{
    #[Test]
    public function creates_via_named_constructors(): void
    {
        $this->assertTrue(EscrowStatus::held()->isHeld());
        $this->assertTrue(EscrowStatus::released()->isReleased());
        $this->assertTrue(EscrowStatus::refunded()->isRefunded());
        $this->assertTrue(EscrowStatus::disputed()->isDisputed());
    }

    #[Test]
    public function creates_from_string(): void
    {
        $this->assertTrue(EscrowStatus::fromString('released')->isReleased());
    }

    #[Test]
    public function labels_are_correct(): void
    {
        $this->assertEquals('Funds Held', EscrowStatus::held()->label());
        $this->assertEquals('Funds Released', EscrowStatus::released()->label());
        $this->assertEquals('Refunded', EscrowStatus::refunded()->label());
        $this->assertEquals('Under Dispute', EscrowStatus::disputed()->label());
    }

    #[Test]
    public function colors_are_correct(): void
    {
        $this->assertEquals('blue', EscrowStatus::held()->color());
        $this->assertEquals('green', EscrowStatus::released()->color());
        $this->assertEquals('orange', EscrowStatus::refunded()->color());
        $this->assertEquals('red', EscrowStatus::disputed()->color());
    }

    #[Test]
    public function rejects_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);
        EscrowStatus::fromString('unknown');
    }
}
