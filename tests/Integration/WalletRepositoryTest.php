<?php

namespace Tests\Integration;

use App\Domain\Wallet\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\EloquentWalletRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WalletRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentWalletRepository $repository;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new EloquentWalletRepository();
        $this->user = User::factory()->create();
    }

    public function test_get_balance_returns_zero_for_new_user()
    {
        $balance = $this->repository->getBalance($this->user);
        
        $this->assertInstanceOf(Money::class, $balance);
        $this->assertTrue($balance->isZero());
    }

    public function test_get_balance_calculates_from_transactions()
    {
        // Create some transactions
        DB::table('transactions')->insert([
            [
                'user_id' => $this->user->id,
                'transaction_type' => 'deposit',
                'amount' => 500,
                'status' => 'completed',
                'reference_number' => 'TEST-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $this->user->id,
                'transaction_type' => 'withdrawal',
                'amount' => -200,
                'status' => 'completed',
                'reference_number' => 'TEST-002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $balance = $this->repository->getBalance($this->user);
        
        $this->assertEquals(300, $balance->amount());
    }

    public function test_get_balance_ignores_pending_transactions()
    {
        DB::table('transactions')->insert([
            [
                'user_id' => $this->user->id,
                'transaction_type' => 'deposit',
                'amount' => 500,
                'status' => 'completed',
                'reference_number' => 'TEST-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $this->user->id,
                'transaction_type' => 'deposit',
                'amount' => 300,
                'status' => 'pending',
                'reference_number' => 'TEST-002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $balance = $this->repository->getBalance($this->user);
        
        // Should only count completed transaction
        $this->assertEquals(500, $balance->amount());
    }

    public function test_get_breakdown_returns_credits_and_debits()
    {
        DB::table('transactions')->insert([
            [
                'user_id' => $this->user->id,
                'transaction_type' => 'deposit',
                'transaction_source' => 'wallet',
                'amount' => 1000,
                'status' => 'completed',
                'reference_number' => 'TEST-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $this->user->id,
                'transaction_type' => 'commission_earned',
                'transaction_source' => 'commissions',
                'amount' => 500,
                'status' => 'completed',
                'reference_number' => 'TEST-002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $this->user->id,
                'transaction_type' => 'withdrawal',
                'transaction_source' => 'wallet',
                'amount' => -300,
                'status' => 'completed',
                'reference_number' => 'TEST-003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $breakdown = $this->repository->getBreakdown($this->user);
        
        $this->assertArrayHasKey('balance', $breakdown);
        $this->assertArrayHasKey('credits', $breakdown);
        $this->assertArrayHasKey('debits', $breakdown);
        
        $this->assertEquals(1200, $breakdown['balance']->amount());
        $this->assertEquals(1500, $breakdown['credits']['total']->amount());
        $this->assertEquals(300, $breakdown['debits']['total']->amount());
    }

    public function test_has_sufficient_balance()
    {
        DB::table('transactions')->insert([
            'user_id' => $this->user->id,
            'transaction_type' => 'deposit',
            'amount' => 500,
            'status' => 'completed',
            'reference_number' => 'TEST-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(
            $this->repository->hasSufficientBalance($this->user, Money::fromKwacha(300))
        );
        
        $this->assertTrue(
            $this->repository->hasSufficientBalance($this->user, Money::fromKwacha(500))
        );
        
        $this->assertFalse(
            $this->repository->hasSufficientBalance($this->user, Money::fromKwacha(600))
        );
    }

    public function test_get_balance_at_returns_historical_balance()
    {
        $pastDate = now()->subDays(5);
        $recentDate = now()->subDays(2);
        
        DB::table('transactions')->insert([
            [
                'user_id' => $this->user->id,
                'transaction_type' => 'deposit',
                'amount' => 500,
                'status' => 'completed',
                'reference_number' => 'TEST-001',
                'created_at' => $pastDate,
                'updated_at' => $pastDate,
            ],
            [
                'user_id' => $this->user->id,
                'transaction_type' => 'deposit',
                'amount' => 300,
                'status' => 'completed',
                'reference_number' => 'TEST-002',
                'created_at' => $recentDate,
                'updated_at' => $recentDate,
            ],
        ]);

        // Balance at past date should only include first transaction
        $balanceAtPast = $this->repository->getBalanceAt($this->user, $pastDate);
        $this->assertEquals(500, $balanceAtPast->amount());
        
        // Balance at recent date should include both
        $balanceAtRecent = $this->repository->getBalanceAt($this->user, $recentDate);
        $this->assertEquals(800, $balanceAtRecent->amount());
    }

    public function test_clear_cache_removes_cached_data()
    {
        // Create transaction
        DB::table('transactions')->insert([
            'user_id' => $this->user->id,
            'transaction_type' => 'deposit',
            'amount' => 500,
            'status' => 'completed',
            'reference_number' => 'TEST-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get balance (should cache it)
        $balance1 = $this->repository->getBalance($this->user);
        $this->assertEquals(500, $balance1->amount());

        // Add another transaction
        DB::table('transactions')->insert([
            'user_id' => $this->user->id,
            'transaction_type' => 'deposit',
            'amount' => 300,
            'status' => 'completed',
            'reference_number' => 'TEST-002',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Without clearing cache, should still return old value
        $balance2 = $this->repository->getBalance($this->user);
        $this->assertEquals(500, $balance2->amount());

        // Clear cache
        $this->repository->clearCache($this->user);

        // Now should return updated value
        $balance3 = $this->repository->getBalance($this->user);
        $this->assertEquals(800, $balance3->amount());
    }
}
