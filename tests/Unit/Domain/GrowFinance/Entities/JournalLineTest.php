<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\JournalLine;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JournalLineTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $line = new JournalLine(id: 1, journalEntryId: 10, accountId: 100, debitAmount: 500.0, creditAmount: 0.0);

        $this->assertSame(1, $line->id);
        $this->assertSame(10, $line->journalEntryId);
        $this->assertSame(100, $line->accountId);
        $this->assertSame(500.0, $line->debitAmount);
        $this->assertSame(0.0, $line->creditAmount);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $line = JournalLine::reconstitute([
            'id' => 1, 'journal_entry_id' => 10, 'account_id' => 100,
            'debit_amount' => 300.0, 'credit_amount' => 0.0,
            'description' => 'Test line',
        ]);

        $this->assertSame(100, $line->accountId);
        $this->assertSame(300.0, $line->debitAmount);
        $this->assertSame('Test line', $line->description);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $line = new JournalLine(id: 1, journalEntryId: 10, accountId: 100, debitAmount: 500.0, creditAmount: 0.0);
        $array = $line->toArray();

        $this->assertSame(10, $array['journal_entry_id']);
        $this->assertSame(500.0, $array['debit_amount']);
    }
}
