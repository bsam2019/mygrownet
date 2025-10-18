<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MonthlyActivityStatus;
use App\Services\PointService;
use App\Notifications\MonthlyQualificationAtRisk;
use Illuminate\Console\Command;

class CheckMonthlyQualification extends Command
{
    protected $signature = 'points:check-qualification';
    protected $description = 'Check monthly qualification status and send notifications';

    public function handle(PointService $pointService): int
    {
        $this->info('Checking monthly qualification for all users...');

        $usersChecked = 0;
        $usersAtRisk = 0;

        User::with('points')->chunk(100, function ($users) use ($pointService, &$usersChecked, &$usersAtRisk) {
            foreach ($users as $user) {
                if (!$user->points) {
                    continue;
                }

                $qualified = $pointService->checkMonthlyQualification($user);

                // Update or create monthly activity status
                MonthlyActivityStatus::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'month' => now()->month,
                        'year' => now()->year,
                    ],
                    [
                        'map_earned' => $user->points->monthly_points,
                        'map_required' => $pointService->getRequiredMAP($user),
                        'qualified' => $qualified,
                        'performance_tier' => $pointService->getPerformanceTier($user),
                        'commission_bonus_percent' => $pointService->getCommissionBonus($user),
                    ]
                );

                // Send notification if at risk (last 5 days of month)
                if (!$qualified && now()->day >= 25) {
                    $user->notify(new MonthlyQualificationAtRisk());
                    $usersAtRisk++;
                }

                $usersChecked++;
            }
        });

        $this->info("✓ Checked {$usersChecked} users");
        $this->info("⚠ {$usersAtRisk} users notified (at risk)");

        return Command::SUCCESS;
    }
}
