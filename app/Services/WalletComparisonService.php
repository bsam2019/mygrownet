<?php

namespace App\Services;

use App\Domain\Wallet\Services\DomainWalletService;
use App\Domain\Wallet\Services\UnifiedWalletService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Wallet Comparison Service
 * 
 * Compares results between old (UnifiedWalletService) and new (DomainWalletService)
 * implementations to ensure they produce identical results.
 * 
 * Used during Phase 2 parallel running.
 */
class WalletComparisonService
{
    public function __construct(
        private readonly UnifiedWalletService $unifiedService,
        private readonly DomainWalletService $domainService
    ) {}

    /**
     * Compare balance calculations between both services
     */
    public function compareBalance(User $user): array
    {
        $oldBalance = $this->unifiedService->calculateBalance($user);
        $newBalance = $this->domainService->getBalance($user)->amount();
        
        $difference = abs($oldBalance - $newBalance);
        $matches = $difference < 0.01; // Account for floating point precision
        
        $result = [
            'user_id' => $user->id,
            'old_balance' => $oldBalance,
            'new_balance' => $newBalance,
            'difference' => $difference,
            'matches' => $matches,
            'timestamp' => now()->toIso8601String(),
        ];
        
        if (!$matches) {
            Log::warning('Wallet balance mismatch detected', $result);
        }
        
        return $result;
    }

    /**
     * Compare breakdown between both services
     */
    public function compareBreakdown(User $user): array
    {
        $oldBreakdown = $this->unifiedService->getWalletBreakdown($user);
        $newBreakdown = $this->domainService->getBreakdown($user);
        
        // Convert Money objects to floats for comparison
        $newBalance = $newBreakdown['balance']->amount();
        $newCredits = $newBreakdown['credits']['total']->amount();
        $newDebits = $newBreakdown['debits']['total']->amount();
        
        $balanceMatches = abs($oldBreakdown['balance'] - $newBalance) < 0.01;
        $creditsMatch = abs($oldBreakdown['credits']['total'] - $newCredits) < 0.01;
        $debitsMatch = abs($oldBreakdown['debits']['total'] - $newDebits) < 0.01;
        
        $result = [
            'user_id' => $user->id,
            'balance_matches' => $balanceMatches,
            'credits_match' => $creditsMatch,
            'debits_match' => $debitsMatch,
            'all_match' => $balanceMatches && $creditsMatch && $debitsMatch,
            'old' => [
                'balance' => $oldBreakdown['balance'],
                'credits' => $oldBreakdown['credits']['total'],
                'debits' => $oldBreakdown['debits']['total'],
            ],
            'new' => [
                'balance' => $newBalance,
                'credits' => $newCredits,
                'debits' => $newDebits,
            ],
            'timestamp' => now()->toIso8601String(),
        ];
        
        if (!$result['all_match']) {
            Log::warning('Wallet breakdown mismatch detected', $result);
        }
        
        return $result;
    }

    /**
     * Run comprehensive comparison for a user
     */
    public function runFullComparison(User $user): array
    {
        return [
            'balance' => $this->compareBalance($user),
            'breakdown' => $this->compareBreakdown($user),
        ];
    }

    /**
     * Run comparison for multiple users
     */
    public function compareMultipleUsers(array $userIds): array
    {
        $results = [];
        $mismatches = 0;
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;
            
            $comparison = $this->runFullComparison($user);
            $results[] = $comparison;
            
            if (!$comparison['balance']['matches'] || !$comparison['breakdown']['all_match']) {
                $mismatches++;
            }
        }
        
        return [
            'total_users' => count($results),
            'mismatches' => $mismatches,
            'success_rate' => count($results) > 0 
                ? round((count($results) - $mismatches) / count($results) * 100, 2) 
                : 0,
            'results' => $results,
        ];
    }
}
