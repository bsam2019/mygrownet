<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Entities;

use App\Domain\QuickInvoice\Entities\LineItem;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class LineItemTest extends TestCase
{
    #[Test]
    public function create_sets_description_and_quantity(): void
    {
        $item = LineItem::create('Widget', 5, 10.00);
        $this->assertSame('Widget', $item->description());
        $this->assertSame(5.0, $item->quantity());
    }

    #[Test]
    public function create_calculates_amount(): void
    {
        $item = LineItem::create('Service', 3, 50.00);
        $this->assertSame(150.0, $item->amount()->amount());
    }

    #[Test]
    public function create_with_unit_and_sort_order(): void
    {
        $item = LineItem::create('Hours', 10, 100, 'ZMW', 'hrs', 2);
        $this->assertSame('hrs', $item->unit());
        $this->assertSame(2, $item->sortOrder());
    }

    #[Test]
    public function create_defaults_sort_order_to_zero(): void
    {
        $item = LineItem::create('Item', 1, 10);
        $this->assertSame(0, $item->sortOrder());
    }

    #[Test]
    public function create_defaults_currency_to_zmw(): void
    {
        $item = LineItem::create('Item', 1, 10);
        $this->assertSame('ZMW', $item->unitPrice()->currency());
    }

    #[Test]
    public function create_trims_description(): void
    {
        $item = LineItem::create('  Trimmed  ', 1, 5);
        $this->assertSame('Trimmed', $item->description());
    }

    #[Test]
    public function create_generates_uuid_id(): void
    {
        $item = LineItem::create('Test', 1, 1);
        $this->assertMatchesRegularExpression('/^[a-f0-9\-]{36}$/', $item->id());
    }

    #[Test]
    public function from_array_hydrates_correctly(): void
    {
        $data = [
            'id' => 'custom-id',
            'description' => 'Desk',
            'quantity' => '2',
            'unit' => 'pieces',
            'unit_price' => '150.50',
            'sort_order' => 3,
        ];
        $item = LineItem::fromArray($data, 'USD');
        $this->assertSame('custom-id', $item->id());
        $this->assertSame('Desk', $item->description());
        $this->assertSame(2.0, $item->quantity());
        $this->assertSame('pieces', $item->unit());
        $this->assertSame(150.5, $item->unitPrice()->amount());
        $this->assertSame('USD', $item->unitPrice()->currency());
        $this->assertSame(3, $item->sortOrder());
        $this->assertSame(301.0, $item->amount()->amount());
    }

    #[Test]
    public function from_array_generates_id_if_missing(): void
    {
        $item = LineItem::fromArray(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10]);
        $this->assertMatchesRegularExpression('/^[a-f0-9\-]{36}$/', $item->id());
    }

    #[Test]
    public function from_array_defaults_sort_order(): void
    {
        $item = LineItem::fromArray(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10]);
        $this->assertSame(0, $item->sortOrder());
    }

    #[Test]
    public function update_quantity_recalculates_amount(): void
    {
        $item = LineItem::create('Item', 2, 10);
        $this->assertSame(20.0, $item->amount()->amount());
        $item->updateQuantity(5);
        $this->assertSame(5.0, $item->quantity());
        $this->assertSame(50.0, $item->amount()->amount());
    }

    #[Test]
    public function update_unit_price_recalculates_amount(): void
    {
        $item = LineItem::create('Item', 3, 20);
        $this->assertSame(60.0, $item->amount()->amount());
        $item->updateUnitPrice(25);
        $this->assertSame(25.0, $item->unitPrice()->amount());
        $this->assertSame(75.0, $item->amount()->amount());
    }

    #[Test]
    public function update_unit_price_preserves_currency(): void
    {
        $item = LineItem::create('Item', 1, 10, 'EUR');
        $item->updateUnitPrice(20);
        $this->assertSame('EUR', $item->unitPrice()->currency());
    }

    #[Test]
    public function to_array_returns_correct_structure(): void
    {
        $item = LineItem::create('Product', 4, 25.50, 'USD', 'box', 1);
        $result = $item->toArray();
        $this->assertSame('Product', $result['description']);
        $this->assertSame(4.0, $result['quantity']);
        $this->assertSame('box', $result['unit']);
        $this->assertSame(25.5, $result['unit_price']);
        $this->assertSame(102.0, $result['amount']);
        $this->assertSame(1, $result['sort_order']);
        $this->assertMatchesRegularExpression('/^[a-f0-9\-]{36}$/', $result['id']);
    }

    #[Test]
    public function unit_can_be_null(): void
    {
        $item = LineItem::create('Service', 1, 100);
        $this->assertNull($item->unit());
    }
}
