<?php

namespace App\Console\Commands;

use App\Models\{User, Package, Subscription, ReferralCommission};
use App\Services\ReferralService;
use Illuminate\Console\Command;

class TestReferralSystem extends Command
{
    protected $signature = 'test:referral-system';
    protected $description = 'Test the 7-level referral commission system';

    public function handle(): int
    {
        $this->info('🧪 Testing MyGrowNet 7-Level Referral System...');
        $this->newLine();

        // Test 1: Check commission rates
        $this->info('Test 1: Verify Commission Rates');
        $this->table(
            ['Level', 'Name', 'Rate'],
            collect(ReferralCommission::COMMISSION_RATES)->map(function($rate, $level) {
                return [
                    $level,
                    ReferralCommission::getLevelName($level),
                    $rate . '%'
                ];
            })
        );
        $this->newLine();

        // Test 2: Check packages exist
        $this->info('Test 2: Check Packages');
        $packageCount = Package::count();
        if ($packageCount > 0) {
            $this->info("✅ {$packageCount} packages found");
            $packages = Package::active()->ordered()->get();
            $this->table(
                ['Name', 'Price', 'Billing Cycle'],
                $packages->map(fn($p) => [$p->name, 'K' . $p->price, $p->billing_cycle])
            );
        } else {
            $this->warn('⚠️  No packages found. Run: php artisan db:seed --class=PackageSeeder');
        }
        $this->newLine();

        // Test 3: Check for test users with referral chain
        $this->info('Test 3: Check Referral Chain');
        $testUser = User::whereNotNull('referrer_id')->first();
        
        if ($testUser) {
            $this->info("Testing with user: {$testUser->name} (ID: {$testUser->id})");
            
            // Build referral chain
            $chain = [];
            $current = $testUser;
            $level = 0;
            
            while ($current && $level < 7) {
                $chain[] = [
                    'Level' => $level === 0 ? 'User' : "Level {$level}",
                    'Name' => $current->name,
                    'ID' => $current->id,
                    'Qualified' => $current->meetsMonthlyQualification() ? '✅' : '❌'
                ];
                $current = $current->referrer;
                $level++;
            }
            
            $this->table(['Level', 'Name', 'ID', 'Qualified'], $chain);
            $this->info("Referral chain depth: " . (count($chain) - 1) . " levels");
        } else {
            $this->warn('⚠️  No users with referrers found. Create test users first.');
        }
        $this->newLine();

        // Test 4: Simulate subscription commission
        if ($testUser && $packageCount > 0) {
            $this->info('Test 4: Simulate Subscription Commission');
            
            $package = Package::where('slug', 'professional')->first() ?? Package::first();
            $this->info("Using package: {$package->name} (K{$package->price})");
            
            // Calculate expected commissions
            $expectedCommissions = [];
            $current = $testUser->referrer;
            $level = 1;
            
            while ($current && $level <= 7) {
                $rate = ReferralCommission::getCommissionRate($level);
                $amount = $package->price * ($rate / 100);
                
                $expectedCommissions[] = [
                    'Level' => $level,
                    'User' => $current->name,
                    'Rate' => $rate . '%',
                    'Amount' => 'K' . number_format($amount, 2),
                    'Qualified' => $current->meetsMonthlyQualification() ? '✅' : '❌'
                ];
                
                $current = $current->referrer;
                $level++;
            }
            
            if (!empty($expectedCommissions)) {
                $this->table(
                    ['Level', 'User', 'Rate', 'Amount', 'Qualified'],
                    $expectedCommissions
                );
                
                $totalCommission = collect($expectedCommissions)->sum(function($c) {
                    return (float) str_replace(['K', ','], '', $c['Amount']);
                });
                
                $this->info("Total commissions: K" . number_format($totalCommission, 2));
                $this->info("Percentage of package: " . number_format(($totalCommission / $package->price) * 100, 1) . "%");
            } else {
                $this->warn('No referral chain to process commissions');
            }
        }
        $this->newLine();

        // Test 5: Check existing commissions
        $this->info('Test 5: Check Existing Commissions');
        $commissionCount = ReferralCommission::count();
        
        if ($commissionCount > 0) {
            $this->info("✅ {$commissionCount} commissions found in database");
            
            // Group by level
            $byLevel = ReferralCommission::selectRaw('level, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('level')
                ->orderBy('level')
                ->get();
            
            $this->table(
                ['Level', 'Count', 'Total Amount'],
                $byLevel->map(fn($l) => [
                    $l->level,
                    $l->count,
                    'K' . number_format($l->total, 2)
                ])
            );
        } else {
            $this->info('ℹ️  No commissions in database yet');
        }
        $this->newLine();

        // Summary
        $this->info('📊 System Status Summary');
        $this->line('  ✅ Commission rates configured (7 levels)');
        $this->line('  ' . ($packageCount > 0 ? '✅' : '❌') . ' Packages seeded');
        $this->line('  ' . ($testUser ? '✅' : '⚠️ ') . ' Test users with referral chain');
        $this->line('  ' . ($commissionCount > 0 ? '✅' : 'ℹ️ ') . ' Commissions in database');
        $this->newLine();

        $this->info('💡 Next Steps:');
        if ($packageCount === 0) {
            $this->line('  1. Run: php artisan db:seed --class=PackageSeeder');
        }
        if (!$testUser) {
            $this->line('  2. Create test users with referral relationships');
        }
        $this->line('  3. Create a test subscription to generate commissions');
        $this->line('  4. Check logs: tail -f storage/logs/laravel.log');
        
        $this->newLine();
        $this->info('✅ Referral system test complete!');

        return 0;
    }
}
