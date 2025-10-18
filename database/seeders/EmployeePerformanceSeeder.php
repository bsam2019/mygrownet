<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeePerformanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = EmployeeModel::with(['position', 'department'])->get();
        $currentDate = Carbon::now();

        foreach ($employees as $employee) {
            // Create performance reviews for the last 3 quarters
            for ($quarter = 0; $quarter < 3; $quarter++) {
                $periodStart = $currentDate->copy()->subMonths(3 * ($quarter + 1))->startOfMonth();
                $periodEnd = $periodStart->copy()->addMonths(3)->subDay();
                
                // Skip if period is in the future
                if ($periodStart->isFuture()) {
                    continue;
                }

                $performanceData = $this->generatePerformanceData($employee, $periodStart, $periodEnd);
                
                EmployeePerformanceModel::create($performanceData);
            }
        }
    }

    private function generatePerformanceData(EmployeeModel $employee, Carbon $periodStart, Carbon $periodEnd): array
    {
        $isFieldAgent = str_contains($employee->position->title, 'Field Agent');
        $isManager = str_contains($employee->position->title, 'Manager') || 
                    str_contains($employee->position->title, 'Director');
        $isCommissionEligible = $employee->position->commission_eligible;

        // Find a reviewer (manager or HR Manager)
        $reviewer = $employee->manager ?? 
                   EmployeeModel::whereHas('position', function($query) {
                       $query->where('title', 'HR Manager');
                   })->first() ??
                   EmployeeModel::whereHas('position', function($query) {
                       $query->where('title', 'Investment Director');
                   })->first();

        $basePerformance = [
            'employee_id' => $employee->id,
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'evaluation_period' => 'quarterly',
            'reviewer_id' => $reviewer->id,
            'reviewer_comments' => $this->generateReviewNotes($employee),
            'goals_next_period' => json_encode($this->generateGoals($employee)),
            'status' => 'approved',
        ];

        if ($isFieldAgent) {
            // Field agents have higher investment facilitation metrics
            return array_merge($basePerformance, [
                'metrics' => [
                    'investments_facilitated_count' => rand(8, 25),
                    'investments_facilitated_amount' => rand(50000, 200000),
                    'client_retention_rate' => rand(75, 95),
                    'commission_generated' => rand(3000, 15000),
                    'new_client_acquisitions' => rand(5, 20),
                    'goal_achievement_rate' => rand(70, 95),
                ],
                'overall_score' => round(rand(30, 50) / 10, 1), // 3.0 to 5.0
                'rating' => 'good',
            ]);
        } elseif ($isCommissionEligible) {
            // Portfolio managers and other commission-eligible roles
            return array_merge($basePerformance, [
                'metrics' => [
                    'investments_facilitated_count' => rand(15, 40),
                    'investments_facilitated_amount' => rand(100000, 500000),
                    'client_retention_rate' => rand(85, 98),
                    'commission_generated' => rand(2000, 12000),
                    'new_client_acquisitions' => rand(3, 12),
                    'goal_achievement_rate' => rand(80, 95),
                ],
                'overall_score' => round(rand(35, 50) / 10, 1), // 3.5 to 5.0
                'rating' => 'excellent',
            ]);
        } elseif ($isManager) {
            // Managers focus on team performance and operational metrics
            return array_merge($basePerformance, [
                'metrics' => [
                    'investments_facilitated_count' => 0,
                    'investments_facilitated_amount' => 0,
                    'client_retention_rate' => rand(90, 98),
                    'commission_generated' => 0,
                    'new_client_acquisitions' => 0,
                    'goal_achievement_rate' => rand(85, 98),
                ],
                'overall_score' => round(rand(38, 50) / 10, 1), // 3.8 to 5.0
                'rating' => 'excellent',
            ]);
        } else {
            // Support staff and other non-commission roles
            return array_merge($basePerformance, [
                'metrics' => [
                    'investments_facilitated_count' => 0,
                    'investments_facilitated_amount' => 0,
                    'client_retention_rate' => rand(85, 95),
                    'commission_generated' => 0,
                    'new_client_acquisitions' => 0,
                    'goal_achievement_rate' => rand(75, 90),
                ],
                'overall_score' => round(rand(32, 48) / 10, 1), // 3.2 to 4.8
                'rating' => 'satisfactory',
            ]);
        }
    }

    private function generateReviewNotes(EmployeeModel $employee): string
    {
        $notes = [
            'Excellent performance this quarter with consistent results.',
            'Shows strong dedication and professional growth.',
            'Meets expectations with room for improvement in client communication.',
            'Outstanding achievement in target goals and team collaboration.',
            'Demonstrates leadership potential and technical expertise.',
            'Consistent performer with reliable results and positive attitude.',
            'Exceeds expectations in most areas with strong client relationships.',
            'Good progress made on development goals with continued improvement.',
        ];

        $specificNotes = [];
        
        if (str_contains($employee->position->title, 'Field Agent')) {
            $specificNotes = [
                'Excellent client acquisition skills and territory management.',
                'Strong performance in investment facilitation and client retention.',
                'Shows great potential for advancement to senior field agent role.',
                'Consistently meets monthly targets with good client feedback.',
            ];
        } elseif (str_contains($employee->position->title, 'Manager')) {
            $specificNotes = [
                'Effective team leadership and strategic planning capabilities.',
                'Successfully managed department objectives and team development.',
                'Strong analytical skills and decision-making under pressure.',
                'Excellent communication with stakeholders and team members.',
            ];
        }

        $allNotes = array_merge($notes, $specificNotes);
        return $allNotes[array_rand($allNotes)];
    }

    private function generateGoals(EmployeeModel $employee): array
    {
        $commonGoals = [
            'Improve professional development through training programs',
            'Enhance communication and collaboration skills',
            'Maintain high performance standards and quality work',
            'Contribute to team objectives and organizational success',
        ];

        $roleSpecificGoals = [];

        if (str_contains($employee->position->title, 'Field Agent')) {
            $roleSpecificGoals = [
                'Increase client acquisition by 15% next quarter',
                'Improve client retention rate to above 90%',
                'Complete advanced sales training certification',
                'Develop expertise in new investment products',
            ];
        } elseif (str_contains($employee->position->title, 'Manager')) {
            $roleSpecificGoals = [
                'Implement new team development initiatives',
                'Improve department efficiency metrics by 10%',
                'Complete leadership development program',
                'Enhance cross-departmental collaboration',
            ];
        } elseif (str_contains($employee->position->title, 'HR')) {
            $roleSpecificGoals = [
                'Streamline employee onboarding process',
                'Implement new performance management system',
                'Improve employee satisfaction scores',
                'Complete HR certification program',
            ];
        } elseif (str_contains($employee->position->title, 'Finance')) {
            $roleSpecificGoals = [
                'Improve financial reporting accuracy and timeliness',
                'Implement cost reduction initiatives',
                'Complete advanced financial analysis training',
                'Enhance budget planning and forecasting',
            ];
        }

        $allGoals = array_merge($commonGoals, $roleSpecificGoals);
        
        // Return 3-4 random goals
        $selectedGoals = array_rand($allGoals, rand(3, 4));
        if (!is_array($selectedGoals)) {
            $selectedGoals = [$selectedGoals];
        }
        
        return array_map(function($index) use ($allGoals) {
            return $allGoals[$index];
        }, $selectedGoals);
    }
}