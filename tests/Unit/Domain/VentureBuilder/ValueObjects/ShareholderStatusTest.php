<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\ValueObjects;

use App\Domain\VentureBuilder\ValueObjects\ShareholderStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ShareholderStatusTest extends TestCase
{
    #[Test]
    public function active_creates_correct_status(): void
    {
        $status = ShareholderStatus::active();
        $this->assertSame('active', $status->value());
        $this->assertTrue($status->isActive());
    }

    #[Test]
    public function inactive_creates_correct_status(): void
    {
        $status = ShareholderStatus::inactive();
        $this->assertSame('inactive', $status->value());
        $this->assertFalse($status->isActive());
    }

    #[Test]
    public function removed_creates_correct_status(): void
    {
        $status = ShareholderStatus::removed();
        $this->assertSame('removed', $status->value());
        $this->assertFalse($status->isActive());
    }

    #[Test]
    public function from_string_creates_correct_status(): void
    {
        $this->assertSame('active', ShareholderStatus::fromString('active')->value());
        $this->assertSame('inactive', ShareholderStatus::fromString('inactive')->value());
        $this->assertSame('removed', ShareholderStatus::fromString('removed')->value());
    }

    #[Test]
    public function from_string_throws_for_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ShareholderStatus::fromString('unknown');
    }
}
