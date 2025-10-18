<?php

namespace Tests\Feature\Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\EmployeeCommissionSeeder;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeCommissionSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed dependencies
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        $this->artisan('db:seed', ['--class' => DepartmentSeeder::class]);
        $this->artisan('db:seed', ['--class' => PositionSeeder::class]);
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);
        
        // Create some users for commission relationships
        User::factory()->count(10)->create();
    }

    public function test_commission_seeder_creates_commission_records(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeCommissionSeeder::class]);

        $this->assertGreaterThan(0, EmployeeCommissionModel::count());
    }

    public function test_commissions_have_valid_amounts(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeCommissionSeeder::class]);

        $commissions = EmployeeCommissionModel::all();

        foreach ($commissions as $commission) {
            // Commission amount should be positive
            $this->assertGreaterThan(0, $commission->commission_amount);
            
            // Investment amount should be positive
            $this->assertGreaterThan(0, $commission->investment_amount);
            
            // Commission should be reasonable percentage of investment
            $commissionPercentage = ($commission->commission_amount / $commission->investment_amount) * 100;
            $this->assertLessThanOrEqual(50, $commissionPercentage); // Max 50%
            $this->assertGreaterThan(0, $commissionPercentage);
        }
    }

    public function test_commissions_have_valid_rates(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeCommissionSeeder::class]);

        $commissions = EmployeeCommissionModel::all();

        foreach ($commissions as $commission) {
            $this->assertGreaterThan(0, $commission->commission_rate);
            $this->assertLessThanOrEqual(100, $commission->commission_rate);
        }
    }

    public function test_commissions_have_valid_relationships(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeCommissionSeeder::class]);

        $commissions = EmployeeCommissionModel::with(['employee', 'user'])->get();

        foreach ($commissions as $commission) {
            // Employee must exist
            $this->assertNotNull($commission->employee);
            $this->assertInstanceOf(EmployeeModel::class, $commission->employee);
            
            // User (client) must exist
            $this->assertNotNull($commission->user);
            $this->assertInstanceOf(User::class, $commission->user);
        }
    }

    public function test_commissions_have_valid_status(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeCommissionSeeder::class]);

        $commissions = EmployeeCommissionModel::all();
        $validStatuses = ['pending', 'paid', 'cancelled'];

        foreach ($commissions as $commission) {
            $this->assertContains($commission->status, $validStatuses);
        }
    }

    public function test_commissions_have_valid_dates(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeCommissionSeeder::class]);

        $commissions = EmployeeCommissionModel::all();

        foreach ($commissions as $commission) {
            $this->assertNotNull($commission->earned_at);
            $this->assertLessThanOrEqual(now(), $commission->earned_at);
            
            if ($commission->paid_at) {
                $this->assertGreaterThanOrEqual($commission->earned_at, $commission->paid_at);
            }
        }
    }

    public function test_field_agents_have_more_commissions(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeCommissionSeeder::class]);

        $fieldAgents = EmployeeModel::whereHas('position', function ($query) {
            $query->where('title', 'Field Agent');
        })->get();

        $otherEmployees = EmployeeModel::whereHas('position', function ($query) {
            $query->where('title', '!=', 'Field Agent');
        })->get();

        if ($fieldAgents->count() > 0 && $otherEmployees->count() > 0) {
            $fieldAgentCommissions = EmployeeCommissionModel::whereIn('employee_id', $fieldAgents->pluck('id'))->count();
            $otherCommissions = EmployeeCommissionModel::whereIn('employee_id', $otherEmployees->pluck('id'))->count();

            $avgFieldAgentCommissions = $fieldAgentCommissions / $fieldAgents->count();
            $avgOtherCommissions = $otherCommissions / $otherEmployees->count();

            // Field agents should have more commissions on average
            $this->assertGreaterThan($avgOtherCommissions, $avgFieldAgentCommissions);
        }
    }

    public function test_commission_types_are_valid(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeCommissionSeeder::class]);

        $commissions = EmployeeCommissionModel::whereNotNull('commission_type')->get();
        $validTypes = ['direct_sale', 'referral', 'override', 'bonus'];

        foreach ($commissions as $commission) {
            $this->assertContains($commission->commission_type, $validTypes);
        }
    }

    public function test_commissions_are_distributed_over_time(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeCommissionSeeder::class]);

        $commissions = EmployeeCommissionModel::all();
        $commissionMonths = $commissions->pluck('earned_at')->map(function ($date) {
            return $date->format('Y-m');
        })->unique();

        // Should have commissions across multiple months
        $this->assertGreaterThan(1, $commissionMonths->count());
    }

    public function test_paid_commissions_have_payment_dates(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeeCommissionSeeder::class]);

        $paidCommissions = EmployeeCommissionModel::where('status', 'paid')->get();

        foreach ($paidCommissions as $commission) {
            $this->assertNotNull($commission->paid_at);
            $this->assertGreaterThanOrEqual($commission->earned_at, $commission->paid_at);
        }
    }
}