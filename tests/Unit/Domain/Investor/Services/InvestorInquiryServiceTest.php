<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\Services;

use App\Domain\Investor\Entities\InvestorInquiry;
use App\Domain\Investor\Repositories\InvestorInquiryRepositoryInterface;
use App\Domain\Investor\Services\InvestorInquiryService;
use App\Domain\Investor\ValueObjects\InvestmentRange;
use DomainException;
use PHPUnit\Framework\TestCase;

class InvestorInquiryServiceTest extends TestCase
{
    private InvestorInquiryRepositoryInterface $repository;
    private InvestorInquiryService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createStub(InvestorInquiryRepositoryInterface::class);
        $this->service = new InvestorInquiryService($this->repository);
    }

    public function test_create_inquiry(): void
    {
        $this->repository = $this->createMock(InvestorInquiryRepositoryInterface::class);
        $this->service = new InvestorInquiryService($this->repository);

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (InvestorInquiry $inquiry) {
                return $inquiry;
            });

        $result = $this->service->createInquiry(
            name: 'John Doe',
            email: 'john@example.com',
            phone: '+260971234567',
            investmentRangeValue: '100-250',
            message: 'Interested in investing'
        );

        $this->assertEquals('John Doe', $result->getName());
        $this->assertEquals('john@example.com', $result->getEmail());
        $this->assertTrue($result->isHighValue());
    }

    public function test_create_inquiry_low_value(): void
    {
        $this->repository = $this->createMock(InvestorInquiryRepositoryInterface::class);
        $this->service = new InvestorInquiryService($this->repository);

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn($i) => $i);

        $result = $this->service->createInquiry(
            name: 'Jane',
            email: 'jane@test.com',
            phone: '+260970000000',
            investmentRangeValue: '25-50'
        );

        $this->assertFalse($result->isHighValue());
    }

    public function test_mark_as_contacted(): void
    {
        $this->repository = $this->createMock(InvestorInquiryRepositoryInterface::class);
        $this->service = new InvestorInquiryService($this->repository);

        $inquiry = InvestorInquiry::create(
            name: 'Test',
            email: 'test@test.com',
            phone: '+260970000000',
            investmentRange: InvestmentRange::from('25-50')
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($inquiry);

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn(InvestorInquiry $i) => $i);

        $result = $this->service->markAsContacted(1);

        $this->assertFalse($result->isNew());
    }

    public function test_mark_as_contacted_throws_when_not_found(): void
    {
        $this->repository = $this->createMock(InvestorInquiryRepositoryInterface::class);
        $this->service = new InvestorInquiryService($this->repository);

        $this->repository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(DomainException::class);
        $this->service->markAsContacted(999);
    }

    public function test_schedule_meeting(): void
    {
        $repository = $this->createMock(InvestorInquiryRepositoryInterface::class);
        $this->service = new InvestorInquiryService($repository);

        $inquiry = InvestorInquiry::create(
            name: 'Test', email: 't@t.com', phone: '+260970000000',
            investmentRange: InvestmentRange::from('50-100')
        );

        $repository->expects($this->any())->method('findById')->with(1)->willReturn($inquiry);
        $repository->expects($this->any())->method('save')->willReturnCallback(fn(InvestorInquiry $i) => $i);

        $this->service->scheduleMeeting(1);
    }

    public function test_close_inquiry(): void
    {
        $repository = $this->createMock(InvestorInquiryRepositoryInterface::class);
        $this->service = new InvestorInquiryService($repository);

        $inquiry = InvestorInquiry::create(
            name: 'Test', email: 't@t.com', phone: '+260970000000',
            investmentRange: InvestmentRange::from('50-100')
        );

        $repository->expects($this->any())->method('findById')->with(1)->willReturn($inquiry);
        $repository->expects($this->any())->method('save')->willReturnCallback(fn(InvestorInquiry $i) => $i);

        $this->service->closeInquiry(1);
    }

    public function test_get_high_value_inquiries(): void
    {
        $this->repository = $this->createMock(InvestorInquiryRepositoryInterface::class);
        $this->service = new InvestorInquiryService($this->repository);

        $inquiry1 = InvestorInquiry::create(
            name: 'A', email: 'a@a.com', phone: '+260970000001',
            investmentRange: InvestmentRange::from('250+')
        );
        $inquiry2 = InvestorInquiry::create(
            name: 'B', email: 'b@b.com', phone: '+260970000002',
            investmentRange: InvestmentRange::from('100-250')
        );

        $this->repository->expects($this->once())
            ->method('findHighValueInquiries')
            ->willReturn([$inquiry1, $inquiry2]);

        $results = $this->service->getHighValueInquiries();

        $this->assertCount(2, $results);
        $this->assertTrue($results[0]->isHighValue());
    }
}
