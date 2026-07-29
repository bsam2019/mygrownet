<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\ValueObjects;

use App\Domain\QuickInvoice\ValueObjects\ClientInfo;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ClientInfoTest extends TestCase
{
    #[Test]
    public function create_with_all_fields(): void
    {
        $info = ClientInfo::create('John Doe', '123 Main St', '+260977123456', 'john@example.com');
        $this->assertSame('John Doe', $info->name());
        $this->assertSame('123 Main St', $info->address());
        $this->assertSame('+260977123456', $info->phone());
        $this->assertSame('john@example.com', $info->email());
    }

    #[Test]
    public function create_with_name_only(): void
    {
        $info = ClientInfo::create('Jane Doe');
        $this->assertSame('Jane Doe', $info->name());
        $this->assertNull($info->address());
        $this->assertNull($info->phone());
        $this->assertNull($info->email());
    }

    #[Test]
    public function create_trims_whitespace(): void
    {
        $info = ClientInfo::create('  Alice  ', '  Somewhere  ', '  123  ', '  a@b.com  ');
        $this->assertSame('Alice', $info->name());
        $this->assertSame('Somewhere', $info->address());
        $this->assertSame('123', $info->phone());
        $this->assertSame('a@b.com', $info->email());
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $info = ClientInfo::create('Bob', '456 Oak Ave', '555-0100', 'bob@test.com');
        $this->assertSame([
            'name' => 'Bob',
            'address' => '456 Oak Ave',
            'phone' => '555-0100',
            'email' => 'bob@test.com',
        ], $info->toArray());
    }

    #[Test]
    public function to_array_with_nulls(): void
    {
        $info = ClientInfo::create('No Contact');
        $this->assertSame([
            'name' => 'No Contact',
            'address' => null,
            'phone' => null,
            'email' => null,
        ], $info->toArray());
    }

    #[Test]
    public function null_fields_when_empty_string(): void
    {
        $info = ClientInfo::create('Test', '', '', '');
        $this->assertNull($info->address());
        $this->assertNull($info->phone());
        $this->assertNull($info->email());
    }
}
