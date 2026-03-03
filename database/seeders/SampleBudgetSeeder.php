<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\BudgetModel;
use App\Infrastructure\Persistence\Eloquent\CMS\BudgetItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use Carbon\Carbon;

class SampleBudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = CompanyModel::where('name', 'MyGrowNet Platform')->first();
        
        if (!$company) {
            $this->command->error('MyGrowNet Platform company not found. Please create it first.');
            return;
        }
        
        // Check if budget already exists for current month
        $existingBudget = BudgetModel::where('company_id', $company->id)
            ->where('period_type', 'monthly')
            ->whereYear('start_date', Carbon::now()->year)
            ->whereMonth('start_date', Carbon::now()->month)
            ->first();
            
        if ($existingBudget) {
            $this->command->info('Budget for current month already exists. Skipping...');
            return;
        }
        
        // Create monthly budget for current month
        $budget = BudgetModel::create([
            'company_id' => $company->id,
            'name' => 'MyGrowNet Platform - ' . Carbon::now()->format('F Y'),
            'period_type' => 'monthly',
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => Carbon::now()->endOfMonth(),
            'total_budget' => 50000.00, // K50,000 total budget
            'status' => 'active',
            'notes' => 'Sample budget for testing Budget Management system',
            'created_by' => 1, // Admin user
        ]);
        
        // Create budget items (expenses)
        $expenseItems = [
            ['category' => 'Marketing', 'budgeted_amount' => 10000.00, 'notes' => 'Digital marketing, ads, campaigns'],
            ['category' => 'Office Expenses', 'budgeted_amount' => 5000.00, 'notes' => 'Office supplies, utilities'],
            ['category' => 'Travel', 'budgeted_amount' => 3000.00, 'notes' => 'Business travel, transport'],
            ['category' => 'Infrastructure', 'budgeted_amount' => 8000.00, 'notes' => 'Servers, hosting, software'],
            ['category' => 'Legal & Compliance', 'budgeted_amount' => 4000.00, 'notes' => 'Legal fees, compliance costs'],
            ['category' => 'Professional Services', 'budgeted_amount' => 6000.00, 'notes' => 'Consultants, contractors'],
            ['category' => 'Utilities', 'budgeted_amount' => 2000.00, 'notes' => 'Electricity, water, internet'],
            ['category' => 'General Expenses', 'budgeted_amount' => 2000.00, 'notes' => 'Miscellaneous expenses'],
        ];
        
        foreach ($expenseItems as $item) {
            BudgetItemModel::create([
                'budget_id' => $budget->id,
                'category' => $item['category'],
                'item_type' => 'expense',
                'budgeted_amount' => $item['budgeted_amount'],
                'notes' => $item['notes'],
            ]);
        }
        
        // Create budget items (revenue)
        $revenueItems = [
            ['category' => 'Wallet Top-ups', 'budgeted_amount' => 30000.00, 'notes' => 'Member wallet deposits'],
            ['category' => 'Starter Kits', 'budgeted_amount' => 15000.00, 'notes' => 'Starter kit sales'],
            ['category' => 'Module Subscriptions', 'budgeted_amount' => 20000.00, 'notes' => 'Monthly module subscriptions'],
            ['category' => 'Product Sales', 'budgeted_amount' => 10000.00, 'notes' => 'Digital product sales'],
        ];
        
        foreach ($revenueItems as $item) {
            BudgetItemModel::create([
                'budget_id' => $budget->id,
                'category' => $item['category'],
                'item_type' => 'revenue',
                'budgeted_amount' => $item['budgeted_amount'],
                'notes' => $item['notes'],
            ]);
        }
        
        $this->command->info('✅ Sample budget created successfully!');
        $this->command->info("Budget ID: {$budget->id}");
        $this->command->info("Period: {$budget->start_date->format('M d, Y')} - {$budget->end_date->format('M d, Y')}");
        $this->command->info("Total Budget: K" . number_format($budget->total_budget, 2));
        $this->command->info("Expense Items: " . count($expenseItems));
        $this->command->info("Revenue Items: " . count($revenueItems));
    }
}
