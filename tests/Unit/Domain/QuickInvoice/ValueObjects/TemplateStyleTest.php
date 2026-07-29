<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\ValueObjects;

use App\Domain\QuickInvoice\ValueObjects\TemplateStyle;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class TemplateStyleTest extends TestCase
{
    #[Test]
    public function cases_have_expected_values(): void
    {
        $this->assertSame('classic', TemplateStyle::CLASSIC->value);
        $this->assertSame('modern', TemplateStyle::MODERN->value);
        $this->assertSame('minimal', TemplateStyle::MINIMAL->value);
        $this->assertSame('professional', TemplateStyle::PROFESSIONAL->value);
        $this->assertSame('bold', TemplateStyle::BOLD->value);
        $this->assertSame('advanced-classic', TemplateStyle::ADVANCED_CLASSIC->value);
        $this->assertSame('advanced-professional', TemplateStyle::ADVANCED_PROFESSIONAL->value);
        $this->assertSame('advanced-minimal', TemplateStyle::ADVANCED_MINIMAL->value);
    }

    #[Test]
    public function label_returns_readable_name(): void
    {
        $this->assertSame('Classic', TemplateStyle::CLASSIC->label());
        $this->assertSame('Modern', TemplateStyle::MODERN->label());
        $this->assertSame('Minimal', TemplateStyle::MINIMAL->label());
        $this->assertSame('Professional', TemplateStyle::PROFESSIONAL->label());
        $this->assertSame('Bold', TemplateStyle::BOLD->label());
        $this->assertSame('Classic (Advanced)', TemplateStyle::ADVANCED_CLASSIC->label());
        $this->assertSame('Professional Modern (Advanced)', TemplateStyle::ADVANCED_PROFESSIONAL->label());
        $this->assertSame('Minimal (Advanced)', TemplateStyle::ADVANCED_MINIMAL->label());
    }

    #[Test]
    public function description_returns_non_empty_string(): void
    {
        foreach (TemplateStyle::cases() as $case) {
            $this->assertNotEmpty($case->description());
        }
    }

    #[Test]
    public function is_advanced_true_for_advanced_templates(): void
    {
        $this->assertTrue(TemplateStyle::ADVANCED_CLASSIC->isAdvanced());
        $this->assertTrue(TemplateStyle::ADVANCED_PROFESSIONAL->isAdvanced());
        $this->assertTrue(TemplateStyle::ADVANCED_MINIMAL->isAdvanced());
    }

    #[Test]
    public function is_advanced_false_for_standard_templates(): void
    {
        $this->assertFalse(TemplateStyle::CLASSIC->isAdvanced());
        $this->assertFalse(TemplateStyle::MODERN->isAdvanced());
        $this->assertFalse(TemplateStyle::MINIMAL->isAdvanced());
        $this->assertFalse(TemplateStyle::PROFESSIONAL->isAdvanced());
        $this->assertFalse(TemplateStyle::BOLD->isAdvanced());
    }

    #[Test]
    public function preview_image_returns_path(): void
    {
        $this->assertSame('/images/invoice-templates/classic.png', TemplateStyle::CLASSIC->previewImage());
        $this->assertSame('/images/invoice-templates/advanced-minimal.png', TemplateStyle::ADVANCED_MINIMAL->previewImage());
    }

    #[Test]
    public function all_returns_array_for_each_case(): void
    {
        $all = TemplateStyle::all();
        $this->assertCount(8, $all);
        foreach ($all as $entry) {
            $this->assertArrayHasKey('value', $entry);
            $this->assertArrayHasKey('label', $entry);
            $this->assertArrayHasKey('description', $entry);
        }
    }

    #[Test]
    public function from_valid_string(): void
    {
        $this->assertEquals(TemplateStyle::MODERN, TemplateStyle::from('modern'));
    }

    #[Test]
    public function try_from_invalid_returns_null(): void
    {
        $this->assertNull(TemplateStyle::tryFrom('vintage'));
    }
}
