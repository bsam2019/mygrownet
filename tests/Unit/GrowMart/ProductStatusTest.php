<?php

namespace Tests\Unit\GrowMart;

use App\Domain\GrowMart\ValueObjects\ProductStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductStatusTest extends TestCase
{
    #[Test]
    public function active_status(): void
    {
        $status = ProductStatus::active();
        $this->assertEquals('active', $status->value());
        $this->assertTrue($status->isActive());
        $this->assertFalse($status->isOutOfStock());
        $this->assertFalse($status->isDiscontinued());
    }

    #[Test]
    public function out_of_stock_status(): void
    {
        $status = ProductStatus::outOfStock();
        $this->assertEquals('out_of_stock', $status->value());
        $this->assertTrue($status->isOutOfStock());
        $this->assertFalse($status->isActive());
    }

    #[Test]
    public function discontinued_status(): void
    {
        $status = ProductStatus::discontinued();
        $this->assertEquals('discontinued', $status->value());
        $this->assertTrue($status->isDiscontinued());
        $this->assertFalse($status->isActive());
    }

    #[Test]
    public function from_string_active(): void
    {
        $this->assertTrue(ProductStatus::fromString('active')->isActive());
    }

    #[Test]
    public function from_string_out_of_stock(): void
    {
        $this->assertTrue(ProductStatus::fromString('out_of_stock')->isOutOfStock());
    }

    #[Test]
    public function from_string_discontinued(): void
    {
        $this->assertTrue(ProductStatus::fromString('discontinued')->isDiscontinued());
    }

    #[Test]
    public function from_string_defaults_to_active(): void
    {
        $this->assertTrue(ProductStatus::fromString('unknown')->isActive());
    }

    #[Test]
    public function label_returns_human_readable_name(): void
    {
        $this->assertEquals('Active', ProductStatus::active()->label());
        $this->assertEquals('Out of Stock', ProductStatus::outOfStock()->label());
        $this->assertEquals('Discontinued', ProductStatus::discontinued()->label());
    }

    #[Test]
    public function color_returns_css_color_string(): void
    {
        $this->assertEquals('green', ProductStatus::active()->color());
        $this->assertEquals('yellow', ProductStatus::outOfStock()->color());
        $this->assertEquals('red', ProductStatus::discontinued()->color());
    }
}
