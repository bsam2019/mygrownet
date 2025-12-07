<?php

namespace Tests\Feature\GrowFinance;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use Inertia\Testing\AssertableInertia as Assert;

class BankingTest extends GrowFinanceTestCase
{
    public function test_banking_index_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.banking.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Banking/Index')
        );
    }

    public function test_can_make_deposit(): void
    {
        $cashAccount = GrowFinanceAccountModel::where('business_id', $this->businessId)
            ->where('code', '1000')
            ->first();

        $initialBalance = $cashAccount->current_balance;

        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.banking.deposit'), [
                'account_id' => $cashAccount->id,
                'amount' => 5000,
                'description' => 'Test deposit',
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        $cashAccount->refresh();
        $this->assertEquals($initialBalance + 5000, $cashAccount->current_balance);
    }

    public function test_can_make_withdrawal(): void
    {
        $cashAccount = GrowFinanceAccountModel::where('business_id', $this->businessId)
            ->where('code', '1000')
            ->first();

        // First deposit some money
        $cashAccount->current_balance = 10000;
        $cashAccount->save();

        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.banking.withdrawal'), [
                'account_id' => $cashAccount->id,
                'amount' => 3000,
                'description' => 'Test withdrawal',
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        $cashAccount->refresh();
        $this->assertEquals(7000, $cashAccount->current_balance);
    }

    public function test_can_transfer_between_accounts(): void
    {
        $cashAccount = GrowFinanceAccountModel::where('business_id', $this->businessId)
            ->where('code', '1000')
            ->first();

        $bankAccount = GrowFinanceAccountModel::where('business_id', $this->businessId)
            ->where('code', '1010')
            ->first();

        // Set initial balances
        $cashAccount->current_balance = 10000;
        $cashAccount->save();
        $bankAccount->current_balance = 5000;
        $bankAccount->save();

        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.banking.transfer'), [
                'from_account_id' => $cashAccount->id,
                'to_account_id' => $bankAccount->id,
                'amount' => 2000,
                'description' => 'Transfer to bank',
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        $cashAccount->refresh();
        $bankAccount->refresh();

        $this->assertEquals(8000, $cashAccount->current_balance);
        $this->assertEquals(7000, $bankAccount->current_balance);
    }

    public function test_reconciliation_page_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.banking.reconcile'));

        $response->assertStatus(200);
    }
}
