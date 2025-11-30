<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\EmployeeTask;
use App\Models\EmployeeGoal;
use App\Models\EmployeeTimeOffRequest;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeNotification;
use App\Models\EmployeePayslip;
use App\Models\EmployeeAnnouncement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeePortalSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure employee role exists
        $employeeRole = Role::firstOrCreate(
            ['name' => 'employee'],
            ['slug' => 'employee', 'description' => 'Employee - Company staff with employee portal access']
        );
        // Create Departments (using existing schema - no 'code' column)
        $hrDept = Department::firstOrCreate(
            ['name' => 'Human Resources'],
            ['description' => 'Human Resources Department']
        );

        $techDept = Department::firstOrCreate(
            ['name' => 'Technology'],
            ['description' => 'Technology and Development Department']
        );

        $salesDept = Department::firstOrCreate(
            ['name' => 'Sales'],
            ['description' => 'Sales and Marketing Department']
        );

        // Create Positions (using existing schema - department_id is required, no 'code' column)
        $managerPos = Position::firstOrCreate(
            ['title' => 'Department Manager', 'department_id' => $techDept->id],
            [
                'level' => 3,
                'min_salary' => 80000,
                'max_salary' => 120000,
            ]
        );

        $seniorDevPos = Position::firstOrCreate(
            ['title' => 'Senior Developer', 'department_id' => $techDept->id],
            [
                'level' => 2,
                'min_salary' => 60000,
                'max_salary' => 90000,
            ]
        );

        $devPos = Position::firstOrCreate(
            ['title' => 'Software Developer', 'department_id' => $techDept->id],
            [
                'level' => 1,
                'min_salary' => 40000,
                'max_salary' => 60000,
            ]
        );

        // Create a test user if not exists
        $user = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'John Employee',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign employee role to the user
        if (!$user->hasRole('employee')) {
            $user->assignRole($employeeRole);
        }

        // Create Manager Employee (using existing schema - employee_id instead of employee_number, current_salary instead of salary)
        $manager = Employee::firstOrCreate(
            ['email' => 'sarah.manager@example.com'],
            [
                'employee_id' => 'EMP001',
                'first_name' => 'Sarah',
                'last_name' => 'Manager',
                'phone' => '+260971234567',
                'department_id' => $techDept->id,
                'position_id' => $managerPos->id,
                'hire_date' => now()->subYears(3),
                'employment_status' => 'active',
                'current_salary' => 100000,
            ]
        );

        // Create Employee linked to user
        $employee = Employee::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'user_id' => $user->id,
                'employee_id' => 'EMP002',
                'first_name' => 'John',
                'last_name' => 'Employee',
                'phone' => '+260977654321',
                'department_id' => $techDept->id,
                'position_id' => $devPos->id,
                'manager_id' => $manager->id,
                'hire_date' => now()->subYear(),
                'employment_status' => 'active',
                'current_salary' => 50000,
            ]
        );

        // Create Tasks
        EmployeeTask::firstOrCreate(
            ['title' => 'Complete Employee Portal Dashboard', 'assigned_to' => $employee->id],
            [
                'description' => 'Implement the main dashboard with all widgets and statistics',
                'assigned_by' => $manager->id,
                'department_id' => $techDept->id,
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => now()->addDays(3),
                'estimated_hours' => 16,
            ]
        );

        EmployeeTask::firstOrCreate(
            ['title' => 'Review API Documentation', 'assigned_to' => $employee->id],
            [
                'description' => 'Review and update the API documentation for the new endpoints',
                'assigned_by' => $manager->id,
                'department_id' => $techDept->id,
                'priority' => 'medium',
                'status' => 'todo',
                'due_date' => now()->addDays(7),
                'estimated_hours' => 8,
            ]
        );

        EmployeeTask::firstOrCreate(
            ['title' => 'Fix Login Bug', 'assigned_to' => $employee->id],
            [
                'description' => 'Users are experiencing issues with the login flow',
                'assigned_by' => $manager->id,
                'department_id' => $techDept->id,
                'priority' => 'urgent',
                'status' => 'todo',
                'due_date' => now()->addDay(),
                'estimated_hours' => 4,
            ]
        );

        EmployeeTask::firstOrCreate(
            ['title' => 'Update Unit Tests', 'assigned_to' => $employee->id],
            [
                'description' => 'Add unit tests for the new features',
                'assigned_by' => $manager->id,
                'department_id' => $techDept->id,
                'priority' => 'low',
                'status' => 'completed',
                'due_date' => now()->subDays(2),
                'completed_at' => now()->subDay(),
                'estimated_hours' => 6,
                'actual_hours' => 5,
            ]
        );

        // Create Goals
        EmployeeGoal::firstOrCreate(
            ['employee_id' => $employee->id, 'title' => 'Complete Vue.js Certification'],
            [
                'description' => 'Obtain Vue.js certification to improve frontend skills',
                'category' => 'development',
                'progress' => 65,
                'status' => 'in_progress',
                'start_date' => now()->subMonths(2),
                'due_date' => now()->addMonth(),
                'milestones' => [
                    ['title' => 'Complete basics course', 'completed' => true],
                    ['title' => 'Build practice project', 'completed' => true],
                    ['title' => 'Pass certification exam', 'completed' => false],
                ],
            ]
        );

        EmployeeGoal::firstOrCreate(
            ['employee_id' => $employee->id, 'title' => 'Improve Code Review Participation'],
            [
                'description' => 'Participate in at least 10 code reviews per month',
                'category' => 'performance',
                'progress' => 40,
                'status' => 'in_progress',
                'start_date' => now()->startOfMonth(),
                'due_date' => now()->endOfMonth(),
            ]
        );

        // Create Time Off Request (only if none pending)
        if (!EmployeeTimeOffRequest::where('employee_id', $employee->id)->where('status', 'pending')->exists()) {
            EmployeeTimeOffRequest::create([
                'employee_id' => $employee->id,
                'type' => 'annual',
                'start_date' => now()->addWeeks(2),
                'end_date' => now()->addWeeks(2)->addDays(4),
                'days_requested' => 5,
                'reason' => 'Family vacation',
                'status' => 'pending',
            ]);
        }

        // Create Attendance Records for the past week
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            if (!$date->isWeekend()) {
                EmployeeAttendance::firstOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date->toDateString()],
                    [
                        'clock_in' => '08:30:00',
                        'clock_out' => '17:30:00',
                        'hours_worked' => 8.5,
                        'overtime_hours' => 0.5,
                        'status' => 'present',
                    ]
                );
            }
        }

        // Create Notifications
        EmployeeNotification::firstOrCreate(
            ['employee_id' => $employee->id, 'title' => 'New Task Assigned'],
            [
                'type' => 'task_assigned',
                'message' => 'You have been assigned a new task: Fix Login Bug',
                'action_url' => '/employee/portal/tasks',
            ]
        );

        EmployeeNotification::firstOrCreate(
            ['employee_id' => $employee->id, 'title' => 'Goal Deadline Approaching'],
            [
                'type' => 'goal_reminder',
                'message' => 'Your goal "Complete Vue.js Certification" is due in 30 days',
                'action_url' => '/employee/portal/goals',
            ]
        );

        // Create Payslips for the past 3 months
        for ($i = 2; $i >= 0; $i--) {
            $payDate = now()->subMonths($i)->endOfMonth();
            $periodStart = now()->subMonths($i)->startOfMonth();
            $periodEnd = now()->subMonths($i)->endOfMonth();

            EmployeePayslip::firstOrCreate(
                ['payslip_number' => 'PAY-' . $payDate->format('Ym') . '-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT)],
                [
                    'employee_id' => $employee->id,
                    'pay_period_start' => $periodStart,
                    'pay_period_end' => $periodEnd,
                    'payment_date' => $payDate,
                    'basic_salary' => 50000,
                    'overtime_pay' => rand(0, 5000),
                    'bonus' => $i === 0 ? 2500 : 0,
                    'commission' => 0,
                    'allowances' => 3000,
                    'gross_pay' => 55500,
                    'tax' => 8325,
                    'pension' => 2775,
                    'health_insurance' => 1500,
                    'loan_deduction' => 0,
                    'other_deductions' => 500,
                    'total_deductions' => 13100,
                    'net_pay' => 42400,
                    'status' => 'paid',
                ]
            );
        }

        // Create Announcements
        EmployeeAnnouncement::firstOrCreate(
            ['title' => 'Welcome to the New Employee Portal!'],
            [
                'content' => "We're excited to announce the launch of our new Employee Portal! This platform is designed to make your work life easier by providing quick access to all your HR needs.\n\nKey features include:\n- View and manage your tasks\n- Track your goals and progress\n- Request time off\n- View your payslips\n- Access company documents\n\nIf you have any questions or feedback, please reach out to the HR team.",
                'type' => 'general',
                'priority' => 'high',
                'created_by' => $manager->id,
                'publish_date' => now(),
                'is_pinned' => true,
                'is_active' => true,
            ]
        );

        EmployeeAnnouncement::firstOrCreate(
            ['title' => 'Updated Leave Policy 2025'],
            [
                'content' => "Please be informed that the company leave policy has been updated for 2025.\n\nKey changes:\n- Annual leave increased to 25 days\n- Sick leave now includes mental health days\n- New parental leave benefits\n\nPlease review the full policy document in the Documents section.",
                'type' => 'policy',
                'priority' => 'normal',
                'created_by' => $manager->id,
                'publish_date' => now()->subDays(3),
                'is_active' => true,
            ]
        );

        EmployeeAnnouncement::firstOrCreate(
            ['title' => 'End of Year Party - December 20th'],
            [
                'content' => "Join us for our annual end-of-year celebration!\n\nDate: December 20th, 2025\nTime: 6:00 PM onwards\nVenue: Grand Ballroom, Radisson Blu\n\nDress code: Smart casual\nRSVP by December 15th\n\nLooking forward to celebrating with everyone!",
                'type' => 'event',
                'priority' => 'normal',
                'created_by' => $manager->id,
                'publish_date' => now()->subDays(7),
                'is_active' => true,
            ]
        );

        $this->command->info('Employee Portal seeded successfully!');
        $this->command->info('Login with: employee@example.com / password');
    }
}
