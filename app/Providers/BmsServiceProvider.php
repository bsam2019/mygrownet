<?php

namespace App\Providers;

use App\Domain\BMS\Repositories\AssetRepositoryInterface;
use App\Domain\BMS\Repositories\BranchRepositoryInterface;
use App\Domain\BMS\Repositories\CmsUserRepositoryInterface;
use App\Domain\BMS\Repositories\CompanyRepositoryInterface;
use App\Domain\BMS\Repositories\ContractRepositoryInterface;
use App\Domain\BMS\Repositories\CustomerRepositoryInterface;
use App\Domain\BMS\Repositories\DepartmentRepositoryInterface;
use App\Domain\BMS\Repositories\EquipmentRepositoryInterface;
use App\Domain\BMS\Repositories\ExpenseCategoryRepositoryInterface;
use App\Domain\BMS\Repositories\ExpenseRepositoryInterface;
use App\Domain\BMS\Repositories\InventoryItemRepositoryInterface;
use App\Domain\BMS\Repositories\InvoiceItemRepositoryInterface;
use App\Domain\BMS\Repositories\InvoiceRepositoryInterface;
use App\Domain\BMS\Repositories\JobRepositoryInterface;
use App\Domain\BMS\Repositories\MaterialCategoryRepositoryInterface;
use App\Domain\BMS\Repositories\MaterialRepositoryInterface;
use App\Domain\BMS\Repositories\PaymentAllocationRepositoryInterface;
use App\Domain\BMS\Repositories\PaymentRepositoryInterface;
use App\Domain\BMS\Repositories\PayrollRunRepositoryInterface;
use App\Domain\BMS\Repositories\ProjectRepositoryInterface;
use App\Domain\BMS\Repositories\PurchaseOrderItemRepositoryInterface;
use App\Domain\BMS\Repositories\PurchaseOrderRepositoryInterface;
use App\Domain\BMS\Repositories\QuotationItemRepositoryInterface;
use App\Domain\BMS\Repositories\QuotationRepositoryInterface;
use App\Domain\BMS\Repositories\RoleRepositoryInterface;
use App\Domain\BMS\Repositories\SubcontractorRepositoryInterface;
use App\Domain\BMS\Repositories\VendorRepositoryInterface;
use App\Domain\BMS\Repositories\WorkerRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentAssetRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentBranchRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentCmsUserRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentCompanyRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentContractRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentCustomerRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentDepartmentRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentEquipmentRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentExpenseCategoryRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentExpenseRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentInventoryItemRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentInvoiceItemRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentInvoiceRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentJobRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentMaterialCategoryRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentMaterialRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentPaymentAllocationRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentPaymentRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentPayrollRunRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentProjectRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentPurchaseOrderItemRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentPurchaseOrderRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentQuotationItemRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentQuotationRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentRoleRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentSubcontractorRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentVendorRepository;
use App\Infrastructure\Persistence\Repositories\BMS\EloquentWorkerRepository;
use Illuminate\Support\ServiceProvider;

class BmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, EloquentCompanyRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(CmsUserRepositoryInterface::class, EloquentCmsUserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, EloquentBranchRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
        $this->app->bind(InvoiceItemRepositoryInterface::class, EloquentInvoiceItemRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);
        $this->app->bind(PaymentAllocationRepositoryInterface::class, EloquentPaymentAllocationRepository::class);
        $this->app->bind(QuotationRepositoryInterface::class, EloquentQuotationRepository::class);
        $this->app->bind(QuotationItemRepositoryInterface::class, EloquentQuotationItemRepository::class);
        $this->app->bind(JobRepositoryInterface::class, EloquentJobRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, EloquentExpenseRepository::class);
        $this->app->bind(ExpenseCategoryRepositoryInterface::class, EloquentExpenseCategoryRepository::class);
        $this->app->bind(WorkerRepositoryInterface::class, EloquentWorkerRepository::class);
        $this->app->bind(VendorRepositoryInterface::class, EloquentVendorRepository::class);
        $this->app->bind(InventoryItemRepositoryInterface::class, EloquentInventoryItemRepository::class);
        $this->app->bind(AssetRepositoryInterface::class, EloquentAssetRepository::class);
        $this->app->bind(EquipmentRepositoryInterface::class, EloquentEquipmentRepository::class);
        $this->app->bind(PurchaseOrderRepositoryInterface::class, EloquentPurchaseOrderRepository::class);
        $this->app->bind(PurchaseOrderItemRepositoryInterface::class, EloquentPurchaseOrderItemRepository::class);
        $this->app->bind(MaterialRepositoryInterface::class, EloquentMaterialRepository::class);
        $this->app->bind(MaterialCategoryRepositoryInterface::class, EloquentMaterialCategoryRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, EloquentDepartmentRepository::class);
        $this->app->bind(PayrollRunRepositoryInterface::class, EloquentPayrollRunRepository::class);
        $this->app->bind(SubcontractorRepositoryInterface::class, EloquentSubcontractorRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, EloquentProjectRepository::class);
        $this->app->bind(ContractRepositoryInterface::class, EloquentContractRepository::class);

        // Services
        $this->app->singleton(\App\Domain\BMS\Core\Services\AuditTrailService::class);
        $this->app->singleton(\App\Domain\BMS\Core\Services\InvoiceService::class);
        $this->app->singleton(\App\Domain\BMS\Core\Services\PaymentService::class);
        $this->app->singleton(\App\Domain\BMS\Core\Services\QuotationService::class);
        $this->app->singleton(\App\Domain\BMS\Core\Services\JobService::class);
        $this->app->singleton(\App\Domain\BMS\Core\Services\CustomerService::class);
        $this->app->singleton(\App\Domain\BMS\Core\Services\ExpenseService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/bms'));
    }
}
