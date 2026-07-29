<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\InvoiceTemplate;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InvoiceTemplateTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $tpl = new InvoiceTemplate(
            id: 1, businessId: 5, name: 'Standard', slug: 'standard',
            description: 'Default template', layout: 'modern',
            colors: ['primary' => '#000'], fonts: ['body' => 'Arial'],
            logoPosition: 'left', showLogo: true, showWatermark: false,
            headerText: 'Header', footerText: 'Footer', termsText: 'Terms',
            customFields: null, isDefault: true, isActive: true,
            createdAt: null, updatedAt: null,
        );

        $this->assertSame('Standard', $tpl->name);
        $this->assertSame('standard', $tpl->slug);
        $this->assertTrue($tpl->isDefault);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $tpl = InvoiceTemplate::reconstitute([
            'id' => 1, 'business_id' => 5, 'name' => 'Standard',
            'slug' => 'standard', 'is_default' => true, 'is_active' => true,
        ]);

        $this->assertSame('Standard', $tpl->name);
        $this->assertTrue($tpl->isDefault);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $tpl = new InvoiceTemplate(
            id: 1, businessId: 5, name: 'Simple', slug: 'simple',
            description: null, layout: null, colors: null, fonts: null,
            logoPosition: null, showLogo: false, showWatermark: false,
            headerText: null, footerText: null, termsText: null,
            customFields: null, isDefault: false, isActive: true,
            createdAt: null, updatedAt: null,
        );
        $array = $tpl->toArray();

        $this->assertSame('Simple', $array['name']);
        $this->assertFalse($array['is_default']);
    }
}
