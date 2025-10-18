<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EmployeeManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder orchestrates the complete employee management system setup
     * including departments, positions, employees, performance data, and commissions.
     */
    public function run(): void
    {
        $this->command->info('Seeding Employee Management System...');

        // Seed in proper order to maintain referential integrity
        $this->call([
            // 1. Create organizational structure
            DepartmentSeeder::class,
            PositionSeeder::class,
            
            // 2. Create employees and link to users
            EmployeeSeeder::class,
            
            // 3. Create performance and commission data
            EmployeePerformanceSeeder::class,
            EmployeeCommissionSeeder::class,
            
            // 4. Create client assignments for field agents
            EmployeeClientAssignmentSeeder::class,
        ]);

        $this->command->info('Employee Management System seeded successfully!');
        $this->displaySeedingSummary();
    }

    private function displaySeedingSummary(): void
    {
        $this->command->info('');
        $this->command->info('=== Employee Management Seeding Summary ===');
        
        // Count records created
        $departmentCount = \App\Infrastructure\Persistence\Eloquent\DepartmentModel::count();
        $positionCount = \App\Infrastructure\Persistence\Eloquent\PositionModel::count();
        $employeeCount = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::count();
        $performanceCount = \App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel::count();
        $commissionCount = \App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel::count();
        $assignmentCount = \App\Infrastructure\Persistence\Eloquent\EmployeeClientAssignmentModel::count();

        $this->command->info("Departments created: {$departmentCount}");
        $this->command->info("Positions created: {$positionCount}");
        $this->command->info("Employees created: {$employeeCount}");
        $this->command->info("Performance reviews created: {$performanceCount}");
        $this->command->info("Commission records created: {$commissionCount}");
        $this->command->info("Client assignments created: {$assignmentCount}");
        
        $this->command->info('');
        $this->command->info('Sample employee login credentials:');
        $this->command->info('Email: sarah.johnson@vbif.com (HR Manager)');
        $this->command->info('Email: patricia.mwanza@vbif.com (Investment Director)');
        $this->command->info('Email: grace.tembo@vbif.com (Field Operations Manager)');
        $this->command->info('Email: david.mulenga@vbif.com (Senior Field Agent)');
        $this->command->info('Password: password123 (for all sample employees)');
        $this->command->info('');
    }
}