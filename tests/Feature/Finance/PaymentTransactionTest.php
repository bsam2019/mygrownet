<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Domain\Payment\Events\PaymentVerified;
use App\Listeners\RecordPaymentTransaction;
use App\Domain\Financial\Services\TransactionIntegrityService;
use App\Domain\Transaction\Enums\TransactionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class PaymentTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected TransactionIntegrityService $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->transactionService = app(TransactionIntegrityService::class);
    }

    /** @test */
    public function it_creates_transaction_when_wallet_topup_is_verified()
    {
        // Arrange
        $event = new PaymentVerified(
            paymentId: 1,
            userId: $this->user->id,
            verifiedBy: 1,
            amount: 500.00,
            paymentType: 'wallet_topup',
            occurredAt: now()
        );

        // Act
        $listener = new RecordPaymentTransaction($this->transactionService);
        $listener->handle($event);

        // Assert
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 500.00,
            'transaction_type' => TransactionType::WALLET_TOPUP->value,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function it_creates_transaction_when_subscription_payment_is_verified()
    {
        // Arrange
        $event = new PaymentVerified(
            paymentId: 2,
            userId: $this->user->id,
            verifiedBy: 1,
            amount: 120.00,
            paymentType: 'subscription',
            occurredAt: now()
        );

        // Act
        $listener = new RecordPaymentTransaction($this->transactionService);
        $listener->handle($event);

        // Assert
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 120.00,
            'transaction_type' => TransactionType::SUBSCRIPTION_PAYMENT->value,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function it_creates_starter_kit_transaction_for_product_payment()
    {
        // Arrange - K500 product payment should be starter kit
        $event = new PaymentVerified(
            paymentId: 3,
            userId: $this->user->id,
            verifiedBy: 1,
            amount: 500.00,
            paymentType: 'product',
            occurredAt: now()
        );

        // Act
        $listener = new RecordPaymentTransaction($this->transactionService);
        $listener->handle($event);

        // Assert
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 500.00,
            'transaction_type' => TransactionType::STARTER_KIT_PURCHASE->value,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function it_creates_shop_purchase_transaction_for_non_starter_kit_product()
    {
        // Arrange - K150 product payment should be shop purchase
        $event = new PaymentVerified(
            paymentId: 4,
            userId: $this->user->id,
            verifiedBy: 1,
            amount: 150.00,
            paymentType: 'product',
            occurredAt: now()
        );

        // Act
        $listener = new RecordPaymentTransaction($this->transactionService);
        $listener->handle($event);

        // Assert
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 150.00,
            'transaction_type' => TransactionType::SHOP_PURCHASE->value,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_transactions()
    {
        // Arrange
        $event = new PaymentVerified(
            paymentId: 5,
            userId: $this->user->id,
            verifiedBy: 1,
            amount: 500.00,
            paymentType: 'wallet_topup',
            occurredAt: now()
        );

        $listener = new RecordPaymentTransaction($this->transactionService);

        // Act - Handle event twice
        $listener->handle($event);
        $listener->handle($event);

        // Assert - Only one transaction should exist
        $count = Transaction::where('user_id', $this->user->id)
            ->where('amount', 500.00)
            ->where('transaction_type', TransactionType::WALLET_TOPUP->value)
            ->count();

        $this->assertEquals(1, $count, 'Should prevent duplicate transactions');
    }

    /** @test */
    public function it_includes_payment_metadata_in_transaction()
    {
        // Arrange
        $event = new PaymentVerified(
            paymentId: 6,
            userId: $this->user->id,
            verifiedBy: 99,
            amount: 500.00,
            paymentType: 'wallet_topup',
            occurredAt: now()
        );

        // Act
        $listener = new RecordPaymentTransaction($this->transactionService);
        $listener->handle($event);

        // Assert
        $transaction = Transaction::where('user_id', $this->user->id)
            ->where('amount', 500.00)
            ->first();

        $this->assertNotNull($transaction);
        $this->assertNotNull($transaction->notes);
        
        $metadata = json_decode($transaction->notes, true);
        $this->assertEquals(6, $metadata['payment_id']);
        $this->assertEquals('wallet_topup', $metadata['payment_type']);
        $this->assertEquals(99, $metadata['verified_by']);
        $this->assertEquals('payment_verification', $metadata['source']);
    }

    /** @test */
    public function it_sets_correct_transaction_source_for_different_payment_types()
    {
        $testCases = [
            ['payment_type' => 'wallet_topup', 'expected_source' => 'wallet'],
            ['payment_type' => 'subscription', 'expected_source' => 'platform'],
            ['payment_type' => 'workshop', 'expected_source' => 'workshops'],
            ['payment_type' => 'learning_pack', 'expected_source' => 'learning'],
            ['payment_type' => 'coaching', 'expected_source' => 'coaching'],
        ];

        foreach ($testCases as $index => $testCase) {
            // Arrange
            $event = new PaymentVerified(
                paymentId: 100 + $index,
                userId: $this->user->id,
                verifiedBy: 1,
                amount: 100.00,
                paymentType: $testCase['payment_type'],
                occurredAt: now()
            );

            // Act
            $listener = new RecordPaymentTransaction($this->transactionService);
            $listener->handle($event);

            // Assert
            $transaction = Transaction::where('reference_number', 'LIKE', "payment_" . (100 + $index) . "_%")
                ->first();

            $this->assertNotNull($transaction, "Transaction not created for {$testCase['payment_type']}");
            $this->assertEquals(
                $testCase['expected_source'],
                $transaction->transaction_source,
                "Wrong source for {$testCase['payment_type']}"
            );
        }
    }

    /** @test */
    public function it_does_not_create_transaction_for_unsupported_payment_types()
    {
        // Arrange
        $event = new PaymentVerified(
            paymentId: 999,
            userId: $this->user->id,
            verifiedBy: 1,
            amount: 100.00,
            paymentType: 'unsupported_type',
            occurredAt: now()
        );

        // Act
        $listener = new RecordPaymentTransaction($this->transactionService);
        $listener->handle($event);

        // Assert - No transaction should be created
        $this->assertDatabaseMissing('transactions', [
            'user_id' => $this->user->id,
            'amount' => 100.00,
        ]);
    }

    /** @test */
    public function it_clears_wallet_cache_after_creating_transaction()
    {
        // Arrange
        \Cache::put("wallet_balance_{$this->user->id}", 1000.00, 3600);
        \Cache::put("wallet_breakdown_{$this->user->id}", ['test' => 'data'], 3600);

        $event = new PaymentVerified(
            paymentId: 7,
            userId: $this->user->id,
            verifiedBy: 1,
            amount: 500.00,
            paymentType: 'wallet_topup',
            occurredAt: now()
        );

        // Act
        $listener = new RecordPaymentTransaction($this->transactionService);
        $listener->handle($event);

        // Assert - Cache should be cleared
        $this->assertNull(\Cache::get("wallet_balance_{$this->user->id}"));
        $this->assertNull(\Cache::get("wallet_breakdown_{$this->user->id}"));
    }
}

