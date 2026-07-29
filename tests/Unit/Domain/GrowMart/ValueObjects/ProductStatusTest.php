<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowMart\ValueObjects;

use App\Domain\GrowMart\ValueObjects\ProductStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProductStatusTest extends TestCase
{
    #[Test]
    public function active_has_correct_value_and_checks(): void
    {
        $status = ProductStatus::active();
        $this->assertEquals('active', $status->value());
        $this->assertTrue($status->isActive());
        $this->assertFalse($status->isOutOfStock());
        $this->assertFalse($status->isDiscontinued());
    }

    #[Test]
    public function out_of_stock_has_correct_value_and_checks(): void
    {
        $status = ProductStatus::outOfStock();
        $this->assertEquals('out_of_stock', $status->value());
        $this->assertTrue($status->isOutOfStock());
        $this->assertFalse($status->isActive());
        $this->assertFalse($status->isDiscontinued());
    }

    #[Test]
    public function discontinued_has_correct_value_and_checks(): void
    {
        $status = ProductStatus::discontinued();
        $this->assertEquals('discontinued', $status->value());
        $this->assertTrue($status->isDiscontinued());
        $this->assertFalse($status->isActive());
        $this->assertFalse($status->isOutOfStock());
    }

    #[Test]
    public function from_string_returns_active(): void
    {
        $status = ProductStatus::fromString('active');
        $this->assertTrue($status->isActive());
    }

    #[Test]
    public function from_string_returns_out_of_stock(): void
    {
        $status = ProductStatus::fromString('out_of_stock');
        $this->assertTrue($status->isOutOfStock());
    }

    #[Test]
    public function from_string_returns_discontinued(): void
    {
        $status = ProductStatus::fromString('discontinued');
        $this->assertTrue($status->isDiscontinued());
    }

    #[Test]
    public function from_string_defaults_to_active_for_unknown_value(): void
    {
        $status = ProductStatus::fromString('non_existent_status');
        $this->assertTrue($status->isActive());
    }

    #[Test]
    public function from_string_defaults_to_active_for_empty_string(): void
    {
        $status = ProductStatus::fromString('');
        $this->assertTrue($status->isActive());
    }

    #[Test]
    public function label_returns_human_readable_names(): void
    {
        $this->assertEquals('Active', ProductStatus::active()->label());
        $this->assertEquals('Out of Stock', ProductStatus::outOfStock()->label());
        $this->assertEquals('Discontinued', ProductStatus::discontinued()->label());
    }

    #[Test]
    public function label_returns_raw_value_when_not_in_labels_array(): void
    {
        $status = ProductStatus::active();
        $this->assertEquals('Active', $status->label());
    }

    #[Test]
    public function color_returns_css_colors(): void
    {
        $this->assertEquals('green', ProductStatus::active()->color());
        $this->assertEquals('yellow', ProductStatus::outOfStock()->color());
        $this->assertEquals('red', ProductStatus::discontinued()->color());
    }

    #[Test]
    public function identity_of_instances_same_status(): void
    {
        $a = ProductStatus::active();
        $b = ProductStatus::active();
        $this->assertEquals($a->value(), $b->value());
        $this->assertNotSame($a, $b);
    }

    #[Test]
    public function from_string_round_trip(): void
    {
        $original = ProductStatus::outOfStock();
        $recreated = ProductStatus::fromString($original->value());
        $this->assertTrue($recreated->isOutOfStock());
    }
}
