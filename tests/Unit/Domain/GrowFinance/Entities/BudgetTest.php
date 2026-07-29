<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\Budget;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BudgetTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $budget = new Budget(id: 1, businessId: 5, name: 'Marketing');

        $this->assertSame(1, $budget->id);
        $this->assertSame(5, $budget->businessId);
        $this->assertSame('Marketing', $budget->name);
    }

    #[Test]
    public function get_remaining_amount_returns_difference()
    {
        $budget = new Budget(id: 1, businessId: 5, name: 'Test', budgetedAmount: 1000.0, spentAmount: 400.0);
        $this->assertSame(600.0, $budget->getRemainingAmount());
    }

    #[Test]
    public function get_remaining_amount_clamps_to_zero()
    {
        $budget = new Budget(id: 1, businessId: 5, name: 'Test', budgetedAmount: 1000.0, spentAmount: 1500.0);
        $this->assertSame(0.0, $budget->getRemainingAmount());
    }

    #[Test]
    public function get_spent_percentage_returns_correct_value()
    {
        $budget = new Budget(id: 1, businessId: 5, name: 'Test', budgetedAmount: 1000.0, spentAmount: 250.0);
        $this->assertSame(25.0, $budget->getSpentPercentage());
    }

    #[Test]
    public function get_spent_percentage_clamps_to_100()
    {
        $budget = new Budget(id: 1, businessId: 5, name: 'Test', budgetedAmount: 1000.0, spentAmount: 2000.0);
        $this->assertSame(100.0, $budget->getSpentPercentage());
    }

    #[Test]
    public function get_spent_percentage_returns_zero_when_no_budget()
    {
        $budget = new Budget(id: 1, businessId: 5, name: 'Test', budgetedAmount: 0.0);
        $this->assertSame(0.0, $budget->getSpentPercentage());
    }

    #[Test]
    public function get_status_returns_correct_state()
    {
        $onTrack = new Budget(id: 1, businessId: 5, name: 'Test', budgetedAmount: 1000.0, spentAmount: 100.0, alertThreshold: 80.0);
        $this->assertSame('on_track', $onTrack->getStatus());

        $nearLimit = new Budget(id: 1, businessId: 5, name: 'Test', budgetedAmount: 1000.0, spentAmount: 850.0, alertThreshold: 80.0);
        $this->assertSame('near_limit', $nearLimit->getStatus());

        $overBudget = new Budget(id: 1, businessId: 5, name: 'Test', budgetedAmount: 1000.0, spentAmount: 1100.0);
        $this->assertSame('over_budget', $overBudget->getStatus());
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $budget = Budget::reconstitute([
            'id' => 1, 'business_id' => 5, 'name' => 'Marketing',
            'budgeted_amount' => 5000.0, 'spent_amount' => 1000.0,
        ]);

        $this->assertSame('Marketing', $budget->name);
        $this->assertSame(5000.0, $budget->budgetedAmount);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $budget = new Budget(id: 1, businessId: 5, name: 'Test', budgetedAmount: 1000.0);
        $array = $budget->toArray();

        $this->assertSame('Test', $array['name']);
        $this->assertSame(1000.0, $array['budgeted_amount']);
    }
}
