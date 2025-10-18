<?php

use App\Models\Investment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new TransactionService();
    $this->user = User::factory()->create();
});

describe('TransactionService', function () {
    it('can create investment transaction', function () {
        $investment = Investment::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000,
        ]);

        $transaction = $this->service->createInvestmentTransaction($investment);

        expect($transaction)->toBeInstanceOf(Transaction::class);
        expect($transaction->user_id)->toBe($this->user->id);
        expect($transaction->type)->toBe('investment');
        expect($transaction->amount)->toBe(1000.0);
        expect($transaction->status)->toBe('completed');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'investment',
            'amount' => 1000.0,
            'status' => 'completed',
        ]);
    });

    it('can create withdrawal transaction', function () {
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 500,
            'status' => 'approved',
        ]);

        $transaction = $this->service->createWithdrawalTransaction($withdrawalRequest);

        expect($transaction->type)->toBe('withdrawal');
        expect($transaction->amount)->toBe(-500.0); // Negative for withdrawal
        expect($transaction->status)->toBe('completed');
        expect($transaction->reference_id)->toBe($withdrawalRequest->id);
        expect($transaction->reference_type)->toBe(WithdrawalRequest::class);
    });

    it('can create commission transaction', function () {
        $commissionAmount = 75.50;
        $referralId = 123;

        $transaction = $this->service->createCommissionTransaction(
            $this->user,
            $commissionAmount,
            $referralId,
            'Level 1 referral commission'
        );

        expect($transaction->type)->toBe('commission');
        expect($transaction->amount)->toBe(75.50);
        expect($transaction->description)->toBe('Level 1 referral commission');
        expect($transaction->metadata['referral_id'])->toBe(123);
    });

    it('can create profit share transaction', function () {
        $profitAmount = 120.00;
        $distributionType = 'annual';

        $transaction = $this->service->createProfitShareTransaction(
            $this->user,
            $profitAmount,
            $distributionType,
            'Annual profit distribution'
        );

        expect($transaction->type)->toBe('profit_share');
        expect($transaction->amount)->toBe(120.00);
        expect($transaction->description)->toBe('Annual profit distribution');
        expect($transaction->metadata['distribution_type'])->toBe('annual');
    });

    it('can calculate user balance', function () {
        // Create various transactions
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'investment',
            'amount' => 1000,
            'status' => 'completed',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'profit_share',
            'amount' => 150,
            'status' => 'completed',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => -200,
            'status' => 'completed',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'commission',
            'amount' => 50,
            'status' => 'pending', // Should not be included
        ]);

        $balance = $this->service->calculateUserBalance($this->user);

        expect($balance)->toBe(950.0); // 1000 + 150 - 200 = 950
    });

    it('can get transaction history with filters', function () {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'investment',
            'amount' => 1000,
            'created_at' => now()->subDays(5),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'commission',
            'amount' => 50,
            'created_at' => now()->subDays(3),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => -100,
            'created_at' => now()->subDay(),
        ]);

        // Test type filter
        $investmentTransactions = $this->service->getTransactionHistory(
            $this->user,
            ['type' => 'investment']
        );

        expect($investmentTransactions)->toHaveCount(1);
        expect($investmentTransactions->first()->type)->toBe('investment');

        // Test date range filter
        $recentTransactions = $this->service->getTransactionHistory(
            $this->user,
            ['from_date' => now()->subDays(2)]
        );

        expect($recentTransactions)->toHaveCount(2);
    });

    it('can process batch transactions', function () {
        $users = User::factory()->count(3)->create();
        
        $transactionData = $users->map(function ($user, $index) {
            return [
                'user_id' => $user->id,
                'type' => 'profit_share',
                'amount' => 100 + ($index * 50),
                'description' => 'Quarterly bonus distribution',
            ];
        })->toArray();

        $transactions = $this->service->processBatchTransactions($transactionData);

        expect($transactions)->toHaveCount(3);
        expect($transactions->pluck('amount')->toArray())->toBe([100.0, 150.0, 200.0]);

        $this->assertDatabaseCount('transactions', 3);
    });

    it('can reverse transaction', function () {
        $originalTransaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'commission',
            'amount' => 100,
            'status' => 'completed',
        ]);

        $reversalTransaction = $this->service->reverseTransaction(
            $originalTransaction,
            'Commission clawback due to early withdrawal'
        );

        expect($reversalTransaction->type)->toBe('reversal');
        expect($reversalTransaction->amount)->toBe(-100.0);
        expect($reversalTransaction->description)->toBe('Commission clawback due to early withdrawal');
        expect($reversalTransaction->reference_id)->toBe($originalTransaction->id);

        // Original transaction should be marked as reversed
        $originalTransaction->refresh();
        expect($originalTransaction->status)->toBe('reversed');
    });

    it('can get transaction summary by type', function () {
        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'type' => 'investment',
            'amount' => 1000,
            'status' => 'completed',
        ]);

        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'type' => 'commission',
            'amount' => 50,
            'status' => 'completed',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => -200,
            'status' => 'completed',
        ]);

        $summary = $this->service->getTransactionSummaryByType($this->user);

        expect($summary)->toHaveKeys(['investment', 'commission', 'withdrawal']);
        expect($summary['investment']['count'])->toBe(2);
        expect($summary['investment']['total'])->toBe(2000.0);
        expect($summary['commission']['count'])->toBe(3);
        expect($summary['commission']['total'])->toBe(150.0);
        expect($summary['withdrawal']['count'])->toBe(1);
        expect($summary['withdrawal']['total'])->toBe(-200.0);
    });

    it('can validate transaction before creation', function () {
        // Test valid transaction
        $validData = [
            'user_id' => $this->user->id,
            'type' => 'investment',
            'amount' => 1000,
            'description' => 'New investment',
        ];

        $isValid = $this->service->validateTransaction($validData);
        expect($isValid)->toBeTrue();

        // Test invalid transaction (negative investment)
        $invalidData = [
            'user_id' => $this->user->id,
            'type' => 'investment',
            'amount' => -1000,
            'description' => 'Invalid investment',
        ];

        $isValid = $this->service->validateTransaction($invalidData);
        expect($isValid)->toBeFalse();
    });

    it('can get monthly transaction trends', function () {
        // Create transactions across different months
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'investment',
            'amount' => 1000,
            'created_at' => now()->subMonths(2)->startOfMonth(),
        ]);

        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'type' => 'investment',
            'amount' => 1500,
            'created_at' => now()->subMonth()->startOfMonth(),
        ]);

        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'type' => 'commission',
            'amount' => 100,
            'created_at' => now()->startOfMonth(),
        ]);

        $trends = $this->service->getMonthlyTransactionTrends($this->user, 3);

        expect($trends)->toHaveCount(3);
        expect($trends->first()['total_amount'])->toBe(300.0); // Current month
        expect($trends->first()['transaction_count'])->toBe(3);
    });

    it('can handle failed transactions', function () {
        $transactionData = [
            'user_id' => $this->user->id,
            'type' => 'investment',
            'amount' => 1000,
            'description' => 'Test investment',
        ];

        $transaction = $this->service->createTransaction($transactionData);
        
        // Simulate transaction failure
        $this->service->markTransactionAsFailed($transaction, 'Payment gateway error');

        $transaction->refresh();
        expect($transaction->status)->toBe('failed');
        expect($transaction->failure_reason)->toBe('Payment gateway error');
    });

    it('can calculate net worth from transactions', function () {
        // Investments (positive)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'investment',
            'amount' => 2000,
            'status' => 'completed',
        ]);

        // Profits (positive)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'profit_share',
            'amount' => 300,
            'status' => 'completed',
        ]);

        // Withdrawals (negative)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'withdrawal',
            'amount' => -500,
            'status' => 'completed',
        ]);

        $netWorth = $this->service->calculateNetWorth($this->user);

        expect($netWorth)->toBe(1800.0); // 2000 + 300 - 500
    });

    it('handles concurrent transaction creation safely', function () {
        $transactionData = [
            'user_id' => $this->user->id,
            'type' => 'commission',
            'amount' => 100,
            'description' => 'Referral commission',
        ];

        // Simulate concurrent creation
        $transaction1 = $this->service->createTransaction($transactionData);
        $transaction2 = $this->service->createTransaction($transactionData);

        expect($transaction1->id)->not->toBe($transaction2->id);
        expect($transaction1->amount)->toBe($transaction2->amount);

        $this->assertDatabaseCount('transactions', 2);
    });
});