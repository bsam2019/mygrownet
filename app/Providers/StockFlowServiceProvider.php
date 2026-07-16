<?php

namespace App\Providers;

use App\Domain\StockFlow\Repositories\AuditRepositoryInterface;
use App\Domain\StockFlow\Repositories\BinRepositoryInterface;
use App\Domain\StockFlow\Repositories\CashRegisterRepositoryInterface;
use App\Domain\StockFlow\Repositories\CommentRepositoryInterface;
use App\Domain\StockFlow\Repositories\CompanyRepositoryInterface;
use App\Domain\StockFlow\Repositories\CompanyRoleRepositoryInterface;
use App\Domain\StockFlow\Repositories\CompanyUserRepositoryInterface;
use App\Domain\StockFlow\Repositories\UserRepositoryInterface;
use App\Domain\StockFlow\Repositories\DepartmentRepositoryInterface;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\Repositories\MessageRepositoryInterface;
use App\Domain\StockFlow\Repositories\PhysicalCountRepositoryInterface;
use App\Domain\StockFlow\Repositories\PurchaseOrderRepositoryInterface;
use App\Domain\StockFlow\Repositories\QuotationRepositoryInterface;
use App\Domain\StockFlow\Repositories\InvoiceRepositoryInterface;
use App\Domain\StockFlow\Repositories\ReceiptRepositoryInterface;
use App\Domain\StockFlow\Repositories\SaleRepositoryInterface;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\Repositories\SupplierRepositoryInterface;
use App\Domain\StockFlow\Services\CompanyRoleService;
use App\Domain\StockFlow\Services\CompanyUserService;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentAuditRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentBinRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentCashRegisterRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentCommentRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentCompanyRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentCompanyRoleRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentCompanyUserRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentUserRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentDepartmentRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentItemRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentMessageRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentPhysicalCountRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentPurchaseOrderRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentQuotationRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentInvoiceRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentReceiptRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentSaleRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentStockMovementRepository;
use App\Infrastructure\Persistence\Repositories\StockFlow\EloquentSupplierRepository;
use Illuminate\Support\ServiceProvider;

class StockFlowServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations'));

        \Illuminate\Support\Facades\Redirect::macro('sfRoute', function ($name, $parameters = [], $status = 302, $headers = []) {
            // Convert stockflow.* to stockflow.sub.* for subdomain routes
            $isSubdomain = request()->route() && str_starts_with(request()->route()->getName() ?? '', 'stockflow.sub.');
            
            if ($isSubdomain && str_starts_with($name, 'stockflow.') && !str_starts_with($name, 'stockflow.sub.')) {
                $name = 'stockflow.sub.' . substr($name, 10);
            }
            
            return $this->route($name, $parameters, $status, $headers);
        });
    }

    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, EloquentCompanyRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, EloquentDepartmentRepository::class);
        $this->app->bind(BinRepositoryInterface::class, EloquentBinRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, EloquentItemRepository::class);
        $this->app->bind(SupplierRepositoryInterface::class, EloquentSupplierRepository::class);
        $this->app->bind(PurchaseOrderRepositoryInterface::class, EloquentPurchaseOrderRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, EloquentSaleRepository::class);
        $this->app->bind(StockMovementRepositoryInterface::class, EloquentStockMovementRepository::class);
        $this->app->bind(PhysicalCountRepositoryInterface::class, EloquentPhysicalCountRepository::class);
        $this->app->bind(AuditRepositoryInterface::class, EloquentAuditRepository::class);
        $this->app->bind(CashRegisterRepositoryInterface::class, EloquentCashRegisterRepository::class);
        $this->app->bind(QuotationRepositoryInterface::class, EloquentQuotationRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
        $this->app->bind(ReceiptRepositoryInterface::class, EloquentReceiptRepository::class);
        
        // Employee/Role repositories
        $this->app->bind(CompanyRoleRepositoryInterface::class, EloquentCompanyRoleRepository::class);
        $this->app->bind(CompanyUserRepositoryInterface::class, EloquentCompanyUserRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        // Comment & Message bindings
        $this->app->bind(CommentRepositoryInterface::class, EloquentCommentRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, EloquentMessageRepository::class);

        // Services
        $this->app->singleton(CompanyRoleService::class);
        $this->app->singleton(CompanyUserService::class);
        $this->app->singleton(\App\Domain\StockFlow\Services\StockFlowNotificationService::class);
        $this->app->singleton(\App\Domain\StockFlow\Services\CommentService::class);
        $this->app->singleton(\App\Domain\StockFlow\Services\MessagingService::class);
    }
}
