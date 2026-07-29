<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\TaxRate;
use App\Domain\GrowFinance\ValueObjects\TaxType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TaxRateTest extends TestCase
{
    private TaxRate $taxRate;

    protected function setUp(): void
    {
        $this->taxRate = new TaxRate(
            id: 1, businessId: 5, name: 'VAT 16%',
            taxType: TaxType::VAT, rate: 16.0,
            effectiveFrom: new DateTimeImmutable('2023-01-01'),
            effectiveTo: new DateTimeImmutable('2026-12-31'),
            jurisdiction: 'ZM', isDefault: true, isActive: true,
            createdAt: null, updatedAt: null,
        );
    }

    #[Test]
    public function constructor_sets_properties()
    {
        $this->assertSame(1, $this->taxRate->id);
        $this->assertSame(TaxType::VAT, $this->taxRate->taxType);
        $this->assertSame(16.0, $this->taxRate->rate);
    }

    #[Test]
    public function is_effective_for_returns_true_within_range()
    {
        $this->assertTrue($this->taxRate->isEffectiveFor(new DateTimeImmutable('2025-01-01')));
    }

    #[Test]
    public function is_effective_for_returns_false_before_effective_from()
    {
        $this->assertFalse($this->taxRate->isEffectiveFor(new DateTimeImmutable('2020-01-01')));
    }

    #[Test]
    public function is_effective_for_returns_false_after_effective_to()
    {
        $this->assertFalse($this->taxRate->isEffectiveFor(new DateTimeImmutable('2030-01-01')));
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $tr = TaxRate::reconstitute([
            'id' => 1, 'business_id' => 5, 'name' => 'VAT 16%',
            'tax_type' => 'vat', 'rate' => 16.0,
            'effective_from' => '2023-01-01',
        ]);

        $this->assertSame(16.0, $tr->rate);
        $this->assertSame(TaxType::VAT, $tr->taxType);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $array = $this->taxRate->toArray();

        $this->assertSame('vat', $array['tax_type']);
        $this->assertSame(16.0, $array['rate']);
    }

    #[Test]
    public function is_effective_for_returns_true_when_no_effective_to()
    {
        $tr = new TaxRate(id: 2, businessId: 5, name: 'Perm', taxType: TaxType::SALES_TAX, rate: 5.0, effectiveFrom: new DateTimeImmutable('2023-01-01'), effectiveTo: null);
        $this->assertTrue($tr->isEffectiveFor(new DateTimeImmutable('2030-01-01')));
    }
}
