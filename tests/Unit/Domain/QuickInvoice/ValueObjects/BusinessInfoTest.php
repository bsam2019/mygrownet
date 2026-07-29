<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\ValueObjects;

use App\Domain\QuickInvoice\ValueObjects\BusinessInfo;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class BusinessInfoTest extends TestCase
{
    #[Test]
    public function create_with_all_fields(): void
    {
        $info = BusinessInfo::create(
            'My Corp',
            '123 Business Rd',
            '+260977111222',
            'info@mycorp.com',
            'logo.png',
            'TAX-001',
            'https://mycorp.com'
        );
        $this->assertSame('My Corp', $info->name());
        $this->assertSame('123 Business Rd', $info->address());
        $this->assertSame('+260977111222', $info->phone());
        $this->assertSame('info@mycorp.com', $info->email());
        $this->assertSame('logo.png', $info->logo());
        $this->assertSame('TAX-001', $info->taxNumber());
        $this->assertSame('https://mycorp.com', $info->website());
    }

    #[Test]
    public function create_with_name_only(): void
    {
        $info = BusinessInfo::create('Solo');
        $this->assertSame('Solo', $info->name());
        $this->assertNull($info->address());
        $this->assertNull($info->phone());
        $this->assertNull($info->email());
        $this->assertNull($info->logo());
        $this->assertNull($info->taxNumber());
        $this->assertNull($info->website());
    }

    #[Test]
    public function create_trims_string_fields(): void
    {
        $info = BusinessInfo::create('  Acme  ', '  Somewhere  ');
        $this->assertSame('Acme', $info->name());
        $this->assertSame('Somewhere', $info->address());
    }

    #[Test]
    public function create_tax_number_trimmed(): void
    {
        $info = BusinessInfo::create('Acme', taxNumber: '  VAT-123  ');
        $this->assertSame('VAT-123', $info->taxNumber());
    }

    #[Test]
    public function with_logo_returns_new_instance(): void
    {
        $original = BusinessInfo::create('Original');
        $updated = $original->withLogo('new-logo.png');
        $this->assertNull($original->logo());
        $this->assertSame('new-logo.png', $updated->logo());
    }

    #[Test]
    public function with_logo_preserves_other_fields(): void
    {
        $original = BusinessInfo::create('Biz', 'Addr', 'Phone', 'Email', null, 'TAX');
        $updated = $original->withLogo('logo.svg');
        $this->assertSame('Biz', $updated->name());
        $this->assertSame('Addr', $updated->address());
        $this->assertSame('TAX', $updated->taxNumber());
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $info = BusinessInfo::create(
            'Full Biz', 'Addr', 'Phone', 'Email', 'logo.png', 'TAX-999', 'https://example.com'
        );
        $this->assertSame([
            'name' => 'Full Biz',
            'address' => 'Addr',
            'phone' => 'Phone',
            'email' => 'Email',
            'logo' => 'logo.png',
            'tax_number' => 'TAX-999',
            'website' => 'https://example.com',
        ], $info->toArray());
    }
}
