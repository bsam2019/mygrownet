<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard/Index', [
            'summary' => $this->getSummaryData(),
            'stats' => $this->getStatsData(),
            'investmentDistribution' => $this->getInvestmentDistribution(),
            'alerts' => $this->getAlerts(),
            'investments' => $this->getRecentInvestments(),
        ]);
    }

    private function getSummaryData()
    {
        return [
            'total_value' => Investment::sum('amount'),
            'total_count' => User::where('role', 'investor')->count(),
            'active_count' => Investment::where('status', 'active')->count(),
            'active_value' => Investment::where('status', 'active')->sum('amount'),
            'revenue' => Investment::sum('platform_fee'),
            'revenue_growth' => $this->calculateRevenueGrowth(),
        ];
    }

    private function getStatsData()
    {
        return [
            'monthly_investment' => Investment::whereMonth('created_at', now()->month)->sum('amount'),
            'investment_growth' => $this->calculateInvestmentGrowth(),
            'new_users' => User::whereMonth('created_at', now()->month)->count(),
            'user_growth' => $this->calculateUserGrowth(),
            'success_rate' => $this->calculateSuccessRate(),
            'completed_investments' => Investment::where('status', 'completed')->count(),
        ];
    }

    private function getInvestmentDistribution()
    {
        return \DB::table('investments')
            ->join('investment_categories', 'investments.category_id', '=', 'investment_categories.id')
            ->select(
                'investment_categories.id',
                'investment_categories.name',
                \DB::raw('COUNT(*) as count'),
                \DB::raw('SUM(amount) as total_value')
            )
            ->groupBy('investment_categories.id', 'investment_categories.name')
            ->get()
            ->map(function ($category) {
                $category->color = $this->getCategoryColor($category->id);
                $category->percentage = round(($category->total_value / Investment::sum('amount')) * 100, 1);
                return $category;
            });
    }

    private function getAlerts()
    {
        return [
            'pending_approvals' => Investment::where('status', 'pending')->count(),
            'pending_withdrawals' => \App\Models\Withdrawal::where('status', 'pending')->count(),
            'withdrawal_amount' => \App\Models\Withdrawal::where('status', 'pending')->sum('amount'),
            'system_alerts' => $this->getSystemAlerts(),
        ];
    }

    private function getRecentInvestments()
    {
        return Investment::with(['user', 'category'])
            ->latest()
            ->take(10)
            ->get();
    }

    private function calculateRevenueGrowth()
    {
        $currentMonth = Investment::whereMonth('created_at', now()->month)->sum('platform_fee');
        $lastMonth = Investment::whereMonth('created_at', now()->subMonth()->month)->sum('platform_fee');
        
        return $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;
    }

    private function calculateInvestmentGrowth()
    {
        $currentMonth = Investment::whereMonth('created_at', now()->month)->sum('amount');
        $lastMonth = Investment::whereMonth('created_at', now()->subMonth()->month)->sum('amount');
        
        return $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;
    }

    private function calculateUserGrowth()
    {
        $currentMonth = User::whereMonth('created_at', now()->month)->count();
        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)->count();
        
        return $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;
    }

    private function calculateSuccessRate()
    {
        $total = Investment::whereIn('status', ['completed', 'failed'])->count();
        $successful = Investment::where('status', 'completed')->count();
        
        return $total > 0 ? round(($successful / $total) * 100, 1) : 0;
    }

    private function getSystemAlerts()
    {
        // This would typically come from your system monitoring service
        // This is just a placeholder implementation
        return [];
    }

    private function getCategoryColor($categoryId)
    {
        // Map category IDs to specific colors
        $colors = [
            1 => '#4F46E5', // Indigo
            2 => '#7C3AED', // Purple
            3 => '#EC4899', // Pink
            4 => '#F59E0B', // Amber
            5 => '#10B981', // Emerald
        ];

        return $colors[$categoryId] ?? '#6B7280'; // Gray default
    }
}