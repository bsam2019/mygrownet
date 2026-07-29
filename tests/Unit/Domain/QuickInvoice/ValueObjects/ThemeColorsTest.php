<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\ValueObjects;

use App\Domain\QuickInvoice\ValueObjects\ThemeColors;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ThemeColorsTest extends TestCase
{
    #[Test]
    public function default_returns_expected_colors(): void
    {
        $colors = ThemeColors::default();
        $this->assertSame('#2563eb', $colors->primary());
        $this->assertSame('#1e40af', $colors->secondary());
        $this->assertSame('#059669', $colors->accent());
        $this->assertSame('#1f2937', $colors->text());
        $this->assertSame('#ffffff', $colors->background());
    }

    #[Test]
    public function create_with_only_primary_auto_generates_secondary(): void
    {
        $colors = ThemeColors::create('#ff0000');
        $this->assertSame('#ff0000', $colors->primary());
        $this->assertSame('#cc0000', $colors->secondary());
    }

    #[Test]
    public function create_with_all_fields(): void
    {
        $colors = ThemeColors::create('#111111', '#222222', '#333333', '#444444', '#555555');
        $this->assertSame('#111111', $colors->primary());
        $this->assertSame('#222222', $colors->secondary());
        $this->assertSame('#333333', $colors->accent());
        $this->assertSame('#444444', $colors->text());
        $this->assertSame('#555555', $colors->background());
    }

    #[Test]
    public function create_with_partial_fields_uses_defaults(): void
    {
        $colors = ThemeColors::create('#ff6600', accent: '#00ff00');
        $this->assertSame('#ff6600', $colors->primary());
        $this->assertSame('#cc3300', $colors->secondary());
        $this->assertSame('#00ff00', $colors->accent());
        $this->assertSame('#1f2937', $colors->text());
        $this->assertSame('#ffffff', $colors->background());
    }

    #[Test]
    public function create_invalid_hex_falls_back_to_default(): void
    {
        $colors = ThemeColors::create('not-a-color');
        $this->assertSame('#2563eb', $colors->primary());
    }

    #[Test]
    public function create_invalid_secondary_falls_back_to_default(): void
    {
        $colors = ThemeColors::create('#ff0000', 'bad-color');
        $this->assertSame('#2563eb', $colors->secondary());
    }

    #[Test]
    public function create_lowercases_hex(): void
    {
        $colors = ThemeColors::create('#FFAABB');
        $this->assertSame('#ffaabb', $colors->primary());
    }

    #[Test]
    public function from_array_with_all_keys(): void
    {
        $colors = ThemeColors::fromArray([
            'primary' => '#aabbcc',
            'secondary' => '#ddeeff',
            'accent' => '#112233',
            'text' => '#445566',
            'background' => '#778899',
        ]);
        $this->assertSame('#aabbcc', $colors->primary());
        $this->assertSame('#ddeeff', $colors->secondary());
    }

    #[Test]
    public function from_array_with_partial_keys_uses_defaults(): void
    {
        $colors = ThemeColors::fromArray(['primary' => '#ff0000']);
        $this->assertSame('#ff0000', $colors->primary());
        $this->assertSame('#cc0000', $colors->secondary());
        $this->assertSame('#059669', $colors->accent());
    }

    #[Test]
    public function to_array_returns_correct_structure(): void
    {
        $colors = ThemeColors::default();
        $this->assertSame([
            'primary' => '#2563eb',
            'secondary' => '#1e40af',
            'accent' => '#059669',
            'text' => '#1f2937',
            'background' => '#ffffff',
        ], $colors->toArray());
    }

    #[Test]
    public function to_css_returns_css_variables(): void
    {
        $css = ThemeColors::default()->toCss();
        $this->assertStringContainsString('--color-primary: #2563eb;', $css);
        $this->assertStringContainsString('--color-secondary: #1e40af;', $css);
        $this->assertStringContainsString('--color-accent: #059669;', $css);
        $this->assertStringContainsString('--color-text: #1f2937;', $css);
        $this->assertStringContainsString('--color-background: #ffffff;', $css);
    }

    #[Test]
    public function darken_reduces_brightness(): void
    {
        $colors = ThemeColors::create('#ffffff');
        $this->assertSame('#ffffff', $colors->primary());
        $this->assertSame('#cccccc', $colors->secondary());
    }

    #[Test]
    public function darken_clamps_at_zero(): void
    {
        $colors = ThemeColors::create('#000000');
        $this->assertSame('#000000', $colors->primary());
        $this->assertSame('#000000', $colors->secondary());
    }
}
