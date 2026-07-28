<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Domain\GrowBuilder\ValueObjects\ProductId;
use App\Domain\GrowBuilder\ValueObjects\PageId;
use App\Domain\GrowBuilder\ValueObjects\OrderId;
use App\Domain\GrowBuilder\ValueObjects\TemplateId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TypedIdTest extends TestCase
{
    public function test_site_id(): void
    {
        $id = SiteId::fromInt(42);
        $this->assertEquals(42, $id->value());
        $this->assertTrue($id->equals(SiteId::fromInt(42)));
        $this->assertFalse($id->equals(SiteId::fromInt(99)));
    }

    public function test_site_id_invalid_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SiteId::fromInt(0);
    }

    public function test_site_id_invalid_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SiteId::fromInt(-1);
    }

    public function test_product_id(): void
    {
        $id = ProductId::fromInt(1);
        $this->assertEquals(1, $id->value());
        $this->assertTrue($id->equals(ProductId::fromInt(1)));
    }

    public function test_product_id_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProductId::fromInt(0);
    }

    public function test_page_id(): void
    {
        $id = PageId::fromInt(7);
        $this->assertEquals(7, $id->value());
        $this->assertTrue($id->equals(PageId::fromInt(7)));
    }

    public function test_page_id_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PageId::fromInt(-5);
    }

    public function test_order_id(): void
    {
        $id = OrderId::fromInt(100);
        $this->assertEquals(100, $id->value());
    }

    public function test_order_id_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OrderId::fromInt(0);
    }

    public function test_template_id(): void
    {
        $id = TemplateId::fromInt(3);
        $this->assertEquals(3, $id->value());
    }

    public function test_template_id_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TemplateId::fromInt(0);
    }
}
