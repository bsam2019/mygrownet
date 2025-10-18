<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Investment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvestmentReportController extends Controller
{
    public function index(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $summary = [
            'total_investments' => Investment::where('status', 'active')->sum('amount'),
            'total_investors' => Investment::where('status', 'active')
                ->distinct('user_id')
                ->count(),
            'new_investments' => Investment::whereBetween('created_at', [
                $dateRange['start'],
                $dateRange['end']
            ])->count(),
            'total_returns_paid' => Transaction::where('transaction_type', 'return')
                ->where('status', 'completed')
                ->sum('amount')
        ];

        $investmentsByCategory = Investment::where('status', 'active')
            ->select('category_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->with('category')
            ->groupBy('category_id')
            ->get();

        $monthlyInvestments = Investment::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->whereBetween('created_at', [
                now()->subMonths(11)->startOfMonth(),
                now()->endOfMonth()
            ])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Admin/Reports/Investments', [
            'summary' => $summary,
            'investmentsByCategory' => $investmentsByCategory,
            'monthlyInvestments' => $monthlyInvestments,
            'dateRange' => [
                'start' => $dateRange['start']->format('Y-m-d'),
                'end' => $dateRange['end']->format('Y-m-d')
            ]
        ]);
    }

    public function performance(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $returns = Transaction::where('transaction_type', 'return')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total_returns')
            )
            ->groupBy('date')
            ->get();

        $withdrawals = Transaction::where('transaction_type', 'withdrawal')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total_withdrawals')
            )
            ->groupBy('date')
            ->get();

        return Inertia::render('Admin/Reports/Performance', [
            'returns' => $returns,
            'withdrawals' => $withdrawals,
            'dateRange' => [
                'start' => $dateRange['start']->format('Y-m-d'),
                'end' => $dateRange['end']->format('Y-m-d')
            ]
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:investments,returns,performance',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from'
        ]);

        $dateRange = [
            Carbon::parse($request->date_from)->startOfDay(),
            Carbon::parse($request->date_to)->endOfDay()
        ];

        $data = match($request->type) {
            'investments' => $this->getInvestmentsData($dateRange),
            'returns' => $this->getReturnsData($dateRange),
            'performance' => $this->getPerformanceData($dateRange)
        };

        // Generate CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$request->type}_report.csv"
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, array_keys($data[0]));

            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getDateRange(Request $request)
    {
        $start = $request->date_from ?
            Carbon::parse($request->date_from)->startOfDay() :
            now()->startOfMonth();

        $end = $request->date_to ?
            Carbon::parse($request->date_to)->endOfDay() :
            now()->endOfDay();

        return ['start' => $start, 'end' => $end];
    }

    private function getInvestmentsData($dateRange)
    {
        return Investment::with(['user', 'category'])
            ->whereBetween('created_at', $dateRange)
            ->get()
            ->map(function($investment) {
                return [
                    'Date' => $investment->created_at->format('Y-m-d'),
                    'Investor' => $investment->user->name,
                    'Category' => $investment->category->name,
                    'Amount' => number_format($investment->amount, 2),
                    'Status' => $investment->status,
                    'Returns Paid' => number_format($investment->returns_paid, 2)
                ];
            });
    }

    private function getReturnsData($dateRange)
    {
        return Transaction::with(['user', 'investment'])
            ->where('transaction_type', 'return')
            ->whereBetween('created_at', $dateRange)
            ->get()
            ->map(function($transaction) {
                return [
                    'Date' => $transaction->created_at->format('Y-m-d'),
                    'Investor' => $transaction->user->name,
                    'Investment ID' => $transaction->investment_id,
                    'Amount' => number_format($transaction->amount, 2),
                    'Status' => $transaction->status
                ];
            });
    }

    private function getPerformanceData($dateRange)
    {
        return DB::table('transactions')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN transaction_type = "return" THEN amount ELSE 0 END) as returns'),
                DB::raw('SUM(CASE WHEN transaction_type = "withdrawal" THEN amount ELSE 0 END) as withdrawals')
            )
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'completed')
            ->groupBy('date')
            ->get()
            ->map(function($row) {
                return [
                    'Date' => $row->date,
                    'Returns' => number_format($row->returns, 2),
                    'Withdrawals' => number_format($row->withdrawals, 2),
                    'Net Flow' => number_format($row->returns - $row->withdrawals, 2)
                ];
            });
    }
}
