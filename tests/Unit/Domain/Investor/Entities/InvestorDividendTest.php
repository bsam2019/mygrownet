<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\Entities;

use App\Domain\Investor\Entities\InvestorDividend;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class InvestorDividendTest extends TestCase
{
    public function test_create_sets_initial_state(): void
    {
        $declarationDate = new DateTimeImmutable('2026-06-01');
        $paymentDate = new DateTimeImmutable('2026-07-15');

        $dividend = InvestorDividend::create(
            investorAccountId: 1,
            dividendPeriod: '2026-Q2',
            grossAmount: 1000.00,
            taxWithheld: 150.00,
            netAmount: 850.00,
            declarationDate: $declarationDate,
            paymentDate: $paymentDate
        );

        $this->assertEquals(1, $dividend->getInvestorAccountId());
        $this->assertEquals('2026-Q2', $dividend->getDividendPeriod());
        $this->assertEquals(1000.00, $dividend->getGrossAmount());
        $this->assertEquals(150.00, $dividend->getTaxWithheld());
        $this->assertEquals(850.00, $dividend->getNetAmount());
        $this->assertEquals('declared', $dividend->getStatus());
        $this->assertNull($dividend->getPaymentMethod());
        $this->assertNull($dividend->getPaymentReference());
        $this->assertEquals(0, $dividend->getId());
    }

    public function test_create_without_payment_date(): void
    {
        $dividend = InvestorDividend::create(
            investorAccountId: 2,
            dividendPeriod: '2026-Q1',
            grossAmount: 500.00,
            taxWithheld: 75.00,
            netAmount: 425.00
        );

        $this->assertNull($dividend->getPaymentDate());
    }

    public function test_mark_as_paid(): void
    {
        $dividend = InvestorDividend::create(
            investorAccountId: 1,
            dividendPeriod: '2026-Q2',
            grossAmount: 1000.00,
            taxWithheld: 150.00,
            netAmount: 850.00
        );

        $dividend->markAsPaid('bank_transfer', 'REF-12345');

        $this->assertEquals('paid', $dividend->getStatus());
        $this->assertEquals('bank_transfer', $dividend->getPaymentMethod());
        $this->assertEquals('REF-12345', $dividend->getPaymentReference());
        $this->assertNotNull($dividend->getPaymentDate());
    }

    public function test_cancel(): void
    {
        $dividend = InvestorDividend::create(
            investorAccountId: 1,
            dividendPeriod: '2026-Q2',
            grossAmount: 1000.00,
            taxWithheld: 150.00,
            netAmount: 850.00
        );

        $dividend->cancel();
        $this->assertEquals('cancelled', $dividend->getStatus());
    }

    public function test_from_persistence_restores_state(): void
    {
        $declarationDate = new DateTimeImmutable('2026-01-01');
        $paymentDate = new DateTimeImmutable('2026-02-15');
        $createdAt = new DateTimeImmutable('2026-01-01');

        $dividend = InvestorDividend::fromPersistence(
            id: 99,
            investorAccountId: 5,
            dividendPeriod: '2025-Annual',
            grossAmount: 2000.00,
            taxWithheld: 300.00,
            netAmount: 1700.00,
            declarationDate: $declarationDate,
            paymentDate: $paymentDate,
            status: 'paid',
            paymentMethod: 'mobile_money',
            paymentReference: 'MM-999',
            createdAt: $createdAt,
            updatedAt: $paymentDate
        );

        $this->assertEquals(99, $dividend->getId());
        $this->assertEquals(5, $dividend->getInvestorAccountId());
        $this->assertEquals('paid', $dividend->getStatus());
        $this->assertEquals('mobile_money', $dividend->getPaymentMethod());
        $this->assertEquals('MM-999', $dividend->getPaymentReference());
    }
}
