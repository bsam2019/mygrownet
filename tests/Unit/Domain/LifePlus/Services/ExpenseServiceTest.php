<?php

namespace Tests\Unit\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusExpense;
use App\Domain\LifePlus\Repositories\BudgetRepositoryInterface;
use App\Domain\LifePlus\Repositories\ExpenseRepositoryInterface;
use App\Domain\LifePlus\Services\ExpenseService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExpenseServiceTest extends TestCase
{
    private ExpenseRepositoryInterface $expenseRepo;
    private BudgetRepositoryInterface $budgetRepo;
    private ExpenseService $service;

    protected function setUp(): void
    {
        $this->expenseRepo = $this->createMock(ExpenseRepositoryInterface::class);
        $this->budgetRepo = $this->createMock(BudgetRepositoryInterface::class);
        $this->service = new ExpenseService($this->expenseRepo, $this->budgetRepo);
    }

    #[Test]
    public function getExpenses_returns_mapped_expenses()
    {
        $expense = LifePlusExpense::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 150.0, 'expense_date' => '2026-08-15']);
        $this->expenseRepo->expects($this->once())->method('findByUser')->with(42, [])->willReturn([$expense]);

        $result = $this->service->getExpenses(42);

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame(150.0, $result[0]['amount']);
        $this->assertSame('2026-08-15', $result[0]['expense_date']);
    }

    #[Test]
    public function createExpense_saves_and_returns_mapped()
    {
        $saved = LifePlusExpense::reconstitute(['id' => 10, 'user_id' => 42, 'amount' => 75.0, 'expense_date' => '2026-08-16']);
        $this->expenseRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->createExpense(42, ['amount' => 75.0, 'expense_date' => '2026-08-16']);

        $this->assertSame(10, $result['id']);
        $this->assertSame(75.0, $result['amount']);
    }

    #[Test]
    public function createExpense_applies_default_expense_date()
    {
        $saved = LifePlusExpense::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 50.0, 'expense_date' => date('Y-m-d')]);
        $this->expenseRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->createExpense(42, ['amount' => 50.0]);

        $this->assertSame(date('Y-m-d'), $result['expense_date']);
    }

    #[Test]
    public function updateExpense_returns_null_when_not_found()
    {
        $this->expenseRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertNull($this->service->updateExpense(1, 42, ['amount' => 200.0]));
    }

    #[Test]
    public function updateExpense_returns_null_on_user_mismatch()
    {
        $expense = LifePlusExpense::reconstitute(['id' => 1, 'user_id' => 99, 'amount' => 50.0, 'expense_date' => '2026-08-15']);
        $this->expenseRepo->expects($this->once())->method('findById')->with(1)->willReturn($expense);

        $this->assertNull($this->service->updateExpense(1, 42, ['amount' => 200.0]));
    }

    #[Test]
    public function updateExpense_merges_and_saves()
    {
        $expense = LifePlusExpense::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 50.0, 'expense_date' => '2026-08-15']);
        $updated = LifePlusExpense::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 200.0, 'expense_date' => '2026-08-15']);

        $this->expenseRepo->expects($this->once())->method('findById')->with(1)->willReturn($expense);
        $this->expenseRepo->expects($this->once())->method('save')->willReturn($updated);

        $result = $this->service->updateExpense(1, 42, ['amount' => 200.0]);

        $this->assertSame(200.0, $result['amount']);
    }

    #[Test]
    public function deleteExpense_returns_true_on_success()
    {
        $expense = LifePlusExpense::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 50.0, 'expense_date' => '2026-08-15']);
        $this->expenseRepo->expects($this->once())->method('findById')->with(1)->willReturn($expense);
        $this->expenseRepo->expects($this->once())->method('delete')->with(1)->willReturn(true);

        $this->assertTrue($this->service->deleteExpense(1, 42));
    }

    #[Test]
    public function deleteExpense_returns_false_on_user_mismatch()
    {
        $expense = LifePlusExpense::reconstitute(['id' => 1, 'user_id' => 99, 'amount' => 50.0, 'expense_date' => '2026-08-15']);
        $this->expenseRepo->expects($this->once())->method('findById')->with(1)->willReturn($expense);

        $this->assertFalse($this->service->deleteExpense(1, 42));
    }

    #[Test]
    public function getMonthSummary_delegates_to_repo()
    {
        $summary = ['total_spent' => 500.0, 'budget' => 1000.0];
        $this->expenseRepo->expects($this->once())->method('getMonthSummary')->with(42, '2026-08')->willReturn($summary);

        $this->assertSame($summary, $this->service->getMonthSummary(42, '2026-08'));
    }

    #[Test]
    public function syncExpenses_creates_new_when_no_local_id()
    {
        $saved = LifePlusExpense::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 30.0, 'expense_date' => '2026-08-15']);
        $this->expenseRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->syncExpenses(42, [['amount' => 30.0, 'expense_date' => '2026-08-15']]);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function syncExpenses_updates_existing_when_local_id_matches()
    {
        $existing = LifePlusExpense::reconstitute(['id' => 5, 'user_id' => 42, 'amount' => 20.0, 'expense_date' => '2026-08-15', 'local_id' => 'local-1']);
        $updated = LifePlusExpense::reconstitute(['id' => 5, 'user_id' => 42, 'amount' => 25.0, 'expense_date' => '2026-08-15', 'local_id' => 'local-1']);

        $this->expenseRepo->expects($this->once())->method('findByLocalId')->with(42, 'local-1')->willReturn($existing);
        $this->expenseRepo->expects($this->once())->method('findById')->with(5)->willReturn($existing);
        $this->expenseRepo->expects($this->once())->method('save')->willReturn($updated);

        $result = $this->service->syncExpenses(42, [['amount' => 25.0, 'expense_date' => '2026-08-15', 'local_id' => 'local-1']]);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function getCategories_returns_empty_array()
    {
        $this->assertSame([], $this->service->getCategories(42));
    }

    #[Test]
    public function createCategory_returns_empty_array()
    {
        $this->assertSame([], $this->service->createCategory(42, ['name' => 'Food']));
    }
}
