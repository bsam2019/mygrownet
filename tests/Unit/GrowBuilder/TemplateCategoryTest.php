<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\ValueObjects\TemplateCategory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TemplateCategoryTest extends TestCase
{
    public function test_all_categories_have_static_constructors(): void
    {
        $this->assertEquals('business', TemplateCategory::business()->value());
        $this->assertEquals('restaurant', TemplateCategory::restaurant()->value());
        $this->assertEquals('church', TemplateCategory::church()->value());
        $this->assertEquals('tutor', TemplateCategory::tutor()->value());
        $this->assertEquals('portfolio', TemplateCategory::portfolio()->value());
        $this->assertEquals('salon', TemplateCategory::salon()->value());
        $this->assertEquals('shop', TemplateCategory::shop()->value());
        $this->assertEquals('service', TemplateCategory::service()->value());
    }

    public function test_from_string_valid(): void
    {
        $this->assertTrue(TemplateCategory::fromString('business')->equals(TemplateCategory::business()));
    }

    public function test_from_string_invalid_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TemplateCategory::fromString('invalid_category');
    }

    public function test_labels(): void
    {
        $this->assertEquals('Business', TemplateCategory::business()->label());
        $this->assertEquals('Restaurant & Food', TemplateCategory::restaurant()->label());
        $this->assertEquals('Shop & Retail', TemplateCategory::shop()->label());
        $this->assertEquals('Service Provider', TemplateCategory::service()->label());
    }

    public function test_all_returns_all_categories(): void
    {
        $all = TemplateCategory::all();
        $this->assertCount(8, $all);
        foreach ($all as $cat) {
            $this->assertInstanceOf(TemplateCategory::class, $cat);
        }
    }

    public function test_all_with_labels(): void
    {
        $labels = TemplateCategory::allWithLabels();
        $this->assertCount(8, $labels);
        $this->assertEquals('Business', $labels['business']);
    }

    public function test_equals(): void
    {
        $this->assertTrue(TemplateCategory::shop()->equals(TemplateCategory::shop()));
        $this->assertFalse(TemplateCategory::shop()->equals(TemplateCategory::salon()));
    }
}
