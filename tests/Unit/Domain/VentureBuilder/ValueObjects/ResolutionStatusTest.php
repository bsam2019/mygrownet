<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\ValueObjects;

use App\Domain\VentureBuilder\ValueObjects\ResolutionStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResolutionStatusTest extends TestCase
{
    #[Test]
    public function draft_creates_correct_status(): void
    {
        $status = ResolutionStatus::draft();
        $this->assertSame('draft', $status->value());
        $this->assertTrue($status->isDraft());
        $this->assertFalse($status->isVoting());
    }

    #[Test]
    public function voting_creates_correct_status(): void
    {
        $status = ResolutionStatus::voting();
        $this->assertSame('voting', $status->value());
        $this->assertTrue($status->isVoting());
        $this->assertFalse($status->isDraft());
    }

    #[Test]
    public function passed_creates_correct_status(): void
    {
        $status = ResolutionStatus::passed();
        $this->assertSame('passed', $status->value());
        $this->assertFalse($status->isVoting());
    }

    #[Test]
    public function rejected_creates_correct_status(): void
    {
        $status = ResolutionStatus::rejected();
        $this->assertSame('rejected', $status->value());
        $this->assertFalse($status->isVoting());
    }

    #[Test]
    public function from_string_creates_correct_status(): void
    {
        $this->assertSame('passed', ResolutionStatus::fromString('passed')->value());
        $this->assertSame('rejected', ResolutionStatus::fromString('rejected')->value());
    }

    #[Test]
    public function from_string_throws_for_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ResolutionStatus::fromString('unknown');
    }
}
