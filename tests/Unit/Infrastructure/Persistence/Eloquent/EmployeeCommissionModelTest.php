<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeCommissionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_amount_and_amount_are_synced(): void
    {
        $commission = EmployeeCommissionModel::factory()->create([
            'amount' => 1000.00
        ]);

        // Both amount and commission_amount should be the same
        $this->assertEquals(1000.00, $commission->amount);
        $this->assertEquals(1000.00, $commission->commission_amount);
    }

    public function test_setting_commission_amount_updates_amount(): void
    {
        $commission = EmployeeCommissionModel::factory()->create();
        
        $commission->commission_amount = 2500.00;
        $commission->save();
        
        $commission->refresh();
        
        $this->assertEquals(2500.00, $commission->amount);
        $this->assertEquals(2500.00, $commission->commission_amount);
    }

    public function test_setting_amount_updates_commission_amount(): void
    {
        $commission = EmployeeCommissionModel::factory()->create();
        
        $commission->amount = 3500.00;
        $commission->save();
        
        $commission->refresh();
        
        $this->assertEquals(3500.00, $commission->amount);
        $this->assertEquals(3500.00, $commission->commission_amount);
    }

    public function test_can_query_by_commission_amount(): void
    {
        EmployeeCommissionModel::factory()->create(['amount' => 1000.00]);
        EmployeeCommissionModel::factory()->create(['amount' => 2000.00]);
        EmployeeCommissionModel::factory()->create(['amount' => 3000.00]);

        $total = EmployeeCommissionModel::sum('commission_amount');
        $this->assertEquals(6000.00, $total);

        $highCommissions = EmployeeCommissionModel::where('commission_amount', '>', 1500)->count();
        $this->assertEquals(2, $highCommissions);
    }

    public function test_commission_amount_is_cast_to_float(): void
    {
        $commission = EmployeeCommissionModel::factory()->create([
            'amount' => '1234.56'
        ]);

        $this->assertIsFloat($commission->commission_amount);
        $this->assertEquals(1234.56, $commission->commission_amount);
    }

    public function test_status_and_payment_status_are_synced(): void
    {
        $commission = EmployeeCommissionModel::factory()->create([
            'payment_status' => 'paid'
        ]);

        $this->assertEquals('paid', $commission->status);
        $this->assertEquals('paid', $commission->payment_status);

        $commission->status = 'pending';
        $commission->save();
        $commission->refresh();

        $this->assertEquals('pending', $commission->status);
        $this->assertEquals('pending', $commission->payment_status);
    }

    public function test_calculation_date_and_earned_date_are_synced(): void
    {
        $date = now()->toDateString();
        $commission = EmployeeCommissionModel::factory()->create([
            'earned_date' => $date
        ]);

        $this->assertEquals($date, $commission->calculation_date->toDateString());
        $this->assertEquals($date, $commission->earned_date->toDateString());

        $newDate = now()->addDays(1)->toDateString();
        $commission->calculation_date = $newDate;
        $commission->save();
        $commission->refresh();

        $this->assertEquals($newDate, $commission->calculation_date->toDateString());
        $this->assertEquals($newDate, $commission->earned_date->toDateString());
    }

    public function test_can_query_by_status_and_calculation_date(): void
    {
        EmployeeCommissionModel::factory()->create([
            'amount' => 1000.00,
            'payment_status' => 'paid',
            'earned_date' => now()
        ]);

        EmployeeCommissionModel::factory()->create([
            'amount' => 2000.00,
            'payment_status' => 'pending',
            'earned_date' => now()
        ]);

        // Test status query
        $paidTotal = EmployeeCommissionModel::where('status', 'paid')->sum('commission_amount');
        $this->assertEquals(1000.00, $paidTotal);

        // Test calculation_date query
        $monthTotal = EmployeeCommissionModel::whereMonth('calculation_date', now()->month)
            ->sum('commission_amount');
        $this->assertEquals(3000.00, $monthTotal);

        // Test combined query (like the original failing query)
        $combinedTotal = EmployeeCommissionModel::where('status', 'paid')
            ->whereMonth('calculation_date', now()->month)
            ->sum('commission_amount');
        $this->assertEquals(1000.00, $combinedTotal);
    }
}