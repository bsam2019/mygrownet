<?php

namespace App\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\Transaction;
use Carbon\Carbon;

class AdminDashboardService
{
    public function getStatistics()
    {
        return [
            'total_users' => $this->getTotalUsers(),
            'total_investments' => $this->getTotalInvestments(),
            'growth_rate' => $this->calculateGrowthRate(),
        ];
    }

    public function getActiveInvestments()
    {
        return Investment::active()->count();
    }

    public function getMonthlyRevenue()
    {
        return Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');
    }

    public function getRecentUsers()
    {
        return User::with(['investments', 'transactions'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function getRecentTransactions()
    {
        return Transaction::with(['user', 'investment'])
            ->latest()
            ->take(5)
            ->get();
    }

    private function getTotalUsers()
    {
        return User::count();
    }

    private function getTotalInvestments()
    {
        return Investment::sum('amount');
    }

    private function calculateGrowthRate()
    {
        $currentMonth = Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        $lastMonth = Transaction::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('amount');

        if ($lastMonth == 0) return 0;
        return (($currentMonth - $lastMonth) / $lastMonth) * 100;
    }
}
