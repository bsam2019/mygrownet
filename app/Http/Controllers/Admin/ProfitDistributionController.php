<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfitDistribution;
use App\Models\Investment;
use App\Models\User;
use App\Domain\Financial\Services\ProfitDistributionService;
use App\Console\Commands\AnnualProfitDistributionCommand;
use App\Console\Commands\QuarterlyBonusDistributionCommand;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class ProfitDistributionController extends Controller
{
    protected $profitDistributionService;

    public function __construct(ProfitDistributionService $profitDistributionService)
    {
        $this->profitDistributionService = $profitDistributionService;
    }

    public function index(Request $request)
    {
        $distributions = ProfitDistribution::when($request->period_type, function ($query, $type) {
                return $query->where('period_type', $type);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        $stats = $this->getDistributionStats();
        $upcomingDistributions = $this->getUpcomingDistributions();

        return Inertia::render('Admin/ProfitDistribution/Index', [
            'distributions' => $distributions,
            'stats' => $stats,
            'upcoming_distributions' => $upcomingDistributions,
            'filters' => $request->only(['period_type', 'status', 'search'])
        ]);
    }

    public function show(ProfitDistribution $profitDistribution)
    {
        return Inertia::render('Admin/ProfitDistribution/Show', [
            'distribution' => $profitDistribution,
            'calculation_details' => $this->getCalculationDetails($profitDistribution),
            'related_distributions' => $this->getRelatedDistributions($profitDistribution)
        ]);
    }

    public function processAnnualDistribution(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:' . date('Y'),
            'fund_performance' => 'required|numeric|min:0|max:100',
            'total_fund_value' => 'required|numeric|min:0'
        ]);

        try {
            $result = $this->profitDistributionService->processAnnualDistribution(
                $request->year,
                $request->fund_performance,
                $request->total_fund_value
            );

            return back()->with('success', "Annual distribution processed: {$result['total_distributed']} distributed to {$result['recipients_count']} investors");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process annual distribution: ' . $e->getMessage());
        }
    }

    public function processQuarterlyBonus(Request $request)
    {
        $request->validate([
            'quarter' => 'required|integer|min:1|max:4',
            'year' => 'required|integer|min:2020|max:' . date('Y'),
            'bonus_pool' => 'required|numeric|min:0',
            'performance_threshold' => 'required|numeric|min:0|max:100'
        ]);

        try {
            $result = $this->profitDistributionService->processQuarterlyBonus(
                $request->quarter,
                $request->year,
                $request->bonus_pool,
                $request->performance_threshold
            );

            return back()->with('success', "Quarterly bonus processed: {$result['total_distributed']} distributed to {$result['recipients_count']} investors");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process quarterly bonus: ' . $e->getMessage());
        }
    }

    public function previewDistribution(Request $request)
    {
        $request->validate([
            'distribution_type' => 'required|in:annual,quarterly',
            'period' => 'required|string',
            'fund_performance' => 'required_if:distribution_type,annual|numeric|min:0|max:100',
            'bonus_pool' => 'required_if:distribution_type,quarterly|numeric|min:0'
        ]);

        try {
            if ($request->distribution_type === 'annual') {
                $preview = $this->profitDistributionService->previewAnnualDistribution(
                    (int) $request->period,
                    $request->fund_performance
                );
            } else {
                [$quarter, $year] = explode('-', $request->period);
                $preview = $this->profitDistributionService->previewQuarterlyBonus(
                    (int) $quarter,
                    (int) $year,
                    $request->bonus_pool
                );
            }

            return response()->json([
                'success' => true,
                'preview' => $preview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'distribution_ids' => 'required|array',
            'distribution_ids.*' => 'exists:profit_distributions,id'
        ]);

        $approved = 0;
        $errors = [];

        foreach ($request->distribution_ids as $id) {
            try {
                $distribution = ProfitDistribution::find($id);
                if ($distribution->status === 'pending') {
                    $distribution->update(['status' => 'approved']);
                    $approved++;
                }
            } catch (\Exception $e) {
                $errors[] = "Distribution {$id}: " . $e->getMessage();
            }
        }

        $message = "Approved {$approved} distributions";
        if (!empty($errors)) {
            $message .= ". Errors: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    public function analytics(Request $request)
    {
        $period = $request->get('period', 'year');
        $analytics = $this->getDistributionAnalytics($period);

        return Inertia::render('Admin/ProfitDistribution/Analytics', [
            'analytics' => $analytics,
            'period' => $period
        ]);
    }

    public function exportDistributions(Request $request)
    {
        $request->validate([
            'period_type' => 'nullable|in:annual,quarterly',
            'status' => 'nullable|in:pending,processing,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $distributions = ProfitDistribution::when($request->period_type, fn($q, $type) => $q->where('period_type', $type))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->start_date, fn($q, $date) => $q->where('created_at', '>=', $date))
            ->when($request->end_date, fn($q, $date) => $q->where('created_at', '<=', $date))
            ->get();

        // Generate CSV export
        $filename = 'profit_distributions_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ];

        $callback = function() use ($distributions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Period Type', 'Period Start', 'Period End', 'Total Profit', 
                'Distribution Percentage', 'Total Distributed', 'Status', 'Created At', 'Processed At'
            ]);

            foreach ($distributions as $distribution) {
                fputcsv($file, [
                    $distribution->id,
                    $distribution->period_type,
                    $distribution->period_start->format('Y-m-d'),
                    $distribution->period_end->format('Y-m-d'),
                    $distribution->total_profit,
                    $distribution->distribution_percentage,
                    $distribution->total_distributed,
                    $distribution->status,
                    $distribution->created_at->format('Y-m-d H:i:s'),
                    $distribution->processed_at?->format('Y-m-d H:i:s') ?? 'N/A'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getDistributionStats()
    {
        return [
            'total_distributed' => ProfitDistribution::where('status', 'completed')->sum('total_distributed'),
            'pending_distributions' => ProfitDistribution::where('status', 'pending')->count(),
            'pending_amount' => ProfitDistribution::where('status', 'pending')->sum('total_distributed'),
            'this_year_distributed' => ProfitDistribution::where('status', 'completed')
                ->whereYear('created_at', date('Y'))
                ->sum('total_distributed'),
            'distribution_types' => [
                'annual' => ProfitDistribution::where('period_type', 'annual')->count(),
                'quarterly' => ProfitDistribution::where('period_type', 'quarterly')->count(),
            ],
            'recent_distributions' => ProfitDistribution::where('status', 'completed')
                ->latest()
                ->take(5)
                ->get()
        ];
    }

    private function getUpcomingDistributions()
    {
        $upcoming = [];
        
        // Check if annual distribution is due
        $lastAnnual = ProfitDistribution::where('period_type', 'annual')
            ->whereYear('period_start', date('Y'))
            ->exists();
            
        if (!$lastAnnual && date('m') >= 12) {
            $upcoming[] = [
                'type' => 'annual',
                'period' => date('Y'),
                'due_date' => date('Y') . '-12-31',
                'estimated_recipients' => Investment::where('status', 'active')->distinct('user_id')->count(),
                'status' => 'due'
            ];
        }

        // Check quarterly distributions
        $currentQuarter = ceil(date('n') / 3);
        for ($q = 1; $q <= $currentQuarter; $q++) {
            $quarterStart = Carbon::create(date('Y'), ($q - 1) * 3 + 1, 1)->startOfMonth();
            $quarterEnd = Carbon::create(date('Y'), $q * 3, 1)->endOfMonth();
            
            $exists = ProfitDistribution::where('period_type', 'quarterly')
                ->where('period_start', '>=', $quarterStart)
                ->where('period_end', '<=', $quarterEnd)
                ->exists();
                
            if (!$exists) {
                $upcoming[] = [
                    'type' => 'quarterly',
                    'period' => date('Y') . '-Q' . $q,
                    'due_date' => date('Y') . '-' . ($q * 3) . '-' . date('t', mktime(0, 0, 0, $q * 3, 1, date('Y'))),
                    'estimated_recipients' => Investment::where('status', 'active')->distinct('user_id')->count(),
                    'status' => $q < $currentQuarter ? 'overdue' : 'upcoming'
                ];
            }
        }

        return $upcoming;
    }

    private function getCalculationDetails(ProfitDistribution $distribution)
    {
        $notes = json_decode($distribution->notes, true) ?? [];
        
        return [
            'total_profit' => $distribution->total_profit,
            'distribution_percentage' => $distribution->distribution_percentage,
            'total_distributed' => $distribution->total_distributed,
            'period_start' => $distribution->period_start,
            'period_end' => $distribution->period_end,
            'calculation_method' => $notes['calculation_method'] ?? 'standard',
            'user_count' => $notes['user_count'] ?? 0,
            'total_investment_pool' => $notes['total_investment_pool'] ?? 0
        ];
    }

    private function getRelatedDistributions(ProfitDistribution $distribution)
    {
        return ProfitDistribution::where('period_type', $distribution->period_type)
            ->where('id', '!=', $distribution->id)
            ->latest()
            ->take(10)
            ->get();
    }

    private function getDistributionAnalytics($period)
    {
        $startDate = match($period) {
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            'all' => now()->subYears(5),
            default => now()->subYear()
        };

        return [
            'distribution_trends' => $this->getDistributionTrends($startDate),
            'tier_performance' => $this->getTierDistributionPerformance($startDate),
            'period_comparison' => $this->getPeriodComparison($startDate),
            'top_recipients' => $this->getTopRecipients($startDate),
            'distribution_efficiency' => $this->getDistributionEfficiency($startDate)
        ];
    }

    private function getDistributionTrends($startDate)
    {
        return ProfitDistribution::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total_distributed) as total_amount')
            ->selectRaw('COUNT(*) as distribution_count')
            ->selectRaw('period_type')
            ->groupBy('date', 'period_type')
            ->orderBy('date')
            ->get();
    }

    private function getTierDistributionPerformance($startDate)
    {
        return ProfitDistribution::where('created_at', '>=', $startDate)
            ->selectRaw('period_type')
            ->selectRaw('COUNT(*) as distribution_count')
            ->selectRaw('SUM(total_distributed) as total_distributed')
            ->selectRaw('AVG(total_distributed) as average_distribution')
            ->groupBy('period_type')
            ->get();
    }

    private function getPeriodComparison($startDate)
    {
        return ProfitDistribution::where('created_at', '>=', $startDate)
            ->selectRaw('period_type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total_distributed) as total_amount')
            ->selectRaw('AVG(total_distributed) as average_amount')
            ->groupBy('period_type')
            ->get();
    }

    private function getTopRecipients($startDate)
    {
        return ProfitDistribution::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->selectRaw('period_type')
            ->selectRaw('COUNT(*) as distribution_count')
            ->selectRaw('SUM(total_distributed) as total_received')
            ->selectRaw('MAX(total_distributed) as highest_distribution')
            ->groupBy('period_type')
            ->orderBy('total_received', 'desc')
            ->get();
    }

    private function getDistributionEfficiency($startDate)
    {
        $totalDistributions = ProfitDistribution::where('created_at', '>=', $startDate)->count();
        $paidDistributions = ProfitDistribution::where('created_at', '>=', $startDate)
            ->where('status', 'completed')->count();
        $pendingDistributions = ProfitDistribution::where('created_at', '>=', $startDate)
            ->where('status', 'pending')->count();

        $averageProcessingTime = DB::table('profit_distributions')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->whereNotNull('processed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, processed_at)) as avg_hours')
            ->value('avg_hours');

        return [
            'total_distributions' => $totalDistributions,
            'success_rate' => $totalDistributions > 0 ? ($paidDistributions / $totalDistributions) * 100 : 0,
            'pending_rate' => $totalDistributions > 0 ? ($pendingDistributions / $totalDistributions) * 100 : 0,
            'average_processing_time_hours' => round($averageProcessingTime ?? 0, 2)
        ];
    }
}