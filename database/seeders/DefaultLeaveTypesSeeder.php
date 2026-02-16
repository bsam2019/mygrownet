<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LeaveTypeModel;
use Illuminate\Database\Seeder;

class DefaultLeaveTypesSeeder extends Seeder
{
    public function run(): void
    {
        $companies = CompanyModel::all();

        $defaultLeaveTypes = [
            [
                'leave_type_name' => 'Annual Leave',
                'leave_code' => 'ANNUAL',
                'description' => 'Standard annual leave entitlement',
                'default_days_per_year' => 24, // Zambian standard
                'is_paid' => true,
                'requires_approval' => true,
                'can_carry_forward' => true,
                'max_carry_forward_days' => 5,
                'max_consecutive_days' => null,
                'min_notice_days' => 7,
                'is_active' => true,
            ],
            [
                'leave_type_name' => 'Sick Leave',
                'leave_code' => 'SICK',
                'description' => 'Medical leave for illness or injury',
                'default_days_per_year' => 12,
                'is_paid' => true,
                'requires_approval' => false,
                'can_carry_forward' => false,
                'max_carry_forward_days' => 0,
                'max_consecutive_days' => null,
                'min_notice_days' => 0,
                'is_active' => true,
            ],
            [
                'leave_type_name' => 'Maternity Leave',
                'leave_code' => 'MATERNITY',
                'description' => 'Maternity leave for expecting mothers',
                'default_days_per_year' => 84, // 12 weeks Zambian law
                'is_paid' => true,
                'requires_approval' => true,
                'can_carry_forward' => false,
                'max_carry_forward_days' => 0,
                'max_consecutive_days' => 84,
                'min_notice_days' => 30,
                'is_active' => true,
            ],
            [
                'leave_type_name' => 'Paternity Leave',
                'leave_code' => 'PATERNITY',
                'description' => 'Paternity leave for new fathers',
                'default_days_per_year' => 2, // Zambian standard
                'is_paid' => true,
                'requires_approval' => true,
                'can_carry_forward' => false,
                'max_carry_forward_days' => 0,
                'max_consecutive_days' => 2,
                'min_notice_days' => 7,
                'is_active' => true,
            ],
            [
                'leave_type_name' => 'Compassionate Leave',
                'leave_code' => 'COMPASSIONATE',
                'description' => 'Leave for family emergencies or bereavement',
                'default_days_per_year' => 5,
                'is_paid' => true,
                'requires_approval' => true,
                'can_carry_forward' => false,
                'max_carry_forward_days' => 0,
                'max_consecutive_days' => 5,
                'min_notice_days' => 0,
                'is_active' => true,
            ],
            [
                'leave_type_name' => 'Unpaid Leave',
                'leave_code' => 'UNPAID',
                'description' => 'Leave without pay',
                'default_days_per_year' => 0,
                'is_paid' => false,
                'requires_approval' => true,
                'can_carry_forward' => false,
                'max_carry_forward_days' => 0,
                'max_consecutive_days' => null,
                'min_notice_days' => 14,
                'is_active' => true,
            ],
            [
                'leave_type_name' => 'Study Leave',
                'leave_code' => 'STUDY',
                'description' => 'Leave for educational purposes',
                'default_days_per_year' => 10,
                'is_paid' => true,
                'requires_approval' => true,
                'can_carry_forward' => false,
                'max_carry_forward_days' => 0,
                'max_consecutive_days' => null,
                'min_notice_days' => 30,
                'is_active' => true,
            ],
        ];

        foreach ($companies as $company) {
            foreach ($defaultLeaveTypes as $leaveType) {
                try {
                    LeaveTypeModel::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'leave_code' => $leaveType['leave_code'],
                        ],
                        array_merge($leaveType, ['company_id' => $company->id])
                    );
                } catch (\Exception $e) {
                    $this->command->warn("Leave type {$leaveType['leave_code']} already exists for company {$company->id}, skipping...");
                    continue;
                }
            }
        }

        $this->command->info('Default leave types seeded successfully!');
    }
}
