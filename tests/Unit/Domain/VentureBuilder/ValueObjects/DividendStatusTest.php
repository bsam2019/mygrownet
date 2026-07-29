<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\ValueObjects;

use App\Domain\VentureBuilder\ValueObjects\DividendStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DividendStatusTest extends TestCase
{
    #[Test]
    public function declared_creates_correct_status(): void
    {
        $status = DividendStatus::declared();
        $this->assertSame('declared', $status->value());
        $this->assertTrue($status->isDeclared());
        $this->assertFalse($status->isPaid());
    }

    #[Test]
    public function paid_creates_correct_status(): void
    {
        $status = DividendStatus::paid();
        $this->assertSame('paid', $status->value());
        $this->assertTrue($status->isPaid());
        $this->assertFalse($status->isDeclared());
    }

    #[Test]
    public function from_string_creates_correct_status(): void
    {
        $this->assertSame('declared', DividendStatus::fromString('declared')->value());
        $this->assertSame('paid', DividendStatus::fromString('paid')->value());
    }

    #[Test]
    public function from_string_throws_for_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DividendStatus::fromString('unknown');
    }
}
