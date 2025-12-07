<?php

namespace Tests\Feature\GrowFinance;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class GrowFinanceTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected int $businessId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@growfinance.test',
            'email_verified_at' => now(),
        ]);

        $this->businessId = $this->user->id;

        // Initialize chart of accounts for the user
        $accountingService = app(AccountingService::class);
        $accountingService->initializeChartOfAccounts($this->businessId);
    }

    protected function actingAsGrowFinanceUser(): static
    {
        return $this->actingAs($this->user);
    }
}
