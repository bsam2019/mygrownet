<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\InvoiceItem;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InvoiceItemTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $item = new InvoiceItem(id: 1, invoiceId: 10, description: 'Service Fee', quantity: 2.0, unitPrice: 100.0, taxRate: 16.0, discountRate: 0.0, lineTotal: 200.0, createdAt: null, updatedAt: null);

        $this->assertSame(1, $item->id);
        $this->assertSame(10, $item->invoiceId);
        $this->assertSame('Service Fee', $item->description);
        $this->assertSame(2.0, $item->quantity);
        $this->assertSame(100.0, $item->unitPrice);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $item = InvoiceItem::reconstitute([
            'id' => 1, 'invoice_id' => 10, 'description' => 'Item',
            'quantity' => 1, 'unit_price' => 500.0, 'line_total' => 500.0,
        ]);

        $this->assertSame('Item', $item->description);
        $this->assertSame(500.0, $item->lineTotal);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $item = new InvoiceItem(id: 1, invoiceId: 10, description: 'Test', quantity: 1.0, unitPrice: 100.0, taxRate: 0.0, discountRate: 0.0, lineTotal: 100.0, createdAt: null, updatedAt: null);
        $array = $item->toArray();

        $this->assertSame(10, $array['invoice_id']);
        $this->assertSame(100.0, $array['line_total']);
    }
}
