<?php

namespace Tests\Feature\GrowFinance;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceExpenseModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceVendorModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

class ExpensesTest extends GrowFinanceTestCase
{
    protected GrowFinanceVendorModel $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = GrowFinanceVendorModel::create([
            'business_id' => $this->businessId,
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'is_active' => true,
        ]);
    }

    public function test_expenses_index_loads_successfully(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.expenses.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Expenses/Index')
        );
    }

    public function test_can_create_expense(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.expenses.store'), [
                'vendor_id' => $this->vendor->id,
                'expense_date' => now()->format('Y-m-d'),
                'category' => 'Office Supplies',
                'description' => 'Test expense',
                'amount' => 500,
                'payment_method' => 'cash',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('growfinance_expenses', [
            'business_id' => $this->businessId,
            'vendor_id' => $this->vendor->id,
            'amount' => 500,
        ]);
    }

    public function test_can_update_expense(): void
    {
        $expense = GrowFinanceExpenseModel::create([
            'business_id' => $this->businessId,
            'vendor_id' => $this->vendor->id,
            'expense_date' => now(),
            'category' => 'Original',
            'description' => 'Original expense',
            'amount' => 100,
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAsGrowFinanceUser()
            ->put(route('growfinance.expenses.update', $expense), [
                'vendor_id' => $this->vendor->id,
                'expense_date' => now()->format('Y-m-d'),
                'category' => 'Updated',
                'description' => 'Updated expense',
                'amount' => 200,
                'payment_method' => 'bank',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('growfinance_expenses', [
            'id' => $expense->id,
            'category' => 'Updated',
            'amount' => 200,
        ]);
    }

    public function test_can_delete_expense(): void
    {
        $expense = GrowFinanceExpenseModel::create([
            'business_id' => $this->businessId,
            'vendor_id' => $this->vendor->id,
            'expense_date' => now(),
            'category' => 'To Delete',
            'amount' => 100,
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAsGrowFinanceUser()
            ->delete(route('growfinance.expenses.destroy', $expense));

        $response->assertRedirect();

        $this->assertDatabaseMissing('growfinance_expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_can_upload_receipt(): void
    {
        Storage::fake('local');

        $expense = GrowFinanceExpenseModel::create([
            'business_id' => $this->businessId,
            'vendor_id' => $this->vendor->id,
            'expense_date' => now(),
            'category' => 'Test',
            'amount' => 100,
            'payment_method' => 'cash',
        ]);

        $file = UploadedFile::fake()->image('receipt.jpg', 800, 600);

        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.expenses.receipt.upload', $expense), [
                'receipt' => $file,
            ]);

        $response->assertRedirect();

        $expense->refresh();
        $this->assertNotNull($expense->receipt_path);
    }
}
