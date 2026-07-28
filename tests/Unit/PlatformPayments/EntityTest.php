<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\PlatformPayments\Entities\PaymentAttempt;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\Settlement;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    public function test_payment_transaction_create_sets_initiated_status(): void
    {
        $tx = PaymentTransaction::create(
            organizationId: 1,
            amount: 100.00,
            currency: 'ZMW',
            paymentMethod: PaymentMethod::MTNMoMo,
            provider: 'pawapay',
        );

        $this->assertNull($tx->id());
        $this->assertEquals(1, $tx->organizationId());
        $this->assertEquals(100.00, $tx->amount());
        $this->assertEquals('ZMW', $tx->currency());
        $this->assertEquals(PaymentMethod::MTNMoMo, $tx->paymentMethod());
        $this->assertEquals(TransactionStatus::Initiated, $tx->status());
        $this->assertEquals('pawapay', $tx->provider());
        $this->assertEquals(0, $tx->attemptCount());
        $this->assertNull($tx->failureReason());
    }

    public function test_payment_transaction_mark_completed(): void
    {
        $tx = PaymentTransaction::create(1, 100, 'ZMW', PaymentMethod::AirtelMoney, 'pawapay');
        $tx->markCompleted('txn_123', 'ref_456');

        $this->assertEquals(TransactionStatus::Completed, $tx->status());
        $this->assertEquals('txn_123', $tx->providerTransactionId());
    }

    public function test_payment_transaction_mark_failed_increments_attempt(): void
    {
        $tx = PaymentTransaction::create(1, 100, 'ZMW', PaymentMethod::Card, 'stripe');
        $this->assertEquals(0, $tx->attemptCount());

        $tx->markFailed('Insufficient funds');
        $this->assertEquals(TransactionStatus::Failed, $tx->status());
        $this->assertEquals('Insufficient funds', $tx->failureReason());
        $this->assertEquals(1, $tx->attemptCount());
    }

    public function test_payment_transaction_mark_settled(): void
    {
        $tx = PaymentTransaction::create(1, 100, 'ZMW', PaymentMethod::BankTransfer, 'pawapay');
        $tx->markCompleted('txn_1');
        $now = new \DateTimeImmutable();
        $tx->markSettled(95.00, 5.00, $now);

        $this->assertEquals(TransactionStatus::Settled, $tx->status());
    }

    public function test_payment_transaction_reconstitute_restores_state(): void
    {
        $now = new \DateTimeImmutable();
        $tx = PaymentTransaction::reconstitute(
            id: 42,
            organizationId: 1,
            amount: 200.00,
            currency: 'USD',
            paymentMethod: 'mtn_momo',
            status: 'completed',
            providerTransactionId: 'prov_1',
            providerReference: 'ref_1',
            provider: 'pawapay',
            fee: 5.00,
            settledAmount: 195.00,
            settledAt: $now,
            metadata: ['order_id' => 101],
            failureReason: null,
            attemptCount: 1,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(42, $tx->id());
        $this->assertEquals(TransactionStatus::Completed, $tx->status());
        $this->assertEquals(1, $tx->attemptCount());
    }

    public function test_payment_transaction_to_array(): void
    {
        $tx = PaymentTransaction::create(1, 50.00, 'ZMW', PaymentMethod::Wallet, 'wallet');
        $data = $tx->toArray();

        $this->assertEquals(1, $data['organization_id']);
        $this->assertEquals(50.00, $data['amount']);
        $this->assertEquals('ZMW', $data['currency']);
        $this->assertEquals('wallet', $data['payment_method']);
        $this->assertEquals('initiated', $data['status']);
    }

    public function test_payment_attempt_create(): void
    {
        $now = new \DateTimeImmutable();
        $attempt = PaymentAttempt::create(
            transactionId: 1,
            attemptNumber: 1,
            scheduledAt: $now,
        );

        $this->assertNull($attempt->id());
        $this->assertEquals(1, $attempt->transactionId());
        $this->assertEquals(1, $attempt->attemptNumber());
        $this->assertEquals('pending', $attempt->status());
    }

    public function test_payment_attempt_mark_success(): void
    {
        $attempt = PaymentAttempt::create(1, 1, new \DateTimeImmutable());
        $attempt->markSuccess(['transaction_id' => 'txn_ok']);

        $this->assertEquals('success', $attempt->status());
    }

    public function test_payment_attempt_mark_failed(): void
    {
        $attempt = PaymentAttempt::create(1, 1, new \DateTimeImmutable());
        $attempt->markFailed('Timeout');

        $this->assertEquals('failed', $attempt->status());
    }

    public function test_payment_attempt_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $attempt = PaymentAttempt::reconstitute(
            id: 10,
            transactionId: 1,
            attemptNumber: 2,
            status: 'failed',
            providerResponse: null,
            errorMessage: 'Declined',
            scheduledAt: $now,
            attemptedAt: $now,
            createdAt: $now,
        );

        $this->assertEquals(10, $attempt->id());
        $this->assertEquals('failed', $attempt->status());
    }

    public function test_settlement_create_matched_when_amounts_equal(): void
    {
        $settlement = Settlement::create(
            organizationId: 1,
            provider: 'pawapay',
            providerSettlementId: 'stl_001',
            expectedAmount: 1000.00,
            actualAmount: 1000.00,
            feeAmount: 50.00,
            currency: 'ZMW',
            settlementDate: new \DateTimeImmutable(),
        );

        $this->assertEquals('matched', $settlement->status());
    }

    public function test_settlement_create_discrepancy_when_amounts_differ(): void
    {
        $settlement = Settlement::create(
            organizationId: 1,
            provider: 'pawapay',
            providerSettlementId: 'stl_002',
            expectedAmount: 1000.00,
            actualAmount: 980.00,
            feeAmount: 50.00,
            currency: 'ZMW',
            settlementDate: new \DateTimeImmutable(),
        );

        $this->assertEquals('discrepancy', $settlement->status());
    }

    public function test_settlement_reconcile(): void
    {
        $settlement = Settlement::create(1, 'pawapay', 'stl_003', 1000, 1000, 50, 'ZMW', new \DateTimeImmutable());
        $settlement->reconcile();

        $this->assertEquals('reconciled', $settlement->status());
    }

    public function test_settlement_flag_discrepancy(): void
    {
        $settlement = Settlement::create(1, 'pawapay', 'stl_004', 1000, 1000, 50, 'ZMW', new \DateTimeImmutable());
        $settlement->flagDiscrepancy('Fee mismatch');

        $this->assertEquals('discrepancy', $settlement->status());
    }

    public function test_settlement_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $s = Settlement::reconstitute(
            id: 5,
            organizationId: 1,
            provider: 'pawapay',
            providerSettlementId: 'stl_005',
            expectedAmount: 500.00,
            actualAmount: 500.00,
            feeAmount: 25.00,
            currency: 'ZMW',
            status: 'matched',
            settlementDate: $now,
            reconciledAt: null,
            discrepancyNotes: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(5, $s->id());
        $this->assertEquals('matched', $s->status());
        $this->assertEquals(500.00, $s->expectedAmount());
    }

    public function test_settlement_to_array(): void
    {
        $s = Settlement::create(1, 'pawapay', 'stl_006', 2000, 2000, 100, 'ZMW', new \DateTimeImmutable());
        $data = $s->toArray();

        $this->assertEquals(2000, $data['expected_amount']);
        $this->assertEquals(100, $data['fee_amount']);
    }
}
