<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\Expense;
use App\Domain\GrowFinance\ValueObjects\PaymentMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExpenseTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $expense = new Expense(id: 1, businessId: 5, amount: 1000.0, category: 'Office Supplies');

        $this->assertSame(1, $expense->id);
        $this->assertSame(5, $expense->businessId);
        $this->assertSame('Office Supplies', $expense->category);
    }

    #[Test]
    public function get_total_amount_includes_tax()
    {
        $expense = new Expense(id: 1, businessId: 5, amount: 1000.0, taxAmount: 160.0);
        $this->assertSame(1160.0, $expense->getTotalAmount());
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $expense = Expense::reconstitute([
            'id' => 1, 'business_id' => 5, 'amount' => 500.0,
            'description' => 'Test expense', 'payment_method' => 'cash',
        ]);

        $this->assertSame('Test expense', $expense->description);
        $this->assertSame(PaymentMethod::CASH, $expense->paymentMethod);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $expense = new Expense(id: 1, businessId: 5, amount: 500.0, category: 'Supplies');
        $array = $expense->toArray();

        $this->assertSame('Supplies', $array['category']);
        $this->assertSame(500.0, $array['amount']);
    }
}
