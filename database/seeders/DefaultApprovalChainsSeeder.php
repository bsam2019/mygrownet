<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\CMS\ApprovalChainModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use Illuminate\Database\Seeder;

class DefaultApprovalChainsSeeder extends Seeder
{
    public function run(): void
    {
        $companies = CompanyModel::all();

        foreach ($companies as $company) {
            // Expense Approval Chains
            ApprovalChainModel::create([
                'company_id' => $company->id,
                'name' => 'Small Expense Approval',
                'entity_type' => 'expense',
                'min_amount' => 0,
                'max_amount' => 500,
                'approval_steps' => [
                    ['level' => 1, 'role' => 'manager'],
                ],
                'is_active' => true,
                'priority' => 1,
            ]);

            ApprovalChainModel::create([
                'company_id' => $company->id,
                'name' => 'Medium Expense Approval',
                'entity_type' => 'expense',
                'min_amount' => 500.01,
                'max_amount' => 5000,
                'approval_steps' => [
                    ['level' => 1, 'role' => 'manager'],
                    ['level' => 2, 'role' => 'owner'],
                ],
                'is_active' => true,
                'priority' => 2,
            ]);

            ApprovalChainModel::create([
                'company_id' => $company->id,
                'name' => 'Large Expense Approval',
                'entity_type' => 'expense',
                'min_amount' => 5000.01,
                'max_amount' => null,
                'approval_steps' => [
                    ['level' => 1, 'role' => 'manager'],
                    ['level' => 2, 'role' => 'accountant'],
                    ['level' => 3, 'role' => 'owner'],
                ],
                'is_active' => true,
                'priority' => 3,
            ]);

            // Quotation Approval Chains
            ApprovalChainModel::create([
                'company_id' => $company->id,
                'name' => 'Standard Quotation Approval',
                'entity_type' => 'quotation',
                'min_amount' => 0,
                'max_amount' => 10000,
                'approval_steps' => [
                    ['level' => 1, 'role' => 'manager'],
                ],
                'is_active' => false, // Disabled by default
                'priority' => 1,
            ]);

            ApprovalChainModel::create([
                'company_id' => $company->id,
                'name' => 'High-Value Quotation Approval',
                'entity_type' => 'quotation',
                'min_amount' => 10000.01,
                'max_amount' => null,
                'approval_steps' => [
                    ['level' => 1, 'role' => 'manager'],
                    ['level' => 2, 'role' => 'owner'],
                ],
                'is_active' => false, // Disabled by default
                'priority' => 2,
            ]);

            // Payment Approval Chains
            ApprovalChainModel::create([
                'company_id' => $company->id,
                'name' => 'Standard Payment Approval',
                'entity_type' => 'payment',
                'min_amount' => 0,
                'max_amount' => 20000,
                'approval_steps' => [
                    ['level' => 1, 'role' => 'manager'],
                ],
                'is_active' => false, // Disabled by default
                'priority' => 1,
            ]);

            ApprovalChainModel::create([
                'company_id' => $company->id,
                'name' => 'Large Payment Approval',
                'entity_type' => 'payment',
                'min_amount' => 20000.01,
                'max_amount' => null,
                'approval_steps' => [
                    ['level' => 1, 'role' => 'accountant'],
                    ['level' => 2, 'role' => 'owner'],
                ],
                'is_active' => false, // Disabled by default
                'priority' => 2,
            ]);
        }

        $this->command->info('Default approval chains created for all companies');
    }
}
