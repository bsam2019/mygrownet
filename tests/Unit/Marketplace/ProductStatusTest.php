<?php

namespace Tests\Unit\Marketplace;

use App\Domain\Marketplace\ValueObjects\ProductStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductStatusTest extends TestCase
{
    #[Test]
    public function creates_via_named_constructors(): void
    {
        $this->assertTrue(ProductStatus::draft()->isDraft());
        $this->assertTrue(ProductStatus::pending()->isPending());
        $this->assertTrue(ProductStatus::active()->isActive());
        $this->assertTrue(ProductStatus::rejected()->isRejected());
        $this->assertTrue(ProductStatus::suspended()->isSuspended());
    }

    #[Test]
    public function creates_from_string(): void
    {
        $this->assertTrue(ProductStatus::fromString('active')->isActive());
    }

    #[Test]
    public function labels_are_correct(): void
    {
        $this->assertEquals('Draft', ProductStatus::draft()->label());
        $this->assertEquals('Pending Review', ProductStatus::pending()->label());
        $this->assertEquals('Active', ProductStatus::active()->label());
        $this->assertEquals('Rejected', ProductStatus::rejected()->label());
        $this->assertEquals('Suspended', ProductStatus::suspended()->label());
    }

    #[Test]
    public function colors_are_correct(): void
    {
        $this->assertEquals('gray', ProductStatus::draft()->color());
        $this->assertEquals('yellow', ProductStatus::pending()->color());
        $this->assertEquals('green', ProductStatus::active()->color());
        $this->assertEquals('red', ProductStatus::rejected()->color());
        $this->assertEquals('orange', ProductStatus::suspended()->color());
    }

    #[Test]
    public function rejects_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProductStatus::fromString('bogus');
    }
}
