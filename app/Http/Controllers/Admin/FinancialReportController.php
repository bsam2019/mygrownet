<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancialReportController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $monthStart = $now->startOfMonth();
        $yearStart = $now->copy()->startOfYear();

        return Inertia::render('Admin/Reports/Index', [
            'summary' => [
                'totalInvestments' => Investment::sum('amount'),
                'activeInvestments' => Investment::where('status', 'active')->sum('amount'),
                'monthlyInvestments' => Investment::where('created_at', '>=', $monthStart)->sum('amount'),
                'yearlyInvestments' => Investment::where('created_at', '>=', $yearStart)->sum('amount'),
                'totalWithdrawals' => Transaction::where('transaction_type', 'withdrawal')
                    ->where('status', 'completed')
                    ->sum('amount'),
                'pendingWithdrawals' => Transaction::where('transaction_type', 'withdrawal')
                    ->where('status', 'pending')
                    ->sum('amount'),
                'monthlyReturns' => Transaction::where('transaction_type', 'return')
                    ->where('created_at', '>=', $monthStart)
                    ->sum('amount'),
                'yearlyReturns' => Transaction::where('transaction_type', 'return')
                    ->where('created_at', '>=', $yearStart)
                    ->sum('amount'),
            ],
            'recentTransactions' => Transaction::with(['user', 'investment'])
                ->latest()
                ->take(10)
                ->get()
        ]);
    }

    public function investments(Request $request)
    {
        $query = Investment::with(['user', 'category'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_from, function($query, $date) {
                return $query->where('created_at', '>=', Carbon::parse($date));
            })
            ->when($request->date_to, function($query, $date) {
                return $query->where('created_at', '<=', Carbon::parse($date));
            });

        return Inertia::render('Admin/Reports/Investments', [
            'investments' => $query->latest()->paginate(15),
            'summary' => [
                'total' => $query->sum('amount'),
                'count' => $query->count(),
                'averageAmount' => $query->avg('amount')
            ]
        ]);
    }

    public function withdrawals(Request $request)
    {
        $query = Transaction::with(['user', 'investment'])
            ->where('transaction_type', 'withdrawal')
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_from, function($query, $date) {
                return $query->where('created_at', '>=', Carbon::parse($date));
            })
            ->when($request->date_to, function($query, $date) {
                return $query->where('created_at', '<=', Carbon::parse($date));
            });

        return Inertia::render('Admin/Reports/Withdrawals', [
            'withdrawals' => $query->latest()->paginate(15),
            'summary' => [
                'total' => $query->sum('amount'),
                'count' => $query->count(),
                'averageAmount' => $query->avg('amount'),
                'pendingAmount' => $query->where('status', 'pending')->sum('amount')
            ]
        ]);
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'type' => 'required|in:investments,withdrawals,returns,referrals',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from'
        ]);

        $dateFrom = Carbon::parse($request->date_from);
        $dateTo = Carbon::parse($request->date_to);

        $data = $this->getReportData($request->type, $dateFrom, $dateTo);

        return response()->json([
            'type' => $request->type,
            'date_range' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d')
            ],
            'data' => $data
        ]);
    }

    private function getReportData($type, $dateFrom, $dateTo)
    {
        return match($type) {
            'investments' => Investment::whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('date')
                ->get(),
            'withdrawals' => Transaction::where('transaction_type', 'withdrawal')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('date')
                ->get(),
            'returns' => Transaction::where('transaction_type', 'return')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('date')
                ->get(),
            'referrals' => Transaction::where('transaction_type', 'referral')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('date')
                ->get(),
        };
    }
}
