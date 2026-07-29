<?php

namespace Tests\Unit\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusBudget;
use App\Domain\LifePlus\Repositories\BudgetRepositoryInterface;
use App\Domain\LifePlus\Services\BudgetService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BudgetServiceTest extends TestCase
{
    private BudgetRepositoryInterface $budgetRepo;
    private BudgetService $service;

    protected function setUp(): void
    {
        $this->budgetRepo = $this->createMock(BudgetRepositoryInterface::class);
        $this->service = new BudgetService($this->budgetRepo);
    }

    #[Test]
    public function getBudgets_returns_mapped_budgets()
    {
        $budget = LifePlusBudget::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 2000.0, 'period' => 'monthly', 'start_date' => '2026-08-01', 'end_date' => '2026-08-31']);
        $this->budgetRepo->expects($this->once())->method('findByUser')->with(42)->willReturn([$budget]);

        $result = $this->service->getBudgets(42);

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame(2000.0, $result[0]['amount']);
        $this->assertSame('monthly', $result[0]['period']);
        $this->assertSame('2026-08-01', $result[0]['start_date']);
        $this->assertSame('2026-08-31', $result[0]['end_date']);
    }

    #[Test]
    public function getCurrentBudget_returns_null_when_none()
    {
        $this->budgetRepo->expects($this->once())->method('findCurrent')->with(42)->willReturn(null);

        $this->assertNull($this->service->getCurrentBudget(42));
    }

    #[Test]
    public function getCurrentBudget_returns_mapped_budget()
    {
        $budget = LifePlusBudget::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 1500.0, 'period' => 'monthly', 'start_date' => '2026-08-01']);
        $this->budgetRepo->expects($this->once())->method('findCurrent')->with(42)->willReturn($budget);

        $result = $this->service->getCurrentBudget(42);

        $this->assertSame(1500.0, $result['amount']);
    }

    #[Test]
    public function createBudget_saves_and_returns_mapped()
    {
        $saved = LifePlusBudget::reconstitute(['id' => 10, 'user_id' => 42, 'amount' => 3000.0, 'period' => 'monthly', 'start_date' => '2026-09-01']);
        $this->budgetRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->createBudget(42, ['amount' => 3000.0]);

        $this->assertSame(10, $result['id']);
        $this->assertSame(3000.0, $result['amount']);
        $this->assertSame('monthly', $result['period']);
    }

    #[Test]
    public function createBudget_applies_default_period_and_start_date()
    {
        $this->budgetRepo->expects($this->once())->method('save')->willReturnCallback(function (LifePlusBudget $budget) {
            $this->assertSame('monthly', $budget->period);
            $this->assertSame((new \DateTimeImmutable('first day of this month'))->format('Y-m-d'), $budget->startDate->format('Y-m-d'));
            return $budget;
        });

        $this->service->createBudget(42, ['amount' => 500.0]);
    }

    #[Test]
    public function updateBudget_returns_null_when_not_found()
    {
        $this->budgetRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertNull($this->service->updateBudget(1, 42, ['amount' => 2500.0]));
    }

    #[Test]
    public function updateBudget_returns_null_on_user_mismatch()
    {
        $budget = LifePlusBudget::reconstitute(['id' => 1, 'user_id' => 99, 'amount' => 1000.0, 'period' => 'monthly', 'start_date' => '2026-08-01']);
        $this->budgetRepo->expects($this->once())->method('findById')->with(1)->willReturn($budget);

        $this->assertNull($this->service->updateBudget(1, 42, ['amount' => 2500.0]));
    }

    #[Test]
    public function updateBudget_merges_and_saves()
    {
        $budget = LifePlusBudget::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 1000.0, 'period' => 'monthly', 'start_date' => '2026-08-01']);
        $updated = LifePlusBudget::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 2500.0, 'period' => 'monthly', 'start_date' => '2026-08-01']);

        $this->budgetRepo->expects($this->once())->method('findById')->with(1)->willReturn($budget);
        $this->budgetRepo->expects($this->once())->method('save')->willReturn($updated);

        $result = $this->service->updateBudget(1, 42, ['amount' => 2500.0]);

        $this->assertSame(2500.0, $result['amount']);
    }

    #[Test]
    public function deleteBudget_returns_true_on_success()
    {
        $budget = LifePlusBudget::reconstitute(['id' => 1, 'user_id' => 42, 'amount' => 500.0, 'period' => 'monthly', 'start_date' => '2026-08-01']);
        $this->budgetRepo->expects($this->once())->method('findById')->with(1)->willReturn($budget);
        $this->budgetRepo->expects($this->once())->method('delete')->with(1)->willReturn(true);

        $this->assertTrue($this->service->deleteBudget(1, 42));
    }

    #[Test]
    public function deleteBudget_returns_false_on_user_mismatch()
    {
        $budget = LifePlusBudget::reconstitute(['id' => 1, 'user_id' => 99, 'amount' => 500.0, 'period' => 'monthly', 'start_date' => '2026-08-01']);
        $this->budgetRepo->expects($this->once())->method('findById')->with(1)->willReturn($budget);

        $this->assertFalse($this->service->deleteBudget(1, 42));
    }

    #[Test]
    public function deleteBudget_returns_false_on_not_found()
    {
        $this->budgetRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertFalse($this->service->deleteBudget(1, 42));
    }
}
