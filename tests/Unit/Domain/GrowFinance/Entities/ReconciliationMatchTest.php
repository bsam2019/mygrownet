<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\ReconciliationMatch;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReconciliationMatchTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $match = new ReconciliationMatch(
            id: 1, reconciliationPeriodId: 10, statementLineId: 100,
            journalLineId: 200, statementAmount: 500.0, journalAmount: 500.0,
            matchType: 'exact', createdAt: null, updatedAt: null,
        );

        $this->assertSame(1, $match->id);
        $this->assertSame(10, $match->reconciliationPeriodId);
        $this->assertSame('exact', $match->matchType);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $match = ReconciliationMatch::reconstitute([
            'id' => 1, 'reconciliation_period_id' => 10,
            'statement_line_id' => 100, 'match_type' => 'system',
        ]);

        $this->assertSame(100, $match->statementLineId);
        $this->assertSame('system', $match->matchType);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $match = new ReconciliationMatch(id: 1, reconciliationPeriodId: 10, statementLineId: 100, journalLineId: null, statementAmount: null, journalAmount: null, matchType: null, createdAt: null, updatedAt: null);
        $array = $match->toArray();

        $this->assertSame(10, $array['reconciliation_period_id']);
        $this->assertSame(100, $array['statement_line_id']);
    }
}
