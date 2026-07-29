<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\QuotationItem;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class QuotationItemTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $item = new QuotationItem(id: 1, quotationId: 10, description: 'Widget', quantity: 5.0, unitPrice: 200.0, taxRate: 16.0, discountRate: 0.0, lineTotal: 1000.0, createdAt: null, updatedAt: null);

        $this->assertSame(1, $item->id);
        $this->assertSame(10, $item->quotationId);
        $this->assertSame('Widget', $item->description);
        $this->assertSame(5.0, $item->quantity);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $item = QuotationItem::reconstitute([
            'id' => 1, 'quotation_id' => 10, 'description' => 'Service',
            'quantity' => 2, 'unit_price' => 300.0, 'line_total' => 600.0,
        ]);

        $this->assertSame('Service', $item->description);
        $this->assertSame(600.0, $item->lineTotal);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $item = new QuotationItem(id: 1, quotationId: 10, description: 'Test', quantity: 1.0, unitPrice: 100.0, taxRate: 0.0, discountRate: 0.0, lineTotal: 100.0, createdAt: null, updatedAt: null);
        $array = $item->toArray();

        $this->assertSame(10, $array['quotation_id']);
        $this->assertSame(100.0, $array['line_total']);
    }
}
