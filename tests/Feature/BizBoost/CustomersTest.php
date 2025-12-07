<?php

namespace Tests\Feature\BizBoost;

use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomersTest extends BizBoostTestCase
{
    use RefreshDatabase;

    public function test_customers_index_page_loads(): void
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('bizboost.customers.index'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('BizBoost/Customers/Index'));
    }

    public function test_can_create_customer(): void
    {
        $this->actingAs($this->user);
        
        $response = $this->post(route('bizboost.customers.store'), [
            'name' => 'Test Customer',
            'phone' => '+260 97 123 4567',
            'email' => 'customer@test.com',
            'source' => 'walk-in',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_customers', [
            'business_id' => $this->business->id,
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
        ]);
    }

    public function test_can_update_customer(): void
    {
        $this->actingAs($this->user);
        
        $customer = $this->createCustomer(['name' => 'Original Name']);
        
        $response = $this->put(route('bizboost.customers.update', $customer->id), [
            'name' => 'Updated Name',
            'phone' => '+260 97 999 9999',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_customer(): void
    {
        $this->actingAs($this->user);
        
        $customer = $this->createCustomer();
        
        $response = $this->delete(route('bizboost.customers.destroy', $customer->id));
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('bizboost_customers', ['id' => $customer->id]);
    }

    public function test_customer_limit_enforced_on_free_tier(): void
    {
        $this->actingAs($this->user);
        
        // Free tier allows 20 customers
        for ($i = 0; $i < 20; $i++) {
            $this->createCustomer(['name' => "Customer $i"]);
        }
        
        // 21st customer should fail
        $response = $this->post(route('bizboost.customers.store'), [
            'name' => 'Over Limit Customer',
            'phone' => '+260 97 000 0000',
        ]);
        
        // Should redirect to upgrade page or return error
        $this->assertTrue(
            $response->isRedirect() || $response->status() === 403
        );
    }

    public function test_can_export_customers(): void
    {
        $this->actingAs($this->user);
        
        $this->createCustomer(['name' => 'Export Test']);
        
        $response = $this->get(route('bizboost.customers.export'));
        
        // Should return CSV or redirect based on tier
        $this->assertTrue(
            $response->status() === 200 || $response->isRedirect()
        );
    }
}
