<?php

namespace Tests\Feature\GrowFinance;

use Inertia\Testing\AssertableInertia as Assert;

class DashboardTest extends GrowFinanceTestCase
{
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('growfinance.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Dashboard')
            ->has('stats')
            ->has('recentTransactions')
        );
    }

    public function test_dashboard_shows_correct_stats(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('stats.totalIncome')
            ->has('stats.totalExpenses')
            ->has('stats.netProfit')
            ->has('stats.accountsReceivable')
        );
    }
}
