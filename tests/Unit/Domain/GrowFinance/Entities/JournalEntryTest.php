<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Entities\JournalLine;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JournalEntryTest extends TestCase
{
    private JournalEntry $draftEntry;

    protected function setUp(): void
    {
        $this->draftEntry = new JournalEntry(
            id: 1, businessId: 5, journalNumber: 'JE-001',
            date: new DateTimeImmutable('2026-01-15'),
            description: 'Test entry', reference: 'REF-001',
            status: JournalStatus::DRAFT,
        );
    }

    #[Test]
    public function constructor_sets_properties()
    {
        $this->assertSame(1, $this->draftEntry->id);
        $this->assertSame('JE-001', $this->draftEntry->journalNumber);
        $this->assertSame(JournalStatus::DRAFT, $this->draftEntry->status);
    }

    #[Test]
    public function draft_is_balanced_when_no_lines()
    {
        $this->assertTrue($this->draftEntry->isBalanced());
    }

    #[Test]
    public function entry_is_balanced_with_equal_lines()
    {
        $line1 = new JournalLine(id: 1, journalEntryId: 1, accountId: 100, debitAmount: 500.0, creditAmount: 0.0);
        $line2 = new JournalLine(id: 2, journalEntryId: 1, accountId: 200, debitAmount: 0.0, creditAmount: 500.0);

        $this->draftEntry->setLines([$line1, $line2]);
        $this->assertTrue($this->draftEntry->isBalanced());
    }

    #[Test]
    public function entry_is_not_balanced_with_unequal_lines()
    {
        $line1 = new JournalLine(id: 1, journalEntryId: 1, accountId: 100, debitAmount: 500.0, creditAmount: 0.0);
        $line2 = new JournalLine(id: 2, journalEntryId: 1, accountId: 200, debitAmount: 0.0, creditAmount: 400.0);

        $this->draftEntry->setLines([$line1, $line2]);
        $this->assertFalse($this->draftEntry->isBalanced());
    }

    #[Test]
    public function posting_draft_changes_status()
    {
        $line1 = new JournalLine(id: 1, journalEntryId: 1, accountId: 100, debitAmount: 500.0, creditAmount: 0.0);
        $line2 = new JournalLine(id: 2, journalEntryId: 1, accountId: 200, debitAmount: 0.0, creditAmount: 500.0);
        $this->draftEntry->setLines([$line1, $line2]);

        $now = new DateTimeImmutable('2026-01-15 10:00:00');
        $posted = $this->draftEntry->post($now);

        $this->assertSame(JournalStatus::POSTED, $posted->status);
        $this->assertSame($now, $posted->postedAt);
        $this->assertNotSame($this->draftEntry, $posted);
    }

    #[Test]
    public function cannot_post_unbalanced_entry()
    {
        $line1 = new JournalLine(id: 1, journalEntryId: 1, accountId: 100, debitAmount: 500.0, creditAmount: 0.0);
        $this->draftEntry->setLines([$line1]);

        $this->expectException(\DomainException::class);
        $this->draftEntry->post(new DateTimeImmutable());
    }

    #[Test]
    public function cannot_post_non_draft_entry()
    {
        $now = new DateTimeImmutable();
        $posted = new JournalEntry(id: 2, businessId: 5, journalNumber: 'JE-002', date: $now, description: 'Posted', reference: null, status: JournalStatus::POSTED);

        $this->expectException(\DomainException::class);
        $posted->post($now);
    }

    #[Test]
    public function can_reverse_posted_entry()
    {
        $now = new DateTimeImmutable();
        $reversed = $this->draftEntry->post($now)->reverse('Correction', $now);

        $this->assertSame('Correction', $reversed->reversalReason);
    }

    #[Test]
    public function cannot_reverse_draft_entry()
    {
        $this->expectException(\DomainException::class);
        $this->draftEntry->reverse('Reason', new DateTimeImmutable());
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $entry = JournalEntry::reconstitute([
            'id' => 1, 'business_id' => 5, 'journal_number' => 'JE-001',
            'date' => '2026-01-15', 'description' => 'Test', 'status' => 'posted',
        ]);

        $this->assertSame(JournalStatus::POSTED, $entry->status);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $array = $this->draftEntry->toArray();

        $this->assertSame('JE-001', $array['journal_number']);
        $this->assertSame('draft', $array['status']);
    }
}
