<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\ValueObjects\Theme;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    public function test_create_with_defaults(): void
    {
        $theme = Theme::create();
        $this->assertEquals('#2563eb', $theme->getPrimaryColor());
        $this->assertEquals('#64748b', $theme->getSecondaryColor());
        $this->assertEquals('#059669', $theme->getAccentColor());
        $this->assertEquals('#ffffff', $theme->getBackgroundColor());
        $this->assertEquals('#1f2937', $theme->getTextColor());
        $this->assertEquals('Inter', $theme->getHeadingFont());
        $this->assertEquals('Inter', $theme->getBodyFont());
        $this->assertEquals(8, $theme->getBorderRadius());
    }

    public function test_create_with_custom_values(): void
    {
        $theme = Theme::create(
            primaryColor: '#ff0000',
            secondaryColor: '#00ff00',
            accentColor: '#0000ff',
            backgroundColor: '#000000',
            textColor: '#ffffff',
            headingFont: 'Roboto',
            bodyFont: 'Open Sans',
            borderRadius: 4,
        );
        $this->assertEquals('#ff0000', $theme->getPrimaryColor());
        $this->assertEquals('#00ff00', $theme->getSecondaryColor());
        $this->assertEquals('#0000ff', $theme->getAccentColor());
        $this->assertEquals('#000000', $theme->getBackgroundColor());
        $this->assertEquals('#ffffff', $theme->getTextColor());
        $this->assertEquals('Roboto', $theme->getHeadingFont());
        $this->assertEquals('Open Sans', $theme->getBodyFont());
        $this->assertEquals(4, $theme->getBorderRadius());
    }

    public function test_from_array(): void
    {
        $theme = Theme::fromArray([
            'primaryColor' => '#ff6600',
            'headingFont' => 'Poppins',
        ]);
        $this->assertEquals('#ff6600', $theme->getPrimaryColor());
        $this->assertEquals('#64748b', $theme->getSecondaryColor()); // default
        $this->assertEquals('Poppins', $theme->getHeadingFont());
        $this->assertEquals('Inter', $theme->getBodyFont()); // default
    }

    public function test_to_array(): void
    {
        $theme = Theme::create(primaryColor: '#111111');
        $arr = $theme->toArray();
        $this->assertEquals('#111111', $arr['primaryColor']);
        $this->assertArrayHasKey('primaryColor', $arr);
        $this->assertArrayHasKey('borderRadius', $arr);
    }

    public function test_with_primary_color_returns_new_instance(): void
    {
        $original = Theme::create();
        $modified = $original->withPrimaryColor('#ff0000');

        $this->assertEquals('#2563eb', $original->getPrimaryColor());
        $this->assertEquals('#ff0000', $modified->getPrimaryColor());
        $this->assertEquals($original->getSecondaryColor(), $modified->getSecondaryColor());
    }

    public function test_border_radius_default(): void
    {
        $theme = Theme::create(borderRadius: 0);
        $this->assertEquals(0, $theme->getBorderRadius());
    }

    public function test_empty_array_returns_defaults(): void
    {
        $theme = Theme::fromArray([]);
        $this->assertEquals('#2563eb', $theme->getPrimaryColor());
        $this->assertEquals(8, $theme->getBorderRadius());
    }
}
