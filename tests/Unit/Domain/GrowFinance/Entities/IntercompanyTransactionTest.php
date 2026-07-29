<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\IntercompanyTransaction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class IntercompanyTransactionTest extends TestCase
{
    private function makeTransaction(): IntercompanyTransaction
    {
        return new IntercompanyTransaction(
            id: null, fromOrgId: 1, toOrgId: 2, transactionType: 'loan',
            reference: 'REF-001', description: null, amount: 5000.0,
            currency: 'ZMW', exchangeRate: 1.0, mapping: [],
            status: 'pending', matchedTransactionId: null,
            transactionDate: '2026-01-15',
        );
    }

    #[Test]
    public function create_returns_new_instance()
    {
        $txn = $this->makeTransaction();

        $this->assertNull($txn->id);
        $this->assertSame(1, $txn->fromOrgId);
        $this->assertSame(2, $txn->toOrgId);
        $this->assertSame('pending', $txn->status);
    }

    #[Test]
    public function match_updates_status()
    {
        $txn = $this->makeTransaction();
        $matched = $txn->match(100);

        $this->assertSame('matched', $matched->status);
        $this->assertSame(100, $matched->matchedTransactionId);
    }

    #[Test]
    public function eliminate_updates_status()
    {
        $txn = $this->makeTransaction();
        $eliminated = $txn->eliminate();

        $this->assertSame('eliminated', $eliminated->status);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $txn = IntercompanyTransaction::reconstitute([
            'id' => 1, 'from_org_id' => 1, 'to_org_id' => 2,
            'transaction_type' => 'loan', 'amount' => 5000.0,
            'transaction_date' => '2026-01-15', 'status' => 'matched',
        ]);

        $this->assertSame('loan', $txn->transactionType);
        $this->assertSame('matched', $txn->status);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $txn = $this->makeTransaction();
        $array = $txn->toArray();

        $this->assertSame(1, $array['from_org_id']);
        $this->assertSame(5000.0, $array['amount']);
    }
}
