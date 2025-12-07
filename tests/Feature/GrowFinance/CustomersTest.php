<?php

namespace Tests\Feature\GrowFinance;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceCustomerModel;
use Inertia\Testing\AssertableInertia as Assert;

class CustomersTest extends GrowFinanceTestCase
{
    public function test_customers_index_loads_successfully(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.customers.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Customers/Index')
        );
    }

    public function test_can_create_customer(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.customers.store'), [
                'name' => 'Test Customer',
                'email' => 'customer@test.com',
                'phone' => '+260 977 123456',
                'credit_limit' => 5000,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('growfinance_customers', [
            'business_id' => $this->businessId,
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
        ]);
    }

    public function test_can_update_customer(): void
    {
        $customer = GrowFinanceCustomerModel::create([
            'business_id' => $this->businessId,
            'name' => 'Original Name',
            'email' => 'original@test.com',
            'is_active' => true,
        ]);

        $response = $this->actingAsGrowFinanceUser()
            ->put(route('growfinance.customers.update', $customer), [
                'name' => 'Updated Name',
                'email' => 'updated@test.com',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('growfinance_customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_customer(): void
    {
        $customer = GrowFinanceCustomerModel::create([
            'business_id' => $this->businessId,
            'name' => 'To Delete',
            'email' => 'delete@test.com',
            'is_active' => true,
        ]);

        $response = $this->actingAsGrowFinanceUser()
            ->delete(route('growfinance.customers.destroy', $customer));

        $response->assertRedirect();

        $this->assertDatabaseMissing('growfinance_customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_customer_validation_requires_name(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.customers.store'), [
                'email' => 'test@test.com',
            ]);

        $response->assertSessionHasErrors('name');
    }
}
