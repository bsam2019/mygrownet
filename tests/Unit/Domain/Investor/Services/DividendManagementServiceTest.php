<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\Services;

use App\Domain\Investor\Entities\InvestorAccount;
use App\Domain\Investor\Entities\InvestorDividend;
use App\Domain\Investor\Entities\InvestorPaymentMethod;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorDividendRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorPaymentMethodRepositoryInterface;
use App\Domain\Investor\Services\DividendManagementService;
use App\Domain\Investor\ValueObjects\InvestorStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DividendManagementServiceTest extends TestCase
{
    private InvestorDividendRepositoryInterface $dividendRepo;
    private InvestorPaymentMethodRepositoryInterface $paymentMethodRepo;
    private InvestorAccountRepositoryInterface $accountRepo;
    private DividendManagementService $service;

    protected function setUp(): void
    {
        $this->dividendRepo = $this->createStub(InvestorDividendRepositoryInterface::class);
        $this->paymentMethodRepo = $this->createStub(InvestorPaymentMethodRepositoryInterface::class);
        $this->accountRepo = $this->createStub(InvestorAccountRepositoryInterface::class);
        $this->service = new DividendManagementService(
            $this->dividendRepo,
            $this->paymentMethodRepo,
            $this->accountRepo
        );
    }

    public function test_get_investor_dividends(): void
    {
        $this->dividendRepo = $this->createMock(InvestorDividendRepositoryInterface::class);
        $this->service = new DividendManagementService(
            $this->dividendRepo, $this->paymentMethodRepo, $this->accountRepo
        );

        $dividend = InvestorDividend::create(1, '2026-Q2', 1000, 150, 850);

        $this->dividendRepo->expects($this->once())
            ->method('findByInvestor')
            ->with(1)
            ->willReturn([$dividend]);

        $result = $this->service->getInvestorDividends(1);

        $this->assertCount(1, $result);
        $this->assertSame($dividend, $result[0]);
    }

    public function test_get_total_dividends_earned(): void
    {
        $dividendRepo = $this->createMock(InvestorDividendRepositoryInterface::class);
        $this->service = new DividendManagementService($dividendRepo, $this->paymentMethodRepo, $this->accountRepo);

        $d1 = InvestorDividend::create(1, '2026-Q1', 1000, 150, 850);
        $d2 = InvestorDividend::create(1, '2026-Q2', 500, 75, 425);

        $dividendRepo->expects($this->any())->method('findByInvestor')->with(1)->willReturn([$d1, $d2]);

        $total = $this->service->getTotalDividendsEarned(1);

        $this->assertEquals(1275.0, $total);
    }

    public function test_get_dividend_history(): void
    {
        $dividendRepo = $this->createMock(InvestorDividendRepositoryInterface::class);
        $this->service = new DividendManagementService($dividendRepo, $this->paymentMethodRepo, $this->accountRepo);

        $dividend = InvestorDividend::create(
            1, '2026-Q2', 1000, 150, 850,
            new DateTimeImmutable('2026-06-01'),
            new DateTimeImmutable('2026-07-15')
        );

        $dividendRepo->expects($this->any())->method('findByInvestor')->with(1)->willReturn([$dividend]);

        $history = $this->service->getDividendHistory(1, '2026');

        $this->assertCount(1, $history);
        $this->assertEquals('2026-Q2', $history[0]['period']);
        $this->assertEquals(1000.0, $history[0]['gross_amount']);
        $this->assertEquals(150.0, $history[0]['tax_withheld']);
        $this->assertEquals(850.0, $history[0]['net_amount']);
        $this->assertEquals('2026-06-01', $history[0]['declaration_date']);
        $this->assertEquals('2026-07-15', $history[0]['payment_date']);
        $this->assertEquals('declared', $history[0]['status']);
        $this->assertEquals('Declared', $history[0]['status_label']);
    }

    public function test_get_dividend_summary(): void
    {
        $dividendRepo = $this->createMock(InvestorDividendRepositoryInterface::class);
        $this->service = new DividendManagementService($dividendRepo, $this->paymentMethodRepo, $this->accountRepo);

        $paid = InvestorDividend::fromPersistence(
            1, 1, '2026-Q1', 2000, 300, 1700,
            new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-02-15'),
            'paid', 'bank', 'REF-001',
            new DateTimeImmutable(), new DateTimeImmutable()
        );

        $pending = InvestorDividend::fromPersistence(
            2, 1, '2026-Q2', 1000, 150, 850,
            new DateTimeImmutable('2026-06-01'), new DateTimeImmutable('2026-07-15'),
            'declared', null, null,
            new DateTimeImmutable(), new DateTimeImmutable()
        );

        $dividendRepo->expects($this->any())->method('findByInvestor')->with(1)->willReturn([$paid, $pending]);

        $summary = $this->service->getDividendSummary(1);

        $this->assertEquals(3000.0, $summary['total_gross']);
        $this->assertEquals(450.0, $summary['total_tax']);
        $this->assertEquals(2550.0, $summary['total_net']);
        $this->assertEquals(1700.0, $summary['total_paid']);
        $this->assertEquals(850.0, $summary['total_pending']);
        $this->assertEquals(2, $summary['dividend_count']);
        $this->assertNotNull($summary['last_dividend']);
        $this->assertNotNull($summary['next_dividend']);
    }

    public function test_declare_dividend(): void
    {
        $dividendRepo = $this->createMock(InvestorDividendRepositoryInterface::class);
        $this->service = new DividendManagementService($dividendRepo, $this->paymentMethodRepo, $this->accountRepo);

        $shareholder = InvestorAccount::fromPersistence(
            1, 1, 'Test', 't@t.com', 50000,
            new DateTimeImmutable(), 1, InvestorStatus::shareholder(), 10.0,
            new DateTimeImmutable(), new DateTimeImmutable()
        );

        $this->accountRepo->method('all')->willReturn([$shareholder]);
        $dividendRepo->expects($this->once())->method('save');

        $count = $this->service->declareDividend(
            '2026-Q2', 50000.00,
            new \DateTime('2026-06-01'), new \DateTime('2026-07-15')
        );

        $this->assertEquals(1, $count);
    }

    public function test_mark_dividend_as_paid(): void
    {
        $dividend = InvestorDividend::create(1, '2026-Q2', 1000, 150, 850);

        $this->dividendRepo = $this->createMock(InvestorDividendRepositoryInterface::class);
        $this->service = new DividendManagementService($this->dividendRepo, $this->paymentMethodRepo, $this->accountRepo);

        $this->dividendRepo->method('findById')->with(1)->willReturn($dividend);
        $this->dividendRepo->expects($this->once())->method('save');

        $result = $this->service->markDividendAsPaid(1, 'bank_transfer', 'REF-123');

        $this->assertTrue($result);
        $this->assertEquals('paid', $dividend->getStatus());
    }

    public function test_mark_dividend_as_paid_returns_false_when_not_found(): void
    {
        $dividendRepo = $this->createMock(InvestorDividendRepositoryInterface::class);
        $this->service = new DividendManagementService($dividendRepo, $this->paymentMethodRepo, $this->accountRepo);

        $dividendRepo->expects($this->any())->method('findById')->with(999)->willReturn(null);

        $result = $this->service->markDividendAsPaid(999, 'bank', 'REF');

        $this->assertFalse($result);
    }

    public function test_get_payment_method(): void
    {
        $paymentMethodRepo = $this->createMock(InvestorPaymentMethodRepositoryInterface::class);
        $this->service = new DividendManagementService($this->dividendRepo, $paymentMethodRepo, $this->accountRepo);

        $method = InvestorPaymentMethod::create(1, 'bank', 'Test Bank', '123456');

        $paymentMethodRepo->expects($this->once())
            ->method('findPrimaryByInvestor')
            ->with(1)
            ->willReturn($method);

        $result = $this->service->getPaymentMethod(1);

        $this->assertSame($method, $result);
    }

    public function test_calculate_tax_withholding(): void
    {
        $result = $this->service->calculateTaxWithholding(1000.00);

        $this->assertEquals(1000.00, $result['gross_amount']);
        $this->assertEquals(15.0, $result['tax_rate']);
        $this->assertEquals(150.0, $result['tax_withheld']);
        $this->assertEquals(850.0, $result['net_amount']);
    }
}
