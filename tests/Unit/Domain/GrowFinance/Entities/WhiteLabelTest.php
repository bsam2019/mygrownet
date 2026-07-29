<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\WhiteLabel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WhiteLabelTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $wl = new WhiteLabel(
            id: 1, businessId: 5, companyName: 'My Brand',
            logoPath: '/logos/logo.png', faviconPath: '/favicon.ico',
            primaryColor: '#3490dc', secondaryColor: '#ffed4a',
            accentColor: '#e3342f', customDomain: 'mybrand.com',
            hidePoweredBy: true, customCss: null, emailBranding: null,
            createdAt: null, updatedAt: null,
        );

        $this->assertSame(1, $wl->id);
        $this->assertSame('My Brand', $wl->companyName);
        $this->assertSame('#3490dc', $wl->primaryColor);
    }

    #[Test]
    public function get_display_name_returns_company_name()
    {
        $wl = new WhiteLabel(id: 1, businessId: 5, companyName: 'Brand', logoPath: null, faviconPath: null, primaryColor: null, secondaryColor: null, accentColor: null, customDomain: null, hidePoweredBy: false, customCss: null, emailBranding: null, createdAt: null, updatedAt: null);
        $this->assertSame('Brand', $wl->getDisplayName());
    }

    #[Test]
    public function get_display_name_returns_fallback()
    {
        $wl = new WhiteLabel(id: 1, businessId: 5, companyName: null, logoPath: null, faviconPath: null, primaryColor: null, secondaryColor: null, accentColor: null, customDomain: null, hidePoweredBy: false, customCss: null, emailBranding: null, createdAt: null, updatedAt: null);
        $this->assertSame('My Business', $wl->getDisplayName());
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $wl = WhiteLabel::reconstitute([
            'id' => 1, 'business_id' => 5, 'company_name' => 'Brand',
            'hide_powered_by' => true,
        ]);

        $this->assertSame('Brand', $wl->companyName);
        $this->assertTrue($wl->hidePoweredBy);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $wl = new WhiteLabel(id: 1, businessId: 5, companyName: 'Brand', logoPath: null, faviconPath: null, primaryColor: null, secondaryColor: null, accentColor: null, customDomain: null, hidePoweredBy: false, customCss: null, emailBranding: null, createdAt: null, updatedAt: null);
        $array = $wl->toArray();

        $this->assertSame('Brand', $array['company_name']);
        $this->assertSame(false, $array['hide_powered_by']);
    }
}
