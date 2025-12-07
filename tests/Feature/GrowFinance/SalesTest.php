<?php

namespace Tests\Feature\GrowFinance;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceCustomerModel;
use Inertia\Testing\AssertableInertia as Assert;

class SalesTest extends GrowFinanceTestCase
{
    public function test_sales_page_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.sales.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Sales/Index')
        );
    }

    public function test_can_record_quick_sale(): void
    {
        $customer = GrowFinanceCustomerModel::create([
            'business_id' => $this->businessId,
            'name' => 'Walk-in Customer',
            'is_active' => true,
        ]);

        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.sales.store'), [
                'customer_id' => $customer->id,
                'description' => 'Quick sale',
                'amount' => 1500,
                'payment_method' => 'cash',
                'sale_date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();
    }

    public function test_sale_validation_requires_amount(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.sales.store'), [
                'description' => 'Sale without amount',
                'payment_method' => 'cash',
            ]);

        $response->assertSessionHasErrors('amount');
    }
}
