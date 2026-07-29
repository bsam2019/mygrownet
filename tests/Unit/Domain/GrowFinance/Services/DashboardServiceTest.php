<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Profile;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\CustomerRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ProfileRepositoryInterface;
use App\Domain\GrowFinance\Repositories\VendorRepositoryInterface;
use App\Domain\GrowFinance\Services\DashboardService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DashboardServiceTest extends TestCase
{
    #[Test]
    public function has_setup_completed_returns_false_when_no_profile()
    {
        $accountRepo = $this->createStub(AccountRepositoryInterface::class);
        $customerRepo = $this->createStub(CustomerRepositoryInterface::class);
        $expenseRepo = $this->createStub(ExpenseRepositoryInterface::class);
        $invoiceRepo = $this->createStub(InvoiceRepositoryInterface::class);
        $vendorRepo = $this->createStub(VendorRepositoryInterface::class);
        $profileRepo = $this->createMock(ProfileRepositoryInterface::class);

        $profileRepo->expects($this->once())->method('findByUser')->willReturn(null);

        $service = new DashboardService($accountRepo, $customerRepo, $expenseRepo, $invoiceRepo, $vendorRepo, $profileRepo);
        $this->assertFalse($service->hasSetupCompleted(5));
    }

    #[Test]
    public function has_setup_completed_returns_true_when_profile_exists()
    {
        $accountRepo = $this->createStub(AccountRepositoryInterface::class);
        $customerRepo = $this->createStub(CustomerRepositoryInterface::class);
        $expenseRepo = $this->createStub(ExpenseRepositoryInterface::class);
        $invoiceRepo = $this->createStub(InvoiceRepositoryInterface::class);
        $vendorRepo = $this->createStub(VendorRepositoryInterface::class);
        $profileRepo = $this->createMock(ProfileRepositoryInterface::class);

        $profileRepo->expects($this->once())->method('findByUser')->willReturn($this->createStub(Profile::class));

        $service = new DashboardService($accountRepo, $customerRepo, $expenseRepo, $invoiceRepo, $vendorRepo, $profileRepo);
        $this->assertTrue($service->hasSetupCompleted(5));
    }


}
