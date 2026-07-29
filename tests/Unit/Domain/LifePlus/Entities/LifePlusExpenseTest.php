<?php

namespace Tests\Unit\Domain\LifePlus\Entities;

use App\Domain\LifePlus\Entities\LifePlusExpense;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LifePlusExpenseTest extends TestCase
{
    #[Test]
    public function reconstitute_sets_all_fields()
    {
        $expenseDate = new DateTimeImmutable('2026-08-15');
        $createdAt = new DateTimeImmutable('2026-08-15 10:00:00');
        $updatedAt = new DateTimeImmutable('2026-08-15 12:00:00');

        $expense = LifePlusExpense::reconstitute([
            'id' => 1,
            'user_id' => 42,
            'category_id' => 3,
            'amount' => '250.00',
            'description' => 'Groceries',
            'expense_date' => '2026-08-15',
            'is_synced' => false,
            'local_id' => 'local-abc',
            'created_at' => '2026-08-15 10:00:00',
            'updated_at' => '2026-08-15 12:00:00',
        ]);

        $this->assertSame(1, $expense->id);
        $this->assertSame(42, $expense->userId);
        $this->assertSame(3, $expense->categoryId);
        $this->assertSame(250.0, $expense->amount);
        $this->assertSame('Groceries', $expense->description);
        $this->assertEquals($expenseDate, $expense->expenseDate);
        $this->assertFalse($expense->isSynced);
        $this->assertSame('local-abc', $expense->localId);
        $this->assertEquals($createdAt, $expense->createdAt);
        $this->assertEquals($updatedAt, $expense->updatedAt);
    }

    #[Test]
    public function reconstitute_applies_defaults()
    {
        $expense = LifePlusExpense::reconstitute([
            'user_id' => 1,
            'amount' => 100.0,
            'expense_date' => '2026-08-15',
        ]);

        $this->assertNull($expense->id);
        $this->assertNull($expense->categoryId);
        $this->assertNull($expense->description);
        $this->assertTrue($expense->isSynced);
        $this->assertNull($expense->localId);
        $this->assertNull($expense->createdAt);
        $this->assertNull($expense->updatedAt);
    }

    #[Test]
    public function toArray_round_trips_all_fields()
    {
        $data = [
            'id' => 5,
            'user_id' => 99,
            'category_id' => null,
            'amount' => 75.50,
            'description' => 'Transport',
            'expense_date' => '2026-08-14',
            'is_synced' => true,
            'local_id' => null,
            'created_at' => '2026-08-14 07:00:00',
            'updated_at' => null,
        ];

        $expense = LifePlusExpense::reconstitute($data);
        $result = $expense->toArray();

        $this->assertSame($data['id'], $result['id']);
        $this->assertSame($data['user_id'], $result['user_id']);
        $this->assertNull($result['category_id']);
        $this->assertSame($data['amount'], $result['amount']);
        $this->assertSame($data['description'], $result['description']);
        $this->assertSame($data['expense_date'], $result['expense_date']);
        $this->assertSame($data['is_synced'], $result['is_synced']);
        $this->assertNull($result['local_id']);
        $this->assertSame($data['created_at'], $result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    public function expense_date_is_required_and_parsed()
    {
        $expense = LifePlusExpense::reconstitute([
            'user_id' => 1,
            'amount' => 50.0,
            'expense_date' => '2026-08-20',
        ]);

        $this->assertEquals('2026-08-20', $expense->expenseDate->format('Y-m-d'));
        $result = $expense->toArray();
        $this->assertSame('2026-08-20', $result['expense_date']);
    }
}
