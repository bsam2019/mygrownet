<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\ValueObjects;

use App\Domain\VentureBuilder\ValueObjects\TransferStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TransferStatusTest extends TestCase
{
    #[Test]
    public function pending_creates_correct_status(): void
    {
        $status = TransferStatus::pending();
        $this->assertSame('pending', $status->value());
        $this->assertTrue($status->isPending());
    }

    #[Test]
    public function approved_creates_correct_status(): void
    {
        $status = TransferStatus::approved();
        $this->assertSame('approved', $status->value());
        $this->assertFalse($status->isPending());
    }

    #[Test]
    public function rejected_creates_correct_status(): void
    {
        $status = TransferStatus::rejected();
        $this->assertSame('rejected', $status->value());
        $this->assertFalse($status->isPending());
    }

    #[Test]
    public function from_string_creates_correct_status(): void
    {
        $this->assertSame('pending', TransferStatus::fromString('pending')->value());
        $this->assertSame('approved', TransferStatus::fromString('approved')->value());
        $this->assertSame('rejected', TransferStatus::fromString('rejected')->value());
    }

    #[Test]
    public function from_string_throws_for_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TransferStatus::fromString('unknown');
    }
}
