<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\TaxReturn;
use App\Domain\GrowFinance\ValueObjects\TaxReturnStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TaxReturnTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $tr = new TaxReturn(
            id: 1, businessId: 5, returnType: 'VAT',
            periodLabel: 'January 2026',
            periodStart: new DateTimeImmutable('2026-01-01'),
            periodEnd: new DateTimeImmutable('2026-01-31'),
            dueDate: new DateTimeImmutable('2026-02-15'),
            outputVat: 5000.0, inputVat: 3000.0, netVatPayable: 2000.0,
            totalSales: 31250.0, totalPurchases: 18750.0,
            withholdingCollected: 0.0, withholdingPaid: 0.0,
            status: TaxReturnStatus::DRAFT,
        );

        $this->assertSame(1, $tr->id);
        $this->assertSame('VAT', $tr->returnType);
        $this->assertSame(2000.0, $tr->netVatPayable);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $tr = TaxReturn::reconstitute([
            'id' => 1, 'business_id' => 5, 'return_type' => 'VAT',
            'period_label' => 'Jan 2026', 'period_start' => '2026-01-01',
            'period_end' => '2026-01-31', 'output_vat' => 5000.0,
            'input_vat' => 3000.0, 'net_vat_payable' => 2000.0,
            'status' => 'filed',
        ]);

        $this->assertSame(TaxReturnStatus::FILED, $tr->status);
        $this->assertSame(2000.0, $tr->netVatPayable);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $tr = new TaxReturn(
            id: 1, businessId: 5, returnType: 'VAT',
            periodLabel: 'Jan 2026',
            periodStart: new DateTimeImmutable('2026-01-01'),
            periodEnd: new DateTimeImmutable('2026-01-31'),
            outputVat: 5000.0, inputVat: 3000.0, netVatPayable: 2000.0,
            totalSales: 0.0, totalPurchases: 0.0,
            withholdingCollected: 0.0, withholdingPaid: 0.0,
            status: TaxReturnStatus::DRAFT,
        );
        $array = $tr->toArray();

        $this->assertSame('VAT', $array['return_type']);
        $this->assertSame('draft', $array['status']);
    }
}
