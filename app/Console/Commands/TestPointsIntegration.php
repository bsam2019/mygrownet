<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Events\UserReferred;
use App\Events\CourseCompleted;
use App\Events\ProductSold;
use App\Events\UserLevelAdvanced;
use App\Services\PointService;
use Illuminate\Console\Command;

class TestPointsIntegration extends Command
{
    protected $signature = 'points:test-integration {user_id?}';
    protected $description = 'Test points system integrations';

    public function handle(PointService $pointService): int
    {
        $userId = $this->argument('user_id') ?? 1;
        $user = User::find($userId);

        if (!$user) {
            $this->error("User #{$userId} not found");
            return 1;
        }

        $this->info("Testing Points System Integrations for: {$user->name}");
        $this->newLine();

        // Get initial points
        $initialLP = $user->points?->lifetime_points ?? 0;
        $initialMAP = $user->points?->monthly_points ?? 0;

        $this->info("Initial Points:");
        $this->line("  LP: {$initialLP}");
        $this->line("  MAP: {$initialMAP}");
        $this->newLine();

        // Test 1: Daily Login
        $this->info("Test 1: Daily Login Points");
        $transaction = $pointService->awardDailyLogin($user);
        if ($transaction) {
            $this->line("  ✓ Awarded 5 MAP for daily login");
        } else {
            $this->line("  ℹ Already logged in today");
        }
        $this->newLine();

        // Test 2: Referral Points (simulated)
        $this->info("Test 2: Referral Points (Simulated)");
        $referee = User::where('id', '!=', $user->id)->first();
        if ($referee) {
            event(new UserReferred($user, $referee));
            $this->line("  ✓ Fired UserReferred event");
            $this->line("  Expected: +150 LP, +150 MAP");
        } else {
            $this->line("  ✗ No other users found for testing");
        }
        $this->newLine();

        // Test 3: Course Completion (simulated)
        $this->info("Test 3: Course Completion (Simulated)");
        $enrollment = $user->courseEnrollments()->first();
        if ($enrollment) {
            event(new CourseCompleted($enrollment));
            $this->line("  ✓ Fired CourseCompleted event");
            $this->line("  Expected: +30-60 LP, +30-60 MAP");
        } else {
            $this->line("  ℹ No course enrollments found");
            $this->line("  Simulating with mock data...");
            $mockEnrollment = (object)[
                'user' => $user,
                'course' => (object)['title' => 'Test Course', 'difficulty_level' => 'basic']
            ];
            event(new CourseCompleted($mockEnrollment));
            $this->line("  ✓ Fired CourseCompleted event with mock data");
        }
        $this->newLine();

        // Test 4: Product Sale (simulated)
        $this->info("Test 4: Product Sale (Simulated)");
        $mockSale = (object)[
            'id' => 999,
            'user' => $user,
            'amount' => 500
        ];
        event(new ProductSold($mockSale));
        $this->line("  ✓ Fired ProductSold event");
        $this->line("  Sale Amount: K500");
        $this->line("  Expected: +50 LP, +50 MAP");
        $this->newLine();

        // Test 5: Level Advancement (simulated)
        $this->info("Test 5: Level Advancement (Simulated)");
        $currentLevel = $user->current_professional_level ?? 'associate';
        $newLevel = 'professional';
        event(new UserLevelAdvanced($user, $currentLevel, $newLevel));
        $this->line("  ✓ Fired UserLevelAdvanced event");
        $this->line("  Old Level: {$currentLevel}");
        $this->line("  New Level: {$newLevel}");
        if ($user->referrer) {
            $this->line("  Expected: +50 LP, +50 MAP to referrer");
        } else {
            $this->line("  ℹ User has no referrer");
        }
        $this->newLine();

        // Wait for queue processing
        $this->info("Waiting for queue processing...");
        sleep(2);

        // Get final points
        $user->refresh();
        $finalLP = $user->points?->lifetime_points ?? 0;
        $finalMAP = $user->points?->monthly_points ?? 0;

        $this->newLine();
        $this->info("Final Points:");
        $this->line("  LP: {$finalLP} (+" . ($finalLP - $initialLP) . ")");
        $this->line("  MAP: {$finalMAP} (+" . ($finalMAP - $initialMAP) . ")");
        $this->newLine();

        // Show recent transactions
        $this->info("Recent Transactions:");
        $transactions = $user->pointTransactions()
            ->latest()
            ->limit(5)
            ->get();

        if ($transactions->isEmpty()) {
            $this->line("  No transactions found");
            $this->newLine();
            $this->warn("Note: If using queues, run 'php artisan queue:work' in another terminal");
        } else {
            foreach ($transactions as $transaction) {
                $this->line("  • {$transaction->source}: +{$transaction->lp_amount} LP, +{$transaction->map_amount} MAP");
                $this->line("    {$transaction->description}");
            }
        }

        $this->newLine();
        $this->info("✓ Integration test completed!");
        $this->line("Check the transactions above to verify points were awarded correctly.");

        return 0;
    }
}
