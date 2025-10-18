<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = DepartmentModel::all()->keyBy('name');
        $positions = PositionModel::with('department')->get()->groupBy('department.name');

        // Create sample employees with realistic data
        $employees = [
            // HR Department
            [
                'employee_number' => 'EMP001',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@vbif.com',
                'phone' => '+260-97-1234567',
                'address' => '123 Independence Avenue, Lusaka',
                'hire_date' => '2023-01-15',
                'employment_status' => 'active',
                'department_name' => 'Human Resources',
                'position_title' => 'HR Manager',
                'base_salary' => 100000,
                'commission_rate' => 0,
                'performance_rating' => 4.2,
                'notes' => 'Excellent leadership skills and employee relations expertise',
                'user_data' => [
                    'name' => 'Sarah Johnson',
                    'email' => 'sarah.johnson@vbif.com',
                    'password' => Hash::make('password123')
                ]
            ],
            [
                'employee_number' => 'EMP002',
                'first_name' => 'Michael',
                'last_name' => 'Banda',
                'email' => 'michael.banda@vbif.com',
                'phone' => '+260-97-2345678',
                'address' => '456 Cairo Road, Lusaka',
                'hire_date' => '2023-03-20',
                'employment_status' => 'active',
                'department_name' => 'Human Resources',
                'position_title' => 'HR Specialist',
                'base_salary' => 55000,
                'commission_rate' => 0,
                'performance_rating' => 3.8,
                'notes' => 'Strong attention to detail and excellent organizational skills',
                'user_data' => [
                    'name' => 'Michael Banda',
                    'email' => 'michael.banda@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],

            // Investment Management
            [
                'employee_number' => 'EMP003',
                'first_name' => 'Patricia',
                'last_name' => 'Mwanza',
                'email' => 'patricia.mwanza@vbif.com',
                'phone' => '+260-97-3456789',
                'address' => '789 Great East Road, Lusaka',
                'hire_date' => '2022-08-10',
                'employment_status' => 'active',
                'department_name' => 'Investment Management',
                'position_title' => 'Investment Director',
                'base_salary' => 175000,
                'commission_rate' => 2.5,
                'performance_rating' => 4.5,
                'notes' => 'Outstanding investment performance and client relationship management',
                'user_data' => [
                    'name' => 'Patricia Mwanza',
                    'email' => 'patricia.mwanza@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],
            [
                'employee_number' => 'EMP004',
                'first_name' => 'James',
                'last_name' => 'Phiri',
                'email' => 'james.phiri@vbif.com',
                'phone' => '+260-97-4567890',
                'address' => '321 Kafue Road, Lusaka',
                'hire_date' => '2023-02-14',
                'employment_status' => 'active',
                'department_name' => 'Investment Management',
                'position_title' => 'Portfolio Manager',
                'base_salary' => 110000,
                'commission_rate' => 1.5,
                'performance_rating' => 4.0,
                'notes' => 'Excellent analytical skills and client communication',
                'user_data' => [
                    'name' => 'James Phiri',
                    'email' => 'james.phiri@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],

            // Field Operations
            [
                'employee_number' => 'EMP005',
                'first_name' => 'Grace',
                'last_name' => 'Tembo',
                'email' => 'grace.tembo@vbif.com',
                'phone' => '+260-97-5678901',
                'address' => '654 Chilimbulu Road, Lusaka',
                'hire_date' => '2022-11-05',
                'employment_status' => 'active',
                'department_name' => 'Field Operations',
                'position_title' => 'Field Operations Manager',
                'base_salary' => 85000,
                'commission_rate' => 3.0,
                'performance_rating' => 4.3,
                'notes' => 'Exceptional team leadership and field operations expertise',
                'user_data' => [
                    'name' => 'Grace Tembo',
                    'email' => 'grace.tembo@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],
            [
                'employee_number' => 'EMP006',
                'first_name' => 'David',
                'last_name' => 'Mulenga',
                'email' => 'david.mulenga@vbif.com',
                'phone' => '+260-97-6789012',
                'address' => '987 Mumbwa Road, Lusaka',
                'hire_date' => '2023-01-08',
                'employment_status' => 'active',
                'department_name' => 'Field Operations',
                'position_title' => 'Senior Field Agent',
                'base_salary' => 60000,
                'commission_rate' => 5.0,
                'performance_rating' => 4.1,
                'notes' => 'Top performer in client acquisition and mentoring',
                'user_data' => [
                    'name' => 'David Mulenga',
                    'email' => 'david.mulenga@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],
            [
                'employee_number' => 'EMP007',
                'first_name' => 'Mary',
                'last_name' => 'Chanda',
                'email' => 'mary.chanda@vbif.com',
                'phone' => '+260-97-7890123',
                'address' => '147 Kamwala Road, Lusaka',
                'hire_date' => '2023-04-12',
                'employment_status' => 'active',
                'department_name' => 'Field Operations',
                'position_title' => 'Field Agent',
                'base_salary' => 42500,
                'commission_rate' => 7.5,
                'performance_rating' => 3.9,
                'notes' => 'Promising new agent with strong client rapport',
                'user_data' => [
                    'name' => 'Mary Chanda',
                    'email' => 'mary.chanda@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],
            [
                'employee_number' => 'EMP008',
                'first_name' => 'Peter',
                'last_name' => 'Sakala',
                'email' => 'peter.sakala@vbif.com',
                'phone' => '+260-97-8901234',
                'address' => '258 Matero Road, Lusaka',
                'hire_date' => '2023-06-01',
                'employment_status' => 'active',
                'department_name' => 'Field Operations',
                'position_title' => 'Field Agent',
                'base_salary' => 40000,
                'commission_rate' => 7.5,
                'performance_rating' => 3.7,
                'notes' => 'Enthusiastic and hardworking, showing good potential',
                'user_data' => [
                    'name' => 'Peter Sakala',
                    'email' => 'peter.sakala@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],

            // Finance & Accounting
            [
                'employee_number' => 'EMP009',
                'first_name' => 'Elizabeth',
                'last_name' => 'Nyirenda',
                'email' => 'elizabeth.nyirenda@vbif.com',
                'phone' => '+260-97-9012345',
                'address' => '369 Chelstone Road, Lusaka',
                'hire_date' => '2022-09-15',
                'employment_status' => 'active',
                'department_name' => 'Finance & Accounting',
                'position_title' => 'Finance Manager',
                'base_salary' => 100000,
                'commission_rate' => 0,
                'performance_rating' => 4.4,
                'notes' => 'Exceptional financial analysis and reporting capabilities',
                'user_data' => [
                    'name' => 'Elizabeth Nyirenda',
                    'email' => 'elizabeth.nyirenda@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],
            [
                'employee_number' => 'EMP010',
                'first_name' => 'Joseph',
                'last_name' => 'Zulu',
                'email' => 'joseph.zulu@vbif.com',
                'phone' => '+260-97-0123456',
                'address' => '741 Woodlands Road, Lusaka',
                'hire_date' => '2023-05-20',
                'employment_status' => 'active',
                'department_name' => 'Finance & Accounting',
                'position_title' => 'Payroll Administrator',
                'base_salary' => 52500,
                'commission_rate' => 0,
                'performance_rating' => 3.8,
                'notes' => 'Meticulous attention to detail in payroll processing',
                'user_data' => [
                    'name' => 'Joseph Zulu',
                    'email' => 'joseph.zulu@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],

            // IT Department
            [
                'employee_number' => 'EMP011',
                'first_name' => 'Robert',
                'last_name' => 'Mwale',
                'email' => 'robert.mwale@vbif.com',
                'phone' => '+260-97-1357924',
                'address' => '852 Roma Road, Lusaka',
                'hire_date' => '2022-07-01',
                'employment_status' => 'active',
                'department_name' => 'Information Technology',
                'position_title' => 'IT Manager',
                'base_salary' => 110000,
                'commission_rate' => 0,
                'performance_rating' => 4.2,
                'notes' => 'Strong technical leadership and system architecture skills',
                'user_data' => [
                    'name' => 'Robert Mwale',
                    'email' => 'robert.mwale@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],

            // Compliance & Risk
            [
                'employee_number' => 'EMP012',
                'first_name' => 'Catherine',
                'last_name' => 'Lungu',
                'email' => 'catherine.lungu@vbif.com',
                'phone' => '+260-97-2468135',
                'address' => '963 Kabulonga Road, Lusaka',
                'hire_date' => '2023-01-30',
                'employment_status' => 'active',
                'department_name' => 'Compliance & Risk',
                'position_title' => 'Compliance Officer',
                'base_salary' => 90000,
                'commission_rate' => 0,
                'performance_rating' => 4.0,
                'notes' => 'Thorough understanding of regulatory requirements and risk management',
                'user_data' => [
                    'name' => 'Catherine Lungu',
                    'email' => 'catherine.lungu@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],

            // Customer Service
            [
                'employee_number' => 'EMP013',
                'first_name' => 'Andrew',
                'last_name' => 'Kasonde',
                'email' => 'andrew.kasonde@vbif.com',
                'phone' => '+260-97-3691470',
                'address' => '159 Leopards Hill Road, Lusaka',
                'hire_date' => '2023-02-28',
                'employment_status' => 'active',
                'department_name' => 'Customer Service',
                'position_title' => 'Customer Service Manager',
                'base_salary' => 70000,
                'commission_rate' => 0,
                'performance_rating' => 3.9,
                'notes' => 'Excellent customer service skills and team management',
                'user_data' => [
                    'name' => 'Andrew Kasonde',
                    'email' => 'andrew.kasonde@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],
            [
                'employee_number' => 'EMP014',
                'first_name' => 'Ruth',
                'last_name' => 'Mbewe',
                'email' => 'ruth.mbewe@vbif.com',
                'phone' => '+260-97-4815926',
                'address' => '357 Makeni Road, Lusaka',
                'hire_date' => '2023-07-10',
                'employment_status' => 'active',
                'department_name' => 'Customer Service',
                'position_title' => 'Customer Service Representative',
                'base_salary' => 37500,
                'commission_rate' => 0,
                'performance_rating' => 3.6,
                'notes' => 'Friendly and helpful with strong problem-solving skills',
                'user_data' => [
                    'name' => 'Ruth Mbewe',
                    'email' => 'ruth.mbewe@vbif.com',
                    'password' => Hash::make('password123'),
                ]
            ],
        ];

        foreach ($employees as $employeeData) {
            // Create user account first
            $userData = $employeeData['user_data'];
            unset($employeeData['user_data']);
            
            $user = User::create($userData);
            
            // Assign role to user
            if (isset($userData['role'])) {
                $user->assignRole($userData['role']);
            }

            // Get department and position
            $departmentName = $employeeData['department_name'];
            $positionTitle = $employeeData['position_title'];
            unset($employeeData['department_name'], $employeeData['position_title']);

            $department = $departments[$departmentName] ?? null;
            $position = $positions[$departmentName]->firstWhere('title', $positionTitle) ?? null;

            if (!$department || !$position) {
                continue;
            }

            $employeeData['department_id'] = $department->id;
            $employeeData['position_id'] = $position->id;
            $employeeData['user_id'] = $user->id;

            // Set manager relationships (department heads report to Investment Director)
            if ($positionTitle === 'HR Manager' || $positionTitle === 'Finance Manager' || 
                $positionTitle === 'IT Manager' || $positionTitle === 'Customer Service Manager') {
                $investmentDirector = EmployeeModel::whereHas('position', function($query) {
                    $query->where('title', 'Investment Director');
                })->first();
                if ($investmentDirector) {
                    $employeeData['manager_id'] = $investmentDirector->id;
                }
            } elseif ($positionTitle === 'Field Operations Manager') {
                $investmentDirector = EmployeeModel::whereHas('position', function($query) {
                    $query->where('title', 'Investment Director');
                })->first();
                if ($investmentDirector) {
                    $employeeData['manager_id'] = $investmentDirector->id;
                }
            } elseif (in_array($positionTitle, ['HR Specialist'])) {
                $hrManager = EmployeeModel::whereHas('position', function($query) {
                    $query->where('title', 'HR Manager');
                })->first();
                if ($hrManager) {
                    $employeeData['manager_id'] = $hrManager->id;
                }
            } elseif (in_array($positionTitle, ['Portfolio Manager'])) {
                $investmentDirector = EmployeeModel::whereHas('position', function($query) {
                    $query->where('title', 'Investment Director');
                })->first();
                if ($investmentDirector) {
                    $employeeData['manager_id'] = $investmentDirector->id;
                }
            } elseif (in_array($positionTitle, ['Senior Field Agent', 'Field Agent'])) {
                $fieldManager = EmployeeModel::whereHas('position', function($query) {
                    $query->where('title', 'Field Operations Manager');
                })->first();
                if ($fieldManager) {
                    $employeeData['manager_id'] = $fieldManager->id;
                }
            } elseif (in_array($positionTitle, ['Payroll Administrator'])) {
                $financeManager = EmployeeModel::whereHas('position', function($query) {
                    $query->where('title', 'Finance Manager');
                })->first();
                if ($financeManager) {
                    $employeeData['manager_id'] = $financeManager->id;
                }
            } elseif (in_array($positionTitle, ['Customer Service Representative'])) {
                $csManager = EmployeeModel::whereHas('position', function($query) {
                    $query->where('title', 'Customer Service Manager');
                })->first();
                if ($csManager) {
                    $employeeData['manager_id'] = $csManager->id;
                }
            }

            // Map seeder data to database columns
            $mappedData = [
                'employee_id' => $employeeData['employee_number'],
                'first_name' => $employeeData['first_name'],
                'last_name' => $employeeData['last_name'],
                'email' => $employeeData['email'],
                'phone' => $employeeData['phone'],
                'address' => $employeeData['address'],
                'hire_date' => $employeeData['hire_date'],
                'employment_status' => $employeeData['employment_status'],
                'current_salary' => $employeeData['base_salary'],
                'notes' => $employeeData['notes'] ?? null,
                'department_id' => $employeeData['department_id'],
                'position_id' => $employeeData['position_id'],
                'user_id' => $employeeData['user_id'],
                'manager_id' => $employeeData['manager_id'] ?? null,
            ];

            EmployeeModel::create($mappedData);
        }

        // Set department heads
        $this->setDepartmentHeads();
    }

    private function setDepartmentHeads(): void
    {
        $departmentHeadMappings = [
            'Human Resources' => 'HR Manager',
            'Investment Management' => 'Investment Director',
            'Field Operations' => 'Field Operations Manager',
            'Finance & Accounting' => 'Finance Manager',
            'Information Technology' => 'IT Manager',
            'Customer Service' => 'Customer Service Manager',
        ];

        foreach ($departmentHeadMappings as $departmentName => $positionTitle) {
            $department = DepartmentModel::where('name', $departmentName)->first();
            $employee = EmployeeModel::whereHas('position', function($query) use ($positionTitle) {
                $query->where('title', $positionTitle);
            })->whereHas('department', function($query) use ($departmentName) {
                $query->where('name', $departmentName);
            })->first();

            if ($department && $employee) {
                $department->update(['head_employee_id' => $employee->id]);
            }
        }
    }
}