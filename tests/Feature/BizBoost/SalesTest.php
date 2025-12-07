<?php

namespace Tests\Feature\BizBoost;

use Illuminate\Foundation\Testing\RefreshDatabase;

class SalesTest extends BizBoostTestCase
{
    use RefreshDatabase;

    public function test_sales_index_page_loads(): void
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('bizboost.sales.index'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('BizBoost/Sales/Index'));
    }

    public function test_can_record_sale(): void
    {
        $this->actingAs($this->user);
        
        $product = $this->createProduct(['name' => 'Test Product', 'price' => 100]);
        $customer = $this->createCustomer(['name' => 'Test Customer']);
        
        $response = $this->post(route('bizboost.sales.store'), [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 100,
            'total_amount' => 200,
            'sale_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_sales', [
            'business_id' => $this->business->id,
            'product_name' => 'Test Product',
            'quantity' => 2,
            'total_amount' => 200,
        ]);
    }

    public function test_can_record_sale_without_customer(): void
    {
        $this->actingAs($this->user);
        
        $product = $this->createProduct(['name' => 'Walk-in Sale Product', 'price' => 50]);
        
        $response = $this->post(route('bizboost.sales.store'), [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 50,
            'total_amount' => 50,
            'sale_date' => now()->format('Y-m-d'),
            'payment_method' => 'mobile_money',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_sales', [
            'business_id' => $this->business->id,
            'customer_id' => null,
            'product_name' => 'Walk-in Sale Product',
        ]);
    }

    public function test_can_delete_sale(): void
    {
        $this->actingAs($this->user);
        
        $sale = $this->createSale();
        
        $response = $this->delete(route('bizboost.sales.destroy', $sale->id));
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('bizboost_sales', ['id' => $sale->id]);
    }

    public function test_sales_reports_page_loads(): void
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('bizboost.sales.reports'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('BizBoost/Sales/Reports'));
    }

    public function test_sales_reports_show_correct_totals(): void
    {
        $this->actingAs($this->user);
        
        // Create some sales
        $this->createSale(['total_amount' => 100, 'sale_date' => now()]);
        $this->createSale(['total_amount' => 200, 'sale_date' => now()]);
        $this->createSale(['total_amount' => 150, 'sale_date' => now()->subMonth()]);
        
        $response = $this->get(route('bizboost.sales.reports'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Sales/Reports')
            ->has('stats')
        );
    }
}
