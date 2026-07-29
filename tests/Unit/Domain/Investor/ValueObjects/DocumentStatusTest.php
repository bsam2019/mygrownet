<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\ValueObjects;

use App\Domain\Investor\ValueObjects\DocumentStatus;
use PHPUnit\Framework\TestCase;

class DocumentStatusTest extends TestCase
{
    public function test_active_case(): void
    {
        $status = DocumentStatus::ACTIVE;
        $this->assertEquals('active', $status->value);
        $this->assertEquals('Active', $status->label());
        $this->assertTrue($status->isActive());
        $this->assertFalse($status->isArchived());
        $this->assertFalse($status->isDraft());
    }

    public function test_archived_case(): void
    {
        $status = DocumentStatus::ARCHIVED;
        $this->assertEquals('archived', $status->value);
        $this->assertEquals('Archived', $status->label());
        $this->assertTrue($status->isArchived());
        $this->assertFalse($status->isActive());
    }

    public function test_draft_case(): void
    {
        $status = DocumentStatus::DRAFT;
        $this->assertEquals('draft', $status->value);
        $this->assertEquals('Draft', $status->label());
        $this->assertTrue($status->isDraft());
        $this->assertFalse($status->isActive());
    }
}
