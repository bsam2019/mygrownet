<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = DepartmentModel::all()->keyBy('name');

        $positions = [
            // Human Resources
            [
                'title' => 'HR Manager',
                'description' => 'Oversees all human resources operations and employee relations',
                'department_name' => 'Human Resources',
                'base_salary_min' => 80000,
                'base_salary_max' => 120000,
                'commission_eligible' => false,
                'commission_rate' => 0,
                'responsibilities' => [
                    'Employee recruitment and onboarding',
                    'Performance management and reviews',
                    'Policy development and implementation',
                    'Employee relations and conflict resolution'
                ],
                'required_permissions' => [
                    'employee.view',
                    'employee.create',
                    'employee.edit',
                    'employee.delete',
                    'department.manage',
                    'performance.manage'
                ],
            ],
            [
                'title' => 'HR Specialist',
                'description' => 'Supports HR operations and employee administration',
                'department_name' => 'Human Resources',
                'base_salary_min' => 45000,
                'base_salary_max' => 65000,
                'commission_eligible' => false,
                'commission_rate' => 0,
                'responsibilities' => [
                    'Employee record maintenance',
                    'Benefits administration',
                    'Recruitment support',
                    'Training coordination'
                ],
                'required_permissions' => [
                    'employee.view',
                    'employee.edit',
                    'performance.view'
                ],
            ],

            // Investment Management
            [
                'title' => 'Investment Director',
                'description' => 'Leads investment strategy and portfolio management',
                'department_name' => 'Investment Management',
                'base_salary_min' => 150000,
                'base_salary_max' => 200000,
                'commission_eligible' => true,
                'commission_rate' => 2.5,
                'responsibilities' => [
                    'Investment strategy development',
                    'Portfolio oversight and management',
                    'Client relationship management',
                    'Team leadership and development'
                ],
                'required_permissions' => [
                    'investment.manage',
                    'client.manage',
                    'employee.view',
                    'performance.view',
                    'commission.view'
                ],
            ],
            [
                'title' => 'Portfolio Manager',
                'description' => 'Manages client investment portfolios and relationships',
                'department_name' => 'Investment Management',
                'base_salary_min' => 90000,
                'base_salary_max' => 130000,
                'commission_eligible' => true,
                'commission_rate' => 1.5,
                'responsibilities' => [
                    'Client portfolio management',
                    'Investment analysis and recommendations',
                    'Client communication and reporting',
                    'Risk assessment and mitigation'
                ],
                'required_permissions' => [
                    'investment.view',
                    'investment.edit',
                    'client.view',
                    'client.edit',
                    'commission.view'
                ],
            ],

            // Field Operations
            [
                'title' => 'Field Operations Manager',
                'description' => 'Oversees field agent activities and client acquisition',
                'department_name' => 'Field Operations',
                'base_salary_min' => 70000,
                'base_salary_max' => 100000,
                'commission_eligible' => true,
                'commission_rate' => 3.0,
                'responsibilities' => [
                    'Field agent management and training',
                    'Client acquisition strategy',
                    'Territory management',
                    'Performance monitoring and coaching'
                ],
                'required_permissions' => [
                    'employee.view',
                    'client.view',
                    'client.create',
                    'commission.view',
                    'performance.view'
                ],
            ],
            [
                'title' => 'Senior Field Agent',
                'description' => 'Experienced field agent with mentoring responsibilities',
                'department_name' => 'Field Operations',
                'base_salary_min' => 50000,
                'base_salary_max' => 70000,
                'commission_eligible' => true,
                'commission_rate' => 5.0,
                'responsibilities' => [
                    'Client prospecting and acquisition',
                    'Investment facilitation and support',
                    'New agent training and mentoring',
                    'Territory development'
                ],
                'required_permissions' => [
                    'client.view',
                    'client.create',
                    'client.edit',
                    'commission.view',
                    'investment.view'
                ],
            ],
            [
                'title' => 'Field Agent',
                'description' => 'Front-line agent responsible for client acquisition and investment facilitation',
                'department_name' => 'Field Operations',
                'base_salary_min' => 35000,
                'base_salary_max' => 50000,
                'commission_eligible' => true,
                'commission_rate' => 7.5,
                'responsibilities' => [
                    'Client prospecting and outreach',
                    'Investment presentation and facilitation',
                    'Client relationship maintenance',
                    'Market feedback and reporting'
                ],
                'required_permissions' => [
                    'client.view',
                    'client.create',
                    'commission.view',
                    'investment.view'
                ],
            ],

            // Finance & Accounting
            [
                'title' => 'Finance Manager',
                'description' => 'Oversees financial operations and reporting',
                'department_name' => 'Finance & Accounting',
                'base_salary_min' => 85000,
                'base_salary_max' => 115000,
                'commission_eligible' => false,
                'commission_rate' => 0,
                'responsibilities' => [
                    'Financial planning and analysis',
                    'Budget management and oversight',
                    'Financial reporting and compliance',
                    'Team management and development'
                ],
                'required_permissions' => [
                    'finance.manage',
                    'commission.manage',
                    'payroll.manage',
                    'employee.view'
                ],
            ],
            [
                'title' => 'Payroll Administrator',
                'description' => 'Manages employee compensation and commission processing',
                'department_name' => 'Finance & Accounting',
                'base_salary_min' => 45000,
                'base_salary_max' => 60000,
                'commission_eligible' => false,
                'commission_rate' => 0,
                'responsibilities' => [
                    'Payroll processing and administration',
                    'Commission calculation and payment',
                    'Benefits administration',
                    'Compliance reporting'
                ],
                'required_permissions' => [
                    'payroll.manage',
                    'commission.manage',
                    'employee.view',
                    'finance.view'
                ],
            ],

            // Information Technology
            [
                'title' => 'IT Manager',
                'description' => 'Leads technology infrastructure and development',
                'department_name' => 'Information Technology',
                'base_salary_min' => 90000,
                'base_salary_max' => 130000,
                'commission_eligible' => false,
                'commission_rate' => 0,
                'responsibilities' => [
                    'Technology strategy and planning',
                    'System architecture and development',
                    'Team leadership and project management',
                    'Security and compliance oversight'
                ],
                'required_permissions' => [
                    'system.manage',
                    'employee.view',
                    'security.manage'
                ],
            ],

            // Compliance & Risk
            [
                'title' => 'Compliance Officer',
                'description' => 'Ensures regulatory compliance and manages operational risks',
                'department_name' => 'Compliance & Risk',
                'base_salary_min' => 75000,
                'base_salary_max' => 105000,
                'commission_eligible' => false,
                'commission_rate' => 0,
                'responsibilities' => [
                    'Regulatory compliance monitoring',
                    'Risk assessment and mitigation',
                    'Audit coordination and reporting',
                    'Policy development and training'
                ],
                'required_permissions' => [
                    'compliance.manage',
                    'audit.view',
                    'employee.view',
                    'finance.view'
                ],
            ],

            // Customer Service
            [
                'title' => 'Customer Service Manager',
                'description' => 'Oversees customer support operations and client satisfaction',
                'department_name' => 'Customer Service',
                'base_salary_min' => 60000,
                'base_salary_max' => 80000,
                'commission_eligible' => false,
                'commission_rate' => 0,
                'responsibilities' => [
                    'Customer service strategy and operations',
                    'Team management and training',
                    'Client satisfaction monitoring',
                    'Process improvement and optimization'
                ],
                'required_permissions' => [
                    'client.view',
                    'client.edit',
                    'employee.view',
                    'support.manage'
                ],
            ],
            [
                'title' => 'Customer Service Representative',
                'description' => 'Provides direct customer support and assistance',
                'department_name' => 'Customer Service',
                'base_salary_min' => 30000,
                'base_salary_max' => 45000,
                'commission_eligible' => false,
                'commission_rate' => 0,
                'responsibilities' => [
                    'Customer inquiry handling and resolution',
                    'Account support and maintenance',
                    'Documentation and record keeping',
                    'Escalation management'
                ],
                'required_permissions' => [
                    'client.view',
                    'support.view',
                    'ticket.manage'
                ],
            ],
        ];

        foreach ($positions as $positionData) {
            $departmentName = $positionData['department_name'];
            
            $department = $departments[$departmentName] ?? null;
            if (!$department) {
                continue;
            }

            // Map the data to match database schema
            $mappedData = [
                'title' => $positionData['title'],
                'description' => $positionData['description'],
                'department_id' => $department->id,
                'min_salary' => $positionData['base_salary_min'],
                'max_salary' => $positionData['base_salary_max'],
                'base_commission_rate' => $positionData['commission_eligible'] ? ($positionData['commission_rate'] / 100) : 0,
                'performance_commission_rate' => $positionData['commission_eligible'] ? 0.02 : 0, // 2% performance bonus
                'permissions' => json_encode($positionData['required_permissions']),
                'level' => $this->getPositionLevel($positionData['title']),
                'is_active' => true,
            ];

            PositionModel::create($mappedData);
        }
    }

    private function getPositionLevel(string $title): int
    {
        $levels = [
            'Director' => 8,
            'Manager' => 6,
            'Senior' => 5,
            'Lead' => 4,
            'Specialist' => 3,
            'Agent' => 2,
            'Representative' => 1,
        ];

        foreach ($levels as $keyword => $level) {
            if (str_contains($title, $keyword)) {
                return $level;
            }
        }

        return 2; // Default level
    }
}