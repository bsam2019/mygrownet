<?php

namespace Tests\Unit\Marketplace;

use App\Domain\Marketplace\ValueObjects\KycStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KycStatusTest extends TestCase
{
    #[Test]
    public function creates_via_named_constructors(): void
    {
        $this->assertTrue(KycStatus::pending()->isPending());
        $this->assertTrue(KycStatus::approved()->isApproved());
        $this->assertTrue(KycStatus::rejected()->isRejected());
    }

    #[Test]
    public function creates_from_string(): void
    {
        $this->assertTrue(KycStatus::fromString('approved')->isApproved());
    }

    #[Test]
    public function labels_are_correct(): void
    {
        $this->assertEquals('Pending Review', KycStatus::pending()->label());
        $this->assertEquals('Approved', KycStatus::approved()->label());
        $this->assertEquals('Rejected', KycStatus::rejected()->label());
    }

    #[Test]
    public function colors_are_correct(): void
    {
        $this->assertEquals('yellow', KycStatus::pending()->color());
        $this->assertEquals('green', KycStatus::approved()->color());
        $this->assertEquals('red', KycStatus::rejected()->color());
    }

    #[Test]
    public function rejects_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);
        KycStatus::fromString('unknown');
    }
}
