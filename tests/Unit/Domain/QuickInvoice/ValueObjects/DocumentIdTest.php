<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\ValueObjects;

use App\Domain\QuickInvoice\ValueObjects\DocumentId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class DocumentIdTest extends TestCase
{
    #[Test]
    public function generate_returns_non_empty_uuid(): void
    {
        $id = DocumentId::generate();
        $this->assertMatchesRegularExpression('/^[a-f0-9\-]{36}$/', $id->value());
    }

    #[Test]
    public function generate_returns_unique_values(): void
    {
        $a = DocumentId::generate();
        $b = DocumentId::generate();
        $this->assertNotSame($a->value(), $b->value());
    }

    #[Test]
    public function from_string_creates_id(): void
    {
        $id = DocumentId::fromString('custom-id-123');
        $this->assertSame('custom-id-123', $id->value());
    }

    #[Test]
    public function from_string_empty_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Document ID cannot be empty');
        DocumentId::fromString('');
    }

    #[Test]
    public function equals_returns_true_for_same_value(): void
    {
        $a = DocumentId::fromString('abc');
        $b = DocumentId::fromString('abc');
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function equals_returns_false_for_different_values(): void
    {
        $a = DocumentId::fromString('abc');
        $b = DocumentId::fromString('xyz');
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function to_string_returns_value(): void
    {
        $id = DocumentId::fromString('test-id');
        $this->assertSame('test-id', (string) $id);
    }
}
