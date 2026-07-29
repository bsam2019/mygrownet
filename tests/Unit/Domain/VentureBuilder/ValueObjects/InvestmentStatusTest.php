<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\ValueObjects;

use App\Domain\VentureBuilder\ValueObjects\InvestmentStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvestmentStatusTest extends TestCase
{
    #[Test]
    public function pending_creates_correct_status(): void
    {
        $status = InvestmentStatus::pending();
        $this->assertSame('pending', $status->value());
        $this->assertTrue($status->isPending());
        $this->assertTrue($status->canBeCancelled());
        $this->assertFalse($status->isConfirmed());
    }

    #[Test]
    public function processing_creates_correct_status(): void
    {
        $status = InvestmentStatus::processing();
        $this->assertSame('processing', $status->value());
        $this->assertTrue($status->canBeCancelled());
        $this->assertFalse($status->isConfirmed());
    }

    #[Test]
    public function confirmed_creates_correct_status(): void
    {
        $status = InvestmentStatus::confirmed();
        $this->assertSame('confirmed', $status->value());
        $this->assertTrue($status->isConfirmed());
        $this->assertFalse($status->canBeCancelled());
        $this->assertFalse($status->isPending());
    }

    #[Test]
    public function completed_creates_correct_status(): void
    {
        $status = InvestmentStatus::completed();
        $this->assertSame('completed', $status->value());
        $this->assertTrue($status->isConfirmed());
    }

    #[Test]
    public function refunded_creates_correct_status(): void
    {
        $status = InvestmentStatus::refunded();
        $this->assertSame('refunded', $status->value());
        $this->assertFalse($status->isConfirmed());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->canBeCancelled());
    }

    #[Test]
    public function from_string_creates_correct_status(): void
    {
        $this->assertSame('confirmed', InvestmentStatus::fromString('confirmed')->value());
        $this->assertSame('pending', InvestmentStatus::fromString('pending')->value());
    }

    #[Test]
    public function from_string_throws_for_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InvestmentStatus::fromString('invalid');
    }
}
