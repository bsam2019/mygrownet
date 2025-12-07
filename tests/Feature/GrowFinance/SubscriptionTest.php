<?php

namespace Tests\Feature\GrowFinance;

use Inertia\Testing\AssertableInertia as Assert;

class SubscriptionTest extends GrowFinanceTestCase
{
    public function test_upgrade_page_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.upgrade'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Upgrade')
            ->has('tiers')
            ->has('currentTier')
        );
    }

    public function test_checkout_page_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.checkout', ['tier' => 'basic', 'billing' => 'monthly']));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Checkout')
        );
    }

    public function test_usage_endpoint_returns_stats(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->getJson(route('growfinance.usage'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'tier',
            'transactions',
            'invoices',
            'customers',
            'vendors',
            'storage',
        ]);
    }

    public function test_free_tier_has_limits(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->getJson(route('growfinance.usage'));

        $response->assertStatus(200);
        $response->assertJsonPath('tier', 'free');
    }
}
