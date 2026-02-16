<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\AllowanceTypeModel;
use App\Infrastructure\Persistence\Eloquent\CMS\DeductionTypeModel;

class DefaultAllowanceDeductionTypesSeeder extends Seeder
{
    public function run(): void
    {
        $companies = CompanyModel::all();

        foreach ($companies as $company) {
            $this->seedAllowanceTypes($company->id);
            $this->seedDeductionTypes($company->id);
        }

        $this->command->info('Default allowance and deduction types seeded successfully!');
    }

    private function seedAllowanceTypes(int $companyId): void
    {
        $allowances = [
            [
                'allowance_name' => 'Housing Allowance',
                'allowance_code' => 'HOUSING',
                'calculation_type' => 'fixed',
                'default_amount' => 1500.00,
                'is_taxable' => true,
                'is_pensionable' => false,
            ],
            [
                'allowance_name' => 'Transport Allowance',
                'allowance_code' => 'TRANSPORT',
                'calculation_type' => 'fixed',
                'default_amount' => 800.00,
                'is_taxable' => true,
                'is_pensionable' => false,
            ],
            [
                'allowance_name' => 'Meal Allowance',
                'allowance_code' => 'MEAL',
                'calculation_type' => 'fixed',
                'default_amount' => 500.00,
                'is_taxable' => true,
                'is_pensionable' => false,
            ],
            [
                'allowance_name' => 'Communication Allowance',
                'allowance_code' => 'COMMUNICATION',
                'calculation_type' => 'fixed',
                'default_amount' => 300.00,
                'is_taxable' => true,
                'is_pensionable' => false,
            ],
            [
                'allowance_name' => 'Medical Allowance',
                'allowance_code' => 'MEDICAL',
                'calculation_type' => 'fixed',
                'default_amount' => 400.00,
                'is_taxable' => false,
                'is_pensionable' => false,
            ],
        ];

        foreach ($allowances as $allowance) {
            try {
                AllowanceTypeModel::create(array_merge($allowance, [
                    'company_id' => $companyId,
                    'is_active' => true,
                ]));
            } catch (\Exception $e) {
                // Skip if already exists
                $this->command->warn("Allowance {$allowance['allowance_code']} already exists for company {$companyId}");
            }
        }
    }

    private function seedDeductionTypes(int $companyId): void
    {
        $deductions = [
            [
                'deduction_name' => 'NAPSA (Employee)',
                'deduction_code' => 'NAPSA_EMP',
                'calculation_type' => 'percentage_of_gross',
                'default_percentage' => 5.00,
                'is_statutory' => true,
            ],
            [
                'deduction_name' => 'NHIMA',
                'deduction_code' => 'NHIMA',
                'calculation_type' => 'percentage_of_gross',
                'default_percentage' => 1.00,
                'is_statutory' => true,
            ],
            [
                'deduction_name' => 'PAYE',
                'deduction_code' => 'PAYE',
                'calculation_type' => 'custom',
                'is_statutory' => true,
            ],
            [
                'deduction_name' => 'Loan Repayment',
                'deduction_code' => 'LOAN',
                'calculation_type' => 'fixed',
                'default_amount' => 0,
                'is_statutory' => false,
            ],
            [
                'deduction_name' => 'Salary Advance',
                'deduction_code' => 'ADVANCE',
                'calculation_type' => 'fixed',
                'default_amount' => 0,
                'is_statutory' => false,
            ],
            [
                'deduction_name' => 'Garnishment',
                'deduction_code' => 'GARNISHMENT',
                'calculation_type' => 'fixed',
                'default_amount' => 0,
                'is_statutory' => false,
            ],
        ];

        foreach ($deductions as $deduction) {
            try {
                DeductionTypeModel::create(array_merge($deduction, [
                    'company_id' => $companyId,
                    'is_active' => true,
                ]));
            } catch (\Exception $e) {
                // Skip if already exists
                $this->command->warn("Deduction {$deduction['deduction_code']} already exists for company {$companyId}");
            }
        }
    }
}
