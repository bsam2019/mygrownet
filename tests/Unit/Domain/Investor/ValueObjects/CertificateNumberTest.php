<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\ValueObjects;

use App\Domain\Investor\ValueObjects\CertificateNumber;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CertificateNumberTest extends TestCase
{
    public function test_from_string_creates_valid(): void
    {
        $cn = CertificateNumber::fromString('VBIF-000001');
        $this->assertEquals('VBIF-000001', $cn->value());
    }

    public function test_throws_exception_for_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Certificate number cannot be empty');
        CertificateNumber::fromString('');
    }

    public function test_throws_exception_for_invalid_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CertificateNumber::fromString('hello_world');
    }

    public function test_throws_exception_for_lowercase(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CertificateNumber::fromString('vbif-000001');
    }

    public function test_generate_creates_formatted_number(): void
    {
        $cn = CertificateNumber::generate('VBIF', 1);
        $this->assertEquals('VBIF-000001', $cn->value());

        $cn2 = CertificateNumber::generate('VBIF', 123);
        $this->assertEquals('VBIF-000123', $cn2->value());
    }

    public function test_equality(): void
    {
        $a = CertificateNumber::fromString('CERT-000001');
        $b = CertificateNumber::fromString('CERT-000001');
        $c = CertificateNumber::fromString('CERT-000002');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function test_to_string(): void
    {
        $cn = CertificateNumber::fromString('VBIF-999999');
        $this->assertEquals('VBIF-999999', (string) $cn);
    }
}
