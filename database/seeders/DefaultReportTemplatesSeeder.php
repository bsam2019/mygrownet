<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\ReportTemplateModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;

class DefaultReportTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $companies = CompanyModel::all();

        $templates = [
            [
                'name' => 'Headcount Report',
                'category' => 'headcount',
                'description' => 'Employee headcount analysis by department, type, and status',
                'parameters' => [
                    'department_id' => 'optional',
                    'employment_type' => 'optional',
                    'employment_status' => 'optional',
                ],
                'columns' => ['name', 'job_title', 'department', 'employment_type', 'employment_status', 'hire_date', 'tenure_months'],
            ],
            [
                'name' => 'Attendance Report',
                'category' => 'attendance',
                'description' => 'Daily attendance records with clock in/out times and status',
                'parameters' => [
                    'date_from' => 'required',
                    'date_to' => 'required',
                    'worker_id' => 'optional',
                    'department_id' => 'optional',
                ],
                'columns' => ['date', 'worker_name', 'department', 'clock_in', 'clock_out', 'total_hours', 'status'],
            ],
            [
                'name' => 'Leave Report',
                'category' => 'leave',
                'description' => 'Leave requests summary with approval status and days taken',
                'parameters' => [
                    'date_from' => 'required',
                    'date_to' => 'required',
                    'worker_id' => 'optional',
                    'leave_type_id' => 'optional',
                    'status' => 'optional',
                ],
                'columns' => ['worker_name', 'department', 'leave_type', 'start_date', 'end_date', 'days_requested', 'status', 'reason'],
            ],
            [
                'name' => 'Payroll Report',
                'category' => 'payroll',
                'description' => 'Payroll summary with gross pay, deductions, and net pay',
                'parameters' => [
                    'date_from' => 'required',
                    'date_to' => 'required',
                    'status' => 'optional',
                ],
                'columns' => ['period', 'status', 'total_gross', 'total_net', 'worker_count'],
            ],
            [
                'name' => 'Performance Report',
                'category' => 'performance',
                'description' => 'Performance review summary with ratings and status',
                'parameters' => [
                    'date_from' => 'required',
                    'date_to' => 'required',
                    'worker_id' => 'optional',
                    'department_id' => 'optional',
                ],
                'columns' => ['worker_name', 'department', 'review_date', 'review_type', 'overall_rating', 'status'],
            ],
            [
                'name' => 'Training Report',
                'category' => 'training',
                'description' => 'Training enrollment and completion tracking',
                'parameters' => [
                    'date_from' => 'required',
                    'date_to' => 'required',
                    'worker_id' => 'optional',
                    'program_id' => 'optional',
                ],
                'columns' => ['worker_name', 'department', 'program_name', 'enrollment_date', 'status', 'assessment_score', 'certificate_number'],
            ],
        ];

        foreach ($companies as $company) {
            foreach ($templates as $template) {
                ReportTemplateModel::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $template['name'],
                        'category' => $template['category'],
                    ],
                    [
                        'description' => $template['description'],
                        'parameters' => $template['parameters'],
                        'columns' => $template['columns'],
                        'is_system' => true,
                    ]
                );
            }
        }

        $this->command->info('Default report templates seeded successfully for all companies.');
    }
}
