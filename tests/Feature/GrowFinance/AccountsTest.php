<?php

namespace Tests\Feature\GrowFinance;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use Inertia\Testing\AssertableInertia as Assert;

class AccountsTest extends GrowFinanceTestCase
{
    public function test_accounts_index_requires_authentication(): void
    {
        $response = $this->get(route('growfinance.accounts.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_accounts_index_loads_successfully(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->get(route('growfinance.accounts.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('GrowFinance/Accounts/Index')
            ->has('accounts')
        );
    }

    public function test_can_create_account(): void
    {
        $response = $this->actingAsGrowFinanceUser()
            ->post(route('growfinance.accounts.store'), [
                'name' => 'Test Account',
                'code' => '9999',
                'type' => 'asset',
                'description' => 'Test account description',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('growfinance_accounts', [
            'business_id' => $this->businessId,
            'name' => 'Test Account',
            'code' => '9999',
        ]);
    }

    public function test_can_update_account(): void
    {
        $account = GrowFinanceAccountModel::where('business_id', $this->businessId)->first();

        $response = $this->actingAsGrowFinanceUser()
            ->put(route('growfinance.accounts.update', $account), [
                'name' => 'Updated Account Name',
                'code' => $account->code,
                'type' => $account->type,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('growfinance_accounts', [
            'id' => $account->id,
            'name' => 'Updated Account Name',
        ]);
    }

    public function test_cannot_delete_system_account(): void
    {
        $account = GrowFinanceAccountModel::where('business_id', $this->businessId)
            ->where('is_system', true)
            ->first();

        $response = $this->actingAsGrowFinanceUser()
            ->delete(route('growfinance.accounts.destroy', $account));

        $response->assertStatus(403);
    }
}
