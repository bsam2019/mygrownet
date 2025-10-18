<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Human Resources',
                'description' => 'Manages employee relations, recruitment, and organizational development',
                'is_active' => true,
            ],
            [
                'name' => 'Investment Management',
                'description' => 'Oversees investment portfolios, client relationships, and fund management',
                'is_active' => true,
            ],
            [
                'name' => 'Field Operations',
                'description' => 'Manages field agents and client acquisition activities',
                'is_active' => true,
            ],
            [
                'name' => 'Finance & Accounting',
                'description' => 'Handles financial operations, payroll, and compliance',
                'is_active' => true,
            ],
            [
                'name' => 'Information Technology',
                'description' => 'Manages technology infrastructure and system development',
                'is_active' => true,
            ],
            [
                'name' => 'Compliance & Risk',
                'description' => 'Ensures regulatory compliance and manages operational risks',
                'is_active' => true,
            ],
            [
                'name' => 'Customer Service',
                'description' => 'Provides client support and manages customer relationships',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            DepartmentModel::create($department);
        }

        // Create hierarchical structure - make Field Operations a sub-department of Investment Management
        $investmentDept = DepartmentModel::where('name', 'Investment Management')->first();
        $fieldDept = DepartmentModel::where('name', 'Field Operations')->first();
        
        if ($investmentDept && $fieldDept) {
            $fieldDept->update(['parent_department_id' => $investmentDept->id]);
        }
    }
}