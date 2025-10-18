<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\ProfitShare;
use App\Models\ReferralCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReportExportController extends Controller
{
    public function exportFinancialReport(Request $request)
    {
        $period = $request->get('period', 'month');
        $format = $request->get('format', 'csv');
        $startDate = $this->getStartDate($period);

        $data = [
            'investments' => $this->getInvestmentData($startDate),
            'profits' => $this->getProfitData($startDate),
            'withdrawals' => $this->getWithdrawalData($startDate)
        ];

        $filename = "financial_report_{$period}_" . now()->format('Y-m-d') . ".{$format}";
        
        return $this->generateReport($data, $format, $filename);
    }

    public function exportMLMReport(Request $request)
    {
        $period = $request->get('period', 'month');
        $format = $request->get('format', 'csv');
        $startDate = $this->getStartDate($period);

        $data = [
            'referral_commissions' => $this->getReferralData($startDate),
            'network_growth' => $this->getNetworkGrowthData($startDate),
            'tier_performance' => $this->getTierPerformanceData($startDate)
        ];

        $filename = "mlm_report_{$period}_" . now()->format('Y-m-d') . ".{$format}";
        
        return $this->generateReport($data, $format, $filename);
    }

    public function exportUserReport(Request $request, $userId)
    {
        $format = $request->get('format', 'csv');
        
        $data = [
            'investments' => $this->getUserInvestments($userId),
            'profits' => $this->getUserProfits($userId),
            'referrals' => $this->getUserReferrals($userId)
        ];

        $filename = "user_report_{$userId}_" . now()->format('Y-m-d') . ".{$format}";
        
        return $this->generateReport($data, $format, $filename);
    }

    protected function getInvestmentData($startDate)
    {
        return Investment::where('created_at', '>=', $startDate)
            ->with(['user'])
            ->select(
                'id',
                'user_id',
                'amount',
                'status',
                'investment_date',
                'created_at'
            )
            ->get()
            ->map(function ($investment) {
                return [
                    'Investment ID' => $investment->id,
                    'User' => $investment->user->name,
                    'Amount' => $investment->amount,
                    'Status' => $investment->status,
                    'Date' => $investment->investment_date,
                ];
            });
    }

    protected function getProfitData($startDate)
    {
        return ProfitShare::where('created_at', '>=', $startDate)
            ->with(['user', 'investment'])
            ->select(
                'id',
                'user_id',
                'investment_id',
                'fixed_profit_amount',
                'performance_bonus',
                'distribution_date',
                'created_at'
            )
            ->get()
            ->map(function ($profit) {
                return [
                    'Distribution ID' => $profit->id,
                    'User' => $profit->user->name,
                    'Investment ID' => $profit->investment_id,
                    'Fixed Profit' => $profit->fixed_profit_amount,
                    'Performance Bonus' => $profit->performance_bonus,
                    'Date' => $profit->distribution_date,
                ];
            });
    }

    protected function getReferralData($startDate)
    {
        return ReferralCommission::where('created_at', '>=', $startDate)
            ->with(['referrer', 'referee'])
            ->select(
                'id',
                'referrer_id',
                'referee_id',
                'level',
                'commission_amount',
                'created_at'
            )
            ->get()
            ->map(function ($commission) {
                return [
                    'Commission ID' => $commission->id,
                    'Referrer' => $commission->referrer->name,
                    'Referee' => $commission->referee->name,
                    'Level' => $commission->level,
                    'Amount' => $commission->commission_amount,
                    'Date' => $commission->created_at,
                ];
            });
    }

    protected function getUserInvestments($userId)
    {
        return Investment::where('user_id', $userId)
            ->select(
                'id',
                'amount',
                'status',
                'investment_date',
                'created_at'
            )
            ->get()
            ->map(function ($investment) {
                return [
                    'Investment ID' => $investment->id,
                    'Amount' => $investment->amount,
                    'Status' => $investment->status,
                    'Date' => $investment->investment_date,
                ];
            });
    }

    protected function getUserProfits($userId)
    {
        return ProfitShare::where('user_id', $userId)
            ->select(
                'id',
                'investment_id',
                'fixed_profit_amount',
                'performance_bonus',
                'distribution_date',
                'created_at'
            )
            ->get()
            ->map(function ($profit) {
                return [
                    'Distribution ID' => $profit->id,
                    'Investment ID' => $profit->investment_id,
                    'Fixed Profit' => $profit->fixed_profit_amount,
                    'Performance Bonus' => $profit->performance_bonus,
                    'Date' => $profit->distribution_date,
                ];
            });
    }

    protected function getUserReferrals($userId)
    {
        return ReferralCommission::where('referrer_id', $userId)
            ->with(['referee'])
            ->select(
                'id',
                'referee_id',
                'level',
                'commission_amount',
                'created_at'
            )
            ->get()
            ->map(function ($commission) {
                return [
                    'Commission ID' => $commission->id,
                    'Referee' => $commission->referee->name,
                    'Level' => $commission->level,
                    'Amount' => $commission->commission_amount,
                    'Date' => $commission->created_at,
                ];
            });
    }

    protected function generateReport($data, $format, $filename)
    {
        switch ($format) {
            case 'csv':
                return $this->generateCSV($data, $filename);
            case 'xlsx':
                return $this->generateExcel($data, $filename);
            case 'pdf':
                return $this->generatePDF($data, $filename);
            default:
                return $this->generateCSV($data, $filename);
        }
    }

    protected function getStartDate($period)
    {
        return match($period) {
            'week' => now()->subWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
    }

    private function generateCSV($data, $filename)
    {
        $output = fopen('php://temp', 'w');
        
        foreach ($data as $section => $rows) {
            fputcsv($output, [$section]);
            if (count($rows) > 0) {
                fputcsv($output, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($output, $row);
                }
            }
            fputcsv($output, []); // Empty line between sections
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    private function generateExcel($data, $filename)
    {
        // Implement Excel export using a library like PhpSpreadsheet
        // Return response with Excel file
    }

    private function generatePDF($data, $filename)
    {
        // Implement PDF export using a library like DOMPDF
        // Return response with PDF file
    }
}