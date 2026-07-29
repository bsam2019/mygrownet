<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\RecurringTransaction;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RecurringTransactionTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $rt = new RecurringTransaction(
            id: 1, businessId: 5, type: 'expense', accountId: 100,
            vendorId: null, customerId: null, description: 'Monthly rent',
            category: 'Rent', amount: 5000.0, paymentMethod: 'bank',
            frequency: 'monthly', startDate: new DateTimeImmutable('2026-01-01'),
            endDate: null, nextDueDate: new DateTimeImmutable('2026-02-01'),
            lastProcessedDate: null, isActive: true, occurrencesCount: 0,
            maxOccurrences: 12, notes: null,
            createdAt: null, updatedAt: null,
        );

        $this->assertSame(1, $rt->id);
        $this->assertSame('Monthly rent', $rt->description);
        $this->assertSame(5000.0, $rt->amount);
    }

    #[Test]
    public function should_process_returns_false_when_not_active()
    {
        $rt = new RecurringTransaction(id: 1, businessId: 5, type: null, accountId: null, vendorId: null, customerId: null, description: null, category: null, amount: 100.0, paymentMethod: null, frequency: null, startDate: null, endDate: null, nextDueDate: new DateTimeImmutable('2020-01-01'), lastProcessedDate: null, isActive: false, occurrencesCount: 0, maxOccurrences: null, notes: null, createdAt: null, updatedAt: null);
        $this->assertFalse($rt->shouldProcess());
    }

    #[Test]
    public function should_process_returns_false_when_no_next_due_date()
    {
        $rt = new RecurringTransaction(id: 1, businessId: 5, type: null, accountId: null, vendorId: null, customerId: null, description: null, category: null, amount: 100.0, paymentMethod: null, frequency: null, startDate: null, endDate: null, nextDueDate: null, lastProcessedDate: null, isActive: true, occurrencesCount: 0, maxOccurrences: null, notes: null, createdAt: null, updatedAt: null);
        $this->assertFalse($rt->shouldProcess());
    }

    #[Test]
    public function should_process_returns_false_when_future_date()
    {
        $rt = new RecurringTransaction(id: 1, businessId: 5, type: null, accountId: null, vendorId: null, customerId: null, description: null, category: null, amount: 100.0, paymentMethod: null, frequency: null, startDate: null, endDate: null, nextDueDate: new DateTimeImmutable('2099-01-01'), lastProcessedDate: null, isActive: true, occurrencesCount: 0, maxOccurrences: null, notes: null, createdAt: null, updatedAt: null);
        $this->assertFalse($rt->shouldProcess());
    }

    #[Test]
    public function should_process_returns_false_when_max_occurrences_reached()
    {
        $rt = new RecurringTransaction(id: 1, businessId: 5, type: null, accountId: null, vendorId: null, customerId: null, description: null, category: null, amount: 100.0, paymentMethod: null, frequency: null, startDate: null, endDate: null, nextDueDate: new DateTimeImmutable('2020-01-01'), lastProcessedDate: null, isActive: true, occurrencesCount: 5, maxOccurrences: 5, notes: null, createdAt: null, updatedAt: null);
        $this->assertFalse($rt->shouldProcess());
    }

    #[Test]
    public function should_process_returns_true_when_due()
    {
        $rt = new RecurringTransaction(id: 1, businessId: 5, type: null, accountId: null, vendorId: null, customerId: null, description: null, category: null, amount: 100.0, paymentMethod: null, frequency: null, startDate: null, endDate: null, nextDueDate: new DateTimeImmutable('2020-01-01'), lastProcessedDate: null, isActive: true, occurrencesCount: 3, maxOccurrences: 12, notes: null, createdAt: null, updatedAt: null);
        $this->assertTrue($rt->shouldProcess());
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $rt = RecurringTransaction::reconstitute([
            'id' => 1, 'business_id' => 5, 'type' => 'expense',
            'amount' => 1000.0, 'is_active' => true,
        ]);

        $this->assertSame('expense', $rt->type);
        $this->assertTrue($rt->isActive);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $rt = new RecurringTransaction(id: 1, businessId: 5, type: 'expense', accountId: 100, vendorId: null, customerId: null, description: 'Test', category: null, amount: 500.0, paymentMethod: null, frequency: 'monthly', startDate: null, endDate: null, nextDueDate: null, lastProcessedDate: null, isActive: true, occurrencesCount: 0, maxOccurrences: null, notes: null, createdAt: null, updatedAt: null);
        $array = $rt->toArray();

        $this->assertSame('expense', $array['type']);
        $this->assertSame(500.0, $array['amount']);
    }
}
