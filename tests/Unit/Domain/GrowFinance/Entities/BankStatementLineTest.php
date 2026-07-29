<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\BankStatementLine;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BankStatementLineTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $line = new BankStatementLine(id: 1, statementId: 10, description: 'Payment', debitAmount: 100.0);

        $this->assertSame(1, $line->id);
        $this->assertSame(10, $line->statementId);
        $this->assertSame('Payment', $line->description);
    }

    #[Test]
    public function get_amount_returns_negative_for_debit()
    {
        $line = new BankStatementLine(id: 1, statementId: 10, debitAmount: 200.0);
        $this->assertSame(-200.0, $line->getAmount());
    }

    #[Test]
    public function get_amount_returns_positive_for_credit()
    {
        $line = new BankStatementLine(id: 1, statementId: 10, creditAmount: 150.0);
        $this->assertSame(150.0, $line->getAmount());
    }

    #[Test]
    public function get_amount_returns_zero_when_both_null()
    {
        $line = new BankStatementLine(id: 1, statementId: 10);
        $this->assertSame(0.0, $line->getAmount());
    }

    #[Test]
    public function get_amount_prefers_credit_over_debit()
    {
        $line = new BankStatementLine(id: 1, statementId: 10, debitAmount: 100.0, creditAmount: 50.0);
        $this->assertSame(50.0, $line->getAmount());
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $line = BankStatementLine::reconstitute([
            'id' => 1, 'statement_id' => 10, 'description' => 'Test',
            'debit_amount' => 100.0,
        ]);

        $this->assertSame('Test', $line->description);
        $this->assertSame(100.0, $line->debitAmount);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $line = new BankStatementLine(id: 1, statementId: 10, description: 'Test', debitAmount: 100.0);
        $array = $line->toArray();

        $this->assertSame(10, $array['statement_id']);
        $this->assertSame(100.0, $array['debit_amount']);
    }
}
