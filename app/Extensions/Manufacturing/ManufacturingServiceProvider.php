<?php

namespace App\Extensions\Manufacturing;

use App\Extensions\ExtensionServiceProvider;
use App\Extensions\Manufacturing\Repositories\BillOfMaterialsRepositoryInterface;
use App\Extensions\Manufacturing\Repositories\EloquentBillOfMaterialsRepository;
use App\Extensions\Manufacturing\Repositories\WorkOrderRepositoryInterface;
use App\Extensions\Manufacturing\Repositories\EloquentWorkOrderRepository;
use App\Extensions\Manufacturing\Services\ManufacturingService;

class ManufacturingServiceProvider extends ExtensionServiceProvider
{
    public function getCode(): string { return 'manufacturing'; }

    public function getName(): string { return 'Manufacturing Extension'; }

    public function getDescription(): ?string
    {
        return 'Bill of Materials (BOM) management, work orders, material issue/receipt, by-product and scrap tracking, and production costing for factories, breweries, bakeries, and workshops.';
    }

    public function getVersion(): ?string { return '1.0.0'; }

    public function getFeatures(): array
    {
        return ['bill-of-materials', 'work-orders', 'production-costing'];
    }

    public function getDefaultSettings(): array
    {
        return ['auto_calculate_bom_cost' => true, 'require_work_order_approval' => false];
    }

    public function boot(): void
    {
        $this->loadExtensionMigrations();
        $this->loadExtensionRoutes();
    }

    public function register(): void
    {
        $this->registerBindings([
            BillOfMaterialsRepositoryInterface::class => EloquentBillOfMaterialsRepository::class,
            WorkOrderRepositoryInterface::class => EloquentWorkOrderRepository::class,
        ]);
        $this->registerServices([ManufacturingService::class]);
    }
}
