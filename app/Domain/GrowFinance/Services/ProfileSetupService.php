<?php

namespace App\Domain\GrowFinance\Services;

use App\Infrastructure\Persistence\Eloquent\UserFinancialProfileModel;
use App\Infrastructure\Persistence\Eloquent\IncomeSourceModel;
use App\Infrastructure\Persistence\Eloquent\UserExpenseCategoryModel;
use App\Infrastructure\Persistence\Eloquent\SavingsGoalModel;
use Illuminate\Support\Facades\DB;

class ProfileSetupService
{
    public const DEFAULT_CATEGORIES = [
        'essential' => [
            ['name' => 'Rent/Mortgage', 'fixed' => true],
            ['name' => 'Utilities', 'fixed' => false],
            ['name' => 'Groceries', 'fixed' => false],
            ['name' => 'Transport', 'fixed' => false],
            ['name' => 'Healthcare', 'fixed' => false],
        ],
        'lifestyle' => [
            ['name' => 'Entertainment', 'fixed' => false],
            ['name' => 'Dining Out', 'fixed' => false],
            ['name' => 'Shopping', 'fixed' => false],
            ['name' => 'Personal Care', 'fixed' => false],
        ],
        'financial' => [
            ['name' => 'Savings', 'fixed' => false],
            ['name' => 'Debt Payments', 'fixed' => true],
            ['name' => 'Insurance', 'fixed' => true],
        ],
    ];

    public const BUDGET_METHODS = [
        '50/30/20' => [
            'needs' => 50,
            'wants' => 30,
            'savings' => 20,
        ],
        'zero_based' => [
            'description' => 'Allocate every kwacha to a category',
        ],
        'custom' => [
            'description' => 'Set your own percentages',
        ],
    ];

    public function getOrCreateProfile(int $userId): UserFinancialProfileModel
    {
        return UserFinancialProfileModel::firstOrCreate(
            ['user_id' => $userId],
            [
                'budget_method' => '50/30/20',
                'budget_period' => 'monthly',
                'currency' => 'ZMW',
                'alert_preferences' => [
                    'budget_warning' => true,
                    'over_budget' => true,
                    'weekly_summary' => true,
                ],
            ]
        );
    }

    public function getWizardData(int $userId): array
    {
        $profile = $this->getOrCreateProfile($userId);
        
        return [
            'profile' => $profile,
            'incomeSources' => IncomeSourceModel::where('user_id', $userId)->active()->get(),
            'expenseCategories' => UserExpenseCategoryModel::where('user_id', $userId)->orderBy('display_order')->get(),
            'savingsGoals' => SavingsGoalModel::where('user_id', $userId)->active()->get(),
            'defaultCategories' => self::DEFAULT_CATEGORIES,
            'budgetMethods' => self::BUDGET_METHODS,
        ];
    }

    public function saveIncome(int $userId, array $data): void
    {
        DB::transaction(function () use ($userId, $data) {
            // Clear existing income sources if replacing
            if (!empty($data['replace_existing'])) {
                IncomeSourceModel::where('user_id', $userId)->delete();
            }

            // Ensure only one primary source
            $hasPrimary = false;
            
            foreach ($data['sources'] as $index => $source) {
                $isPrimary = $source['is_primary'] ?? ($index === 0);
                
                if ($isPrimary && $hasPrimary) {
                    $isPrimary = false;
                }
                
                if ($isPrimary) {
                    $hasPrimary = true;
                }

                IncomeSourceModel::create([
                    'user_id' => $userId,
                    'source_type' => $source['source_type'],
                    'amount' => $source['amount'],
                    'frequency' => $source['frequency'],
                    'next_expected_date' => $source['next_expected_date'] ?? null,
                    'is_primary' => $isPrimary,
                    'is_active' => true,
                ]);
            }
        });
    }

    public function saveCategories(int $userId, array $data): void
    {
        DB::transaction(function () use ($userId, $data) {
            // Clear existing categories if replacing
            if (!empty($data['replace_existing'])) {
                UserExpenseCategoryModel::where('user_id', $userId)->delete();
            }

            $order = 0;
            foreach ($data['categories'] as $category) {
                UserExpenseCategoryModel::create([
                    'user_id' => $userId,
                    'category_name' => $category['category_name'],
                    'category_type' => $category['category_type'],
                    'estimated_monthly_amount' => $category['estimated_monthly_amount'] ?? 0,
                    'is_fixed' => $category['is_fixed'] ?? false,
                    'is_active' => $category['is_active'] ?? true,
                    'display_order' => $order++,
                ]);
            }
        });
    }

    public function saveGoals(int $userId, array $data): void
    {
        DB::transaction(function () use ($userId, $data) {
            foreach ($data['goals'] as $goal) {
                SavingsGoalModel::create([
                    'user_id' => $userId,
                    'goal_name' => $goal['goal_name'],
                    'target_amount' => $goal['target_amount'],
                    'target_date' => $goal['target_date'] ?? null,
                    'priority' => $goal['priority'] ?? 'medium',
                    'status' => 'active',
                ]);
            }
        });
    }

    public function savePreferences(int $userId, array $data): void
    {
        $profile = $this->getOrCreateProfile($userId);
        
        $profile->update([
            'budget_method' => $data['budget_method'] ?? '50/30/20',
            'budget_period' => $data['budget_period'] ?? 'monthly',
            'currency' => $data['currency'] ?? 'ZMW',
            'alert_preferences' => $data['alert_preferences'] ?? [
                'budget_warning' => true,
                'over_budget' => true,
                'weekly_summary' => true,
            ],
        ]);
    }

    public function completeSetup(int $userId): void
    {
        $profile = $this->getOrCreateProfile($userId);
        
        $profile->update([
            'setup_completed' => true,
            'setup_completed_at' => now(),
        ]);
    }

    public function isSetupComplete(int $userId): bool
    {
        $profile = UserFinancialProfileModel::where('user_id', $userId)->first();
        return $profile && $profile->setup_completed;
    }

    public function getSetupSummary(int $userId): array
    {
        $profile = $this->getOrCreateProfile($userId);
        $incomeSources = IncomeSourceModel::where('user_id', $userId)->active()->get();
        $categories = UserExpenseCategoryModel::where('user_id', $userId)->active()->get();
        $goals = SavingsGoalModel::where('user_id', $userId)->active()->get();

        $totalMonthlyIncome = $incomeSources->sum(function ($source) {
            return $this->normalizeToMonthly($source->amount, $source->frequency);
        });

        $totalMonthlyExpenses = $categories->sum('estimated_monthly_amount');
        $monthlySurplus = $totalMonthlyIncome - $totalMonthlyExpenses;

        return [
            'total_monthly_income' => $totalMonthlyIncome,
            'total_monthly_expenses' => $totalMonthlyExpenses,
            'monthly_surplus' => $monthlySurplus,
            'income_sources_count' => $incomeSources->count(),
            'active_categories_count' => $categories->count(),
            'savings_goals_count' => $goals->count(),
            'total_savings_target' => $goals->sum('target_amount'),
            'budget_method' => $profile->budget_method,
            'budget_period' => $profile->budget_period,
        ];
    }

    private function normalizeToMonthly(float $amount, string $frequency): float
    {
        return match ($frequency) {
            'weekly' => $amount * 4.33,
            'bi-weekly' => $amount * 2.17,
            'monthly' => $amount,
            'quarterly' => $amount / 3,
            'annually' => $amount / 12,
            default => $amount,
        };
    }
}
