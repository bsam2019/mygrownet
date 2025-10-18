<?php

namespace App\Domain\Investment\Repositories;

use App\Models\User;
use App\Models\Investment;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

interface InvestmentRepositoryInterface
{
    /**
     * Find active investments by user
     */
    public function findActiveInvestmentsByUser(User $user): Collection;

    /**
     * Calculate total investment pool amount
     */
    public function getTotalInvestmentPool(): float;

    /**
     * Get investments within a date range
     */
    public function getInvestmentsByDateRange(Carbon $start, Carbon $end): Collection;

    /**
     * Calculate user's percentage of total investment pool
     */
    public function calculateUserPoolPercentage(User $user): float;

    /**
     * Get total active investments amount
     */
    public function getTotalActiveInvestmentsAmount(): float;

    /**
     * Find investments by status
     */
    public function findInvestmentsByStatus(string $status): Collection;

    /**
     * Get user's total investment amount
     */
    public function getUserTotalInvestmentAmount(User $user): float;

    /**
     * Find investments eligible for profit distribution
     */
    public function findEligibleForProfitDistribution(): Collection;

    /**
     * Get investment statistics for a date range
     */
    public function getInvestmentStatistics(Carbon $start, Carbon $end): array;
}