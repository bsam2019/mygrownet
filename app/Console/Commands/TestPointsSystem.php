<?php

namespace App\Console\Commands;

use App\Events\CourseCompleted;
use App\Events\ProductSold;
use App\Events\UserReferred;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Console\Command;

class TestPointsSystem extends Command
{
    protected $signature = 'test:points-system';
    protected $description = 'Test the complete points system with all integrations';

    public function handle(PointService $pointService): int
    {
        $this->info('🧪 Testing MyGrowNet Points System...');
        $this->newLine();

        // Get or create test users
        $testUser = User::where('email', 'test@example.com')->first();
        $referrer = User::where('email', 'referrer@example.com')->first();

        if (!$testUser) {
            $this->error('❌ Test user not found. Please create test@example.com first.');
            return 1;
        }

        // Ensure points record exists
        $testUser->points()->firstOrCreate(['user_id' => $testUser->id]);
        if ($referrer) {
            $referrer->points()->firstOrCreate(['user_id' => $referrer->id]);
        }

        $this->info("Testing with user: {$testUser->name} (ID: {$testUser->id})");
        $this->newLine();

        // Test 1: Manual Point Award
        $this->info('Test 1: Manual Point Award');
        try {
            $pointService->awardPoints(
                user: $testUser,
                source: 'manual_test',
                lpAmount: 10,
                mapAmount: 10,
                description: 'Test point award'
            );
            $this->info('✅ Manual point award successful');
        } catch (\Exception $e) {
            $this->error('❌ Manual point award failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 2: Daily Login Points
        $this->info('Test 2: Daily Login Points');
        try {
            $pointService->awardDailyLoginPoints($testUser);
            $this->info('✅ Daily login points awarded');
        } catch (\Exception $e) {
            $this->error('❌ Daily login failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 3: Referral Points (if referrer exists)
        if ($referrer) {
            $this->info('Test 3: Referral Points');
            try {
                event(new UserReferred($referrer, $testUser));
                $this->info('✅ Referral event fired');
            } catch (\Exception $e) {
                $this->error('❌ Referral event failed: ' . $e->getMessage());
            }
            $this->newLine();
        }

        // Test 4: Course Completion Points
        $this->info('Test 4: Course Completion Points');
        try {
            $mockCourse = (object)[
                'id' => 1,
                'title' => 'Test Course',
                'difficulty' => 'basic'
            ];
            event(new CourseCompleted($testUser, $mockCourse));
            $this->info('✅ Course completion event fired');
        } catch (\Exception $e) {
            $this->error('❌ Course completion failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 5: Product Sale Points
        $this->info('Test 5: Product Sale Points');
        try {
            $mockSale = (object)[
                'id' => 1,
                'user' => $testUser,
                'amount' => 500
            ];
            event(new ProductSold($mockSale));
            $this->info('✅ Product sale event fired');
        } catch (\Exception $e) {
            $this->error('❌ Product sale failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Display current points
        $testUser->points->refresh();
        $this->info('📊 Current Points for ' . $testUser->name);
        $this->table(
            ['Metric', 'Value'],
            [
                ['Lifetime Points (LP)', $testUser->points->lifetime_points],
                ['Monthly Points (MAP)', $testUser->points->monthly_points],
                ['Current Streak', $testUser->points->current_streak . ' days'],
                ['Professional Level', $testUser->professional_level],
            ]
        );
        $this->newLine();

        // Display recent transactions
        $transactions = $testUser->pointTransactions()
            ->latest()
            ->take(5)
            ->get();

        if ($transactions->count() > 0) {
            $this->info('📝 Recent Transactions');
            $this->table(
                ['Source', 'LP', 'MAP', 'Description', 'Date'],
                $transactions->map(fn($t) => [
                    $t->source,
                    $t->lp_amount,
                    $t->map_amount,
                    $t->description,
                    $t->created_at->format('Y-m-d H:i')
                ])
            );
        }

        $this->newLine();
        $this->info('✅ Points system test complete!');
        $this->newLine();
        $this->info('💡 Next steps:');
        $this->line('  1. Check admin dashboard: /admin/points');
        $this->line('  2. Check user dashboard: /points');
        $this->line('  3. Test bulk operations in admin panel');
        $this->line('  4. Monitor queue: php artisan queue:work');

        return 0;
    }
}
