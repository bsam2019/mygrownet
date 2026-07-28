<?php

namespace Tests\Feature\GrowFinance;

use Inertia\Testing\AssertableInertia as Assert;

class DashboardTest extends GrowFinanceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!\Illuminate\Support\Facades\Schema::hasTable('growfinance_profiles')) {
            \Illuminate\Support\Facades\Schema::create('growfinance_profiles', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('business_name')->nullable();
                $table->string('account_number')->nullable();
                $table->timestamps();
            });
        }

        \App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceProfileModel::create([
            'user_id' => $this->businessId,
            'business_name' => 'Test Business',
            'account_number' => 'ACC-001',
        ]);
    }

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
            ->has('financialSummary')
            ->has('recentTransactions')
        );
    }

    public function test_dashboard_shows_correct_stats(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('financialSummary')
            ->has('invoiceStats')
            ->has('overdueInvoices')
        );
    }
}
