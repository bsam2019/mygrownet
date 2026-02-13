<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\BudgetModel;
use App\Infrastructure\Persistence\Eloquent\CMS\BudgetItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    public function createBudget(int $companyId, array $data, int $userId): BudgetModel
    {
        return DB::transaction(function () use ($companyId, $data, $userId) {
            $budget = BudgetModel::create([
                'company_id' => $companyId,
                'name' => $data['name'],
                'period_type' => $data['period_type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_budget' => $data['total_budget'] ?? 0,
                'status' => $data['status'] ?? 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    BudgetItemModel::create([
                        'budget_id' => $budget->id,
                        'category' => $item['category'],
                        'item_type' => $item['item_type'],
                        'budgeted_amount' => $item['budgeted_amount'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            return $budget->load('items');
        });
    }

    public function updateBudget(int $budgetId, array $data): BudgetModel
    {
        return DB::transaction(function () use ($budgetId, $data) {
            $budget = BudgetModel::findOrFail($budgetId);
            
            $budget->update([
                'name' => $data['name'] ?? $budget->name,
                'period_type' => $data['period_type'] ?? $budget->period_type,
                'start_date' => $data['start_date'] ?? $budget->start_date,
                'end_date' => $data['end_date'] ?? $budget->end_date,
                'total_budget' => $data['total_budget'] ?? $budget->total_budget,
                'status' => $data['status'] ?? $budget->status,
                'notes' => $data['notes'] ?? $budget->notes,
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                $budget->items()->delete();
                
                // Create new items
                foreach ($data['items'] as $item) {
                    BudgetItemModel::create([
                        'budget_id' => $budget->id,
                        'category' => $item['category'],
                        'item_type' => $item['item_type'],
                        'budgeted_amount' => $item['budgeted_amount'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            return $budget->load('items');
        });
    }

    public function getBudgetVsActual(int $budgetId): array
    {
        $budget = BudgetModel::with('items')->findOrFail($budgetId);
        
        $startDate = $budget->start_date->toDateString();
        $endDate = $budget->end_date->toDateString();
        $companyId = $budget->company_id;

        // Get actual revenue
        $actualRevenue = InvoiceModel::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('amount_paid');

        // Get actual expenses by category
        $actualExpenses = ExpenseModel::where('company_id', $companyId)
            ->where('approval_status', 'approved')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with('category')
            ->get();

        $expensesByCategory = $actualExpenses->groupBy('category.name')
            ->map(fn($group) => $group->sum('amount'));

        // Build comparison data
        $revenueItems = $budget->items->where('item_type', 'revenue');
        $expenseItems = $budget->items->where('item_type', 'expense');

        $revenueComparison = [];
        foreach ($revenueItems as $item) {
            $budgeted = $item->budgeted_amount;
            $actual = $item->category === 'Total Revenue' ? $actualRevenue : 0;
            $variance = $actual - $budgeted;
            $variancePercent = $budgeted > 0 ? ($variance / $budgeted) * 100 : 0;

            $revenueComparison[] = [
                'category' => $item->category,
                'budgeted' => $budgeted,
                'actual' => $actual,
                'variance' => $variance,
                'variance_percent' => $variancePercent,
                'status' => $variance >= 0 ? 'over' : 'under',
            ];
        }

        $expenseComparison = [];
        foreach ($expenseItems as $item) {
            $budgeted = $item->budgeted_amount;
            $actual = $expensesByCategory->get($item->category, 0);
            $variance = $actual - $budgeted;
            $variancePercent = $budgeted > 0 ? ($variance / $budgeted) * 100 : 0;

            $expenseComparison[] = [
                'category' => $item->category,
                'budgeted' => $budgeted,
                'actual' => $actual,
                'variance' => $variance,
                'variance_percent' => $variancePercent,
                'status' => $variance <= 0 ? 'under' : 'over',
            ];
        }

        $totalBudgetedRevenue = $revenueItems->sum('budgeted_amount');
        $totalBudgetedExpenses = $expenseItems->sum('budgeted_amount');
        $totalActualExpenses = $actualExpenses->sum('amount');

        return [
            'budget' => [
                'id' => $budget->id,
                'name' => $budget->name,
                'period_type' => $budget->period_type,
                'start_date' => $budget->start_date,
                'end_date' => $budget->end_date,
                'status' => $budget->status,
            ],
            'revenue' => [
                'items' => $revenueComparison,
                'total_budgeted' => $totalBudgetedRevenue,
                'total_actual' => $actualRevenue,
                'total_variance' => $actualRevenue - $totalBudgetedRevenue,
                'total_variance_percent' => $totalBudgetedRevenue > 0 
                    ? (($actualRevenue - $totalBudgetedRevenue) / $totalBudgetedRevenue) * 100 
                    : 0,
            ],
            'expenses' => [
                'items' => $expenseComparison,
                'total_budgeted' => $totalBudgetedExpenses,
                'total_actual' => $totalActualExpenses,
                'total_variance' => $totalActualExpenses - $totalBudgetedExpenses,
                'total_variance_percent' => $totalBudgetedExpenses > 0 
                    ? (($totalActualExpenses - $totalBudgetedExpenses) / $totalBudgetedExpenses) * 100 
                    : 0,
            ],
            'net' => [
                'budgeted_profit' => $totalBudgetedRevenue - $totalBudgetedExpenses,
                'actual_profit' => $actualRevenue - $totalActualExpenses,
                'variance' => ($actualRevenue - $totalActualExpenses) - ($totalBudgetedRevenue - $totalBudgetedExpenses),
            ],
        ];
    }

    public function deleteBudget(int $budgetId): bool
    {
        $budget = BudgetModel::findOrFail($budgetId);
        return $budget->delete();
    }
}
