<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeCommissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commissionEligibleEmployees = EmployeeModel::whereHas('position', function($query) {
            $query->where('commission_eligible', true);
        })->get();

        $users = User::where('id', '>', 1)->take(50)->get(); // Get some users for client assignments
        $currentDate = Carbon::now();

        foreach ($commissionEligibleEmployees as $employee) {
            // Generate commissions for the last 6 months
            for ($month = 0; $month < 6; $month++) {
                $monthDate = $currentDate->copy()->subMonths($month);
                
                // Skip future months
                if ($monthDate->isFuture()) {
                    continue;
                }

                $this->generateMonthlyCommissions($employee, $users, $monthDate);
            }
        }
    }

    private function generateMonthlyCommissions(EmployeeModel $employee, $users, Carbon $monthDate): void
    {
        $isFieldAgent = str_contains($employee->position->title, 'Field Agent');
        $isSeniorAgent = str_contains($employee->position->title, 'Senior');
        $commissionRate = $employee->position->commission_rate;

        // Determine number of commissions based on role
        if ($isFieldAgent) {
            $commissionCount = $isSeniorAgent ? rand(8, 15) : rand(5, 12);
        } else {
            $commissionCount = rand(3, 8); // Portfolio managers, etc.
        }

        for ($i = 0; $i < $commissionCount; $i++) {
            $commissionData = $this->generateCommissionData($employee, $users, $monthDate, $commissionRate);
            EmployeeCommissionModel::create($commissionData);
        }

        // Add some performance bonuses quarterly
        if ($monthDate->month % 3 === 0) {
            $bonusData = $this->generatePerformanceBonus($employee, $monthDate);
            if ($bonusData) {
                EmployeeCommissionModel::create($bonusData);
            }
        }
    }

    private function generateCommissionData(EmployeeModel $employee, $users, Carbon $monthDate, float $commissionRate): array
    {
        $commissionTypes = ['investment_facilitation', 'referral'];
        $commissionType = $commissionTypes[array_rand($commissionTypes)];
        
        // Generate realistic investment amounts based on employee level
        $isFieldAgent = str_contains($employee->position->title, 'Field Agent');
        $isSeniorAgent = str_contains($employee->position->title, 'Senior');
        
        if ($isFieldAgent) {
            $baseAmount = $isSeniorAgent ? rand(5000, 25000) : rand(2000, 15000);
        } else {
            $baseAmount = rand(10000, 50000); // Portfolio managers handle larger amounts
        }

        $commissionAmount = $baseAmount * ($commissionRate / 100);
        
        // Random calculation date within the month
        $calculationDate = $monthDate->copy()->addDays(rand(1, $monthDate->daysInMonth));
        
        // Payment date is usually 1-2 weeks after calculation
        $paymentDate = null;
        $status = 'pending';
        
        if ($calculationDate->addWeeks(2)->isPast()) {
            $paymentDate = $calculationDate->copy()->addDays(rand(7, 14));
            $status = rand(0, 10) > 1 ? 'paid' : 'approved'; // 90% paid, 10% approved
        } elseif ($calculationDate->addWeek()->isPast()) {
            $status = 'approved';
        }

        return [
            'employee_id' => $employee->id,
            'investment_id' => null, // Would link to actual investments in real system
            'user_id' => $users->random()->id,
            'commission_type' => $commissionType,
            'base_amount' => $baseAmount,
            'commission_rate' => $commissionRate,
            'commission_amount' => round($commissionAmount, 2),
            'calculation_date' => $calculationDate->format('Y-m-d'),
            'payment_date' => $paymentDate?->format('Y-m-d'),
            'status' => $status,
            'notes' => $this->generateCommissionNotes($commissionType, $baseAmount),
        ];
    }

    private function generatePerformanceBonus(EmployeeModel $employee, Carbon $monthDate): ?array
    {
        // Only generate bonuses for top performers (random chance)
        if (rand(0, 100) > 30) { // 30% chance of bonus
            return null;
        }

        $bonusAmount = rand(1000, 5000);
        $calculationDate = $monthDate->copy()->endOfMonth();
        
        return [
            'employee_id' => $employee->id,
            'investment_id' => null,
            'user_id' => null,
            'commission_type' => 'performance_bonus',
            'base_amount' => 0,
            'commission_rate' => 0,
            'commission_amount' => $bonusAmount,
            'calculation_date' => $calculationDate->format('Y-m-d'),
            'payment_date' => $calculationDate->copy()->addDays(rand(7, 14))->format('Y-m-d'),
            'status' => 'paid',
            'notes' => 'Quarterly performance bonus for exceeding targets',
        ];
    }

    private function generateCommissionNotes(string $commissionType, float $baseAmount): string
    {
        $notes = [
            'investment_facilitation' => [
                "Commission for facilitating K{$baseAmount} investment",
                "Investment facilitation fee for new client onboarding",
                "Commission earned for successful investment placement",
                "Fee for investment advisory and facilitation services",
            ],
            'referral' => [
                "Referral commission for bringing new client with K{$baseAmount} investment",
                "Commission for successful client referral and onboarding",
                "Referral fee for new investor introduction",
                "Commission for expanding client network through referrals",
            ],
        ];

        $typeNotes = $notes[$commissionType] ?? ['Commission payment'];
        return $typeNotes[array_rand($typeNotes)];
    }
}