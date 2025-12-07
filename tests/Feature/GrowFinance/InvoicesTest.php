<?php

namespace Tests\Feature\GrowFinance;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceCustomerModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceModel;
use Inertia\Testing\AssertableInertia as Assert;

class InvoicesTest extends GrowFinanceTestCase
{
    protected GrowFinanceCustomerModel $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = GrowFinanceCustomerModel::create([
            'business_id' => $this->businessId,
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'is_active' => true,
        ]);
    }

    public function test_invoices_index_loads_successfully(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.invoices.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Invoices/Index')
        );
    }

    public function test_can_create_invoice(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'items' => [
                    [
                        'description' => 'Test Product',
                        'quantity' => 2,
                        'unit_price' => 1000,
                        'tax_rate' => 16,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('growfinance_invoices', [
            'business_id' => $this->businessId,
            'customer_id' => $this->customer->id,
        ]);
    }

    public function test_invoice_calculates_totals_correctly(): void
    {
        $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.invoices.store'), [
                'customer_id' => $this->customer->id,
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'items' => [
                    [
                        'description' => 'Product A',
                        'quantity' => 2,
                        'unit_price' => 1000,
                        'tax_rate' => 16,
                    ],
                ],
            ]);

        $invoice = GrowFinanceInvoiceModel::where('business_id', $this->businessId)->first();

        // 2 * 1000 = 2000 subtotal
        // 2000 * 0.16 = 320 tax
        // Total = 2320
        $this->assertEquals(2000, $invoice->subtotal);
        $this->assertEquals(320, $invoice->tax_amount);
        $this->assertEquals(2320, $invoice->total_amount);
    }

    public function test_can_record_payment(): void
    {
        $invoice = GrowFinanceInvoiceModel::create([
            'business_id' => $this->businessId,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-00001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'sent',
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
            'amount_paid' => 0,
        ]);

        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.invoices.payment', $invoice), [
                'amount' => 1160,
                'payment_method' => 'bank',
                'payment_date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(1160, $invoice->amount_paid);
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_can_send_invoice(): void
    {
        $invoice = GrowFinanceInvoiceModel::create([
            'business_id' => $this->businessId,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-00001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'draft',
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
            'amount_paid' => 0,
        ]);

        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.invoices.send', $invoice));

        $response->assertRedirect();

        $invoice->refresh();
        $this->assertEquals('sent', $invoice->status);
    }
}
