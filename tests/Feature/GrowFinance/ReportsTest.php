<?php

namespace Tests\Feature\GrowFinance;

use Inertia\Testing\AssertableInertia as Assert;

class ReportsTest extends GrowFinanceTestCase
{
    public function test_profit_loss_report_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.reports.profit-loss'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Reports/ProfitLoss')
        );
    }

    public function test_balance_sheet_report_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.reports.balance-sheet'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Reports/BalanceSheet')
        );
    }

    public function test_cash_flow_report_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.reports.cash-flow'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Reports/CashFlow')
        );
    }

    public function test_trial_balance_report_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.reports.trial-balance'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Reports/TrialBalance')
        );
    }

    public function test_general_ledger_report_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.reports.general-ledger'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Reports/GeneralLedger')
        );
    }

    public function test_can_export_report_as_csv(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.reports.export', ['type' => 'profit-loss', 'format' => 'csv']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_reports_filter_by_date_range(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.reports.profit-loss', [
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->endOfMonth()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
    }
}
