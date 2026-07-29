<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\BankStatement;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BankStatementTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $stmt = new BankStatement(id: 1, businessId: 5, bankAccountId: 10, statementPeriod: 'Jan 2026');

        $this->assertSame(1, $stmt->id);
        $this->assertSame(5, $stmt->businessId);
        $this->assertSame(10, $stmt->bankAccountId);
        $this->assertSame('Jan 2026', $stmt->statementPeriod);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $stmt = BankStatement::reconstitute([
            'id' => 1, 'business_id' => 5, 'bank_account_id' => 10,
            'statement_period' => 'Jan 2026', 'status' => 'processed',
        ]);

        $this->assertSame('Jan 2026', $stmt->statementPeriod);
        $this->assertSame('processed', $stmt->status);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $stmt = new BankStatement(id: 1, businessId: 5, bankAccountId: 10);
        $array = $stmt->toArray();

        $this->assertSame(1, $array['id']);
        $this->assertSame(10, $array['bank_account_id']);
    }
}
