<?php

namespace Tests\Feature\GrowFinance;

use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceAccountModel;
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
            ->where('code', '1110')
            ->first();

        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.banking.deposit'), [
                'account_id' => $cashAccount->id,
                'amount' => 5000,
                'description' => 'Test deposit',
                'deposit_date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_can_make_withdrawal(): void
    {
        $cashAccount = GrowFinanceAccountModel::where('business_id', $this->businessId)
            ->where('code', '1110')
            ->first();

        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.banking.withdrawal'), [
                'account_id' => $cashAccount->id,
                'amount' => 3000,
                'description' => 'Test withdrawal',
                'withdrawal_date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_can_transfer_between_accounts(): void
    {
        $cashAccount = GrowFinanceAccountModel::where('business_id', $this->businessId)
            ->where('code', '1110')
            ->first();

        $bankAccount = GrowFinanceAccountModel::where('business_id', $this->businessId)
            ->where('code', '1120')
            ->first();

        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.banking.transfer'), [
                'from_account_id' => $cashAccount->id,
                'to_account_id' => $bankAccount->id,
                'amount' => 2000,
                'description' => 'Transfer to bank',
                'transfer_date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_reconciliation_page_loads(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.banking.reconcile'));

        $response->assertStatus(200);
    }
}
