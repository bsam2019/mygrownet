<?php
declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    public function __construct(
        private ReportingEngine $reportingEngine,
        private AccountRepositoryInterface $accountRepo,
        private DashboardWidgetService $widgetService,
    ) {}

    public function exportCsv(int $businessId, string $reportType, array $params = []): StreamedResponse
    {
        $data = $this->getReportData($businessId, $reportType, $params);
        $filename = $reportType . '_' . date('Ymd') . '.csv';

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            if (!empty($data)) {
                fputcsv($file, array_keys((array)$data[0]));
                foreach ($data as $row) {
                    fputcsv($file, (array)$row);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPdf(int $businessId, string $reportType, array $params = []): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getReportData($businessId, $reportType, $params);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('growfinance.exports.' . $reportType, [
            'data' => $data,
            'title' => ucwords(str_replace('_', ' ', $reportType)),
            'date' => date('Y-m-d H:i'),
        ]);
        return $pdf;
    }

    private function getReportData(int $businessId, string $reportType, array $params): array
    {
        $from = isset($params['from']) ? new \DateTimeImmutable($params['from']) : new \DateTimeImmutable('first day of this month');
        $to = isset($params['to']) ? new \DateTimeImmutable($params['to']) : new \DateTimeImmutable('now');
        $asOf = isset($params['as_of']) ? new \DateTimeImmutable($params['as_of']) : new \DateTimeImmutable('now');

        return match ($reportType) {
            'trial_balance' => $this->reportingEngine->getTrialBalance($businessId, $asOf),
            'profit_and_loss' => $this->formatPnlForExport($this->reportingEngine->getProfitAndLoss($businessId, $from, $to)),
            'balance_sheet' => $this->formatBsForExport($this->reportingEngine->getBalanceSheet($businessId, $asOf)),
            'cash_flow' => $this->reportingEngine->getCashFlow($businessId, $from, $to),
            'expense_breakdown' => $this->widgetService->getExpenseBreakdown($businessId, $from, $to),
            'ar_ap' => [$this->widgetService->getArApSummary($businessId)],
            default => throw new \InvalidArgumentException("Unknown report type: {$reportType}"),
        };
    }

    private function formatPnlForExport(array $pnl): array
    {
        $rows = [];
        $rows[] = ['Type' => '', 'Account' => 'INCOME', 'Amount' => ''];
        foreach ($pnl['income'] as $i) {
            $rows[] = ['Type' => 'Income', 'Account' => $i['account_name'] . ' (' . $i['account_code'] . ')', 'Amount' => number_format($i['amount'], 2)];
        }
        $rows[] = ['Type' => '', 'Account' => 'Total Income', 'Amount' => number_format($pnl['total_income'], 2)];
        $rows[] = ['Type' => '', 'Account' => 'EXPENSES', 'Amount' => ''];
        foreach ($pnl['expenses'] as $e) {
            $rows[] = ['Type' => 'Expense', 'Account' => $e['account_name'] . ' (' . $e['account_code'] . ')', 'Amount' => number_format($e['amount'], 2)];
        }
        $rows[] = ['Type' => '', 'Account' => 'Total Expenses', 'Amount' => number_format($pnl['total_expenses'], 2)];
        $net = $pnl['total_income'] - $pnl['total_expenses'];
        $rows[] = ['Type' => '', 'Account' => 'NET ' . ($net >= 0 ? 'PROFIT' : 'LOSS'), 'Amount' => number_format(abs($net), 2)];
        return $rows;
    }

    private function formatBsForExport(array $bs): array
    {
        $rows = [];
        $rows[] = ['Section' => '', 'Account' => 'ASSETS', 'Amount' => ''];
        foreach ($bs['assets'] as $a) {
            $rows[] = ['Section' => 'Assets', 'Account' => $a['account_name'] . ' (' . $a['account_code'] . ')', 'Amount' => number_format($a['amount'], 2)];
        }
        $rows[] = ['Section' => '', 'Account' => 'Total Assets', 'Amount' => number_format($bs['total_assets'], 2)];
        $rows[] = ['Section' => '', 'Account' => 'LIABILITIES', 'Amount' => ''];
        foreach ($bs['liabilities'] as $l) {
            $rows[] = ['Section' => 'Liabilities', 'Account' => $l['account_name'] . ' (' . $l['account_code'] . ')', 'Amount' => number_format($l['amount'], 2)];
        }
        $rows[] = ['Section' => '', 'Account' => 'Total Liabilities', 'Amount' => number_format($bs['total_liabilities'], 2)];
        $rows[] = ['Section' => '', 'Account' => 'EQUITY', 'Amount' => ''];
        foreach ($bs['equity'] as $e) {
            $rows[] = ['Section' => 'Equity', 'Account' => $e['account_name'] . ' (' . $e['account_code'] . ')', 'Amount' => number_format($e['amount'], 2)];
        }
        $rows[] = ['Section' => '', 'Account' => 'Total Equity', 'Amount' => number_format($bs['total_equity'], 2)];
        $rows[] = ['Section' => '', 'Account' => 'LIABILITIES + EQUITY', 'Amount' => number_format($bs['total_liabilities'] + $bs['total_equity'], 2)];
        return $rows;
    }
}
