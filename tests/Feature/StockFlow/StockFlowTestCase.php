<?php

namespace Tests\Feature\StockFlow;

use App\Models\User;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\Repositories\SaleRepositoryInterface;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\Repositories\CashRegisterRepositoryInterface;
use App\Domain\StockFlow\Repositories\SupplierRepositoryInterface;
use App\Domain\StockFlow\Repositories\PurchaseOrderRepositoryInterface;
use App\Domain\StockFlow\Repositories\PhysicalCountRepositoryInterface;
use App\Domain\StockFlow\Repositories\AuditRepositoryInterface;
use App\Domain\StockFlow\Repositories\DepartmentRepositoryInterface;
use App\Domain\StockFlow\Repositories\BinRepositoryInterface;
use App\Domain\StockFlow\Repositories\LotRepositoryInterface;
use App\Domain\StockFlow\Services\InventoryService;
use App\Domain\StockFlow\Services\SalesService;
use App\Domain\StockFlow\Services\PurchasingService;
use App\Domain\StockFlow\Services\CashRegisterService;
use App\Domain\StockFlow\Services\PhysicalCountService;
use App\Domain\StockFlow\Services\StockLevelProjector;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class StockFlowTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected int $companyId;

    protected InventoryService $inventoryService;
    protected SalesService $salesService;
    protected PurchasingService $purchasingService;
    protected CashRegisterService $cashRegisterService;
    protected PhysicalCountService $physicalCountService;
    protected StockLevelProjector $stockLevelProjector;

    protected ItemRepositoryInterface $itemRepository;
    protected SaleRepositoryInterface $saleRepository;
    protected StockMovementRepositoryInterface $movementRepository;
    protected CashRegisterRepositoryInterface $cashRegisterRepository;
    protected SupplierRepositoryInterface $supplierRepository;
    protected PurchaseOrderRepositoryInterface $poRepository;
    protected PhysicalCountRepositoryInterface $countRepository;
    protected AuditRepositoryInterface $auditRepository;
    protected DepartmentRepositoryInterface $departmentRepository;
    protected BinRepositoryInterface $binRepository;
    protected LotRepositoryInterface $lotRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create();

        $saCompany = SaCompanyModel::create([
            'name' => 'Test Company',
            'email' => 'test@stockflow.test',
            'currency' => 'ZMW',
            'status' => 'active',
        ]);
        $this->companyId = $saCompany->id;

        $this->inventoryService = app(InventoryService::class);
        $this->salesService = app(SalesService::class);
        $this->purchasingService = app(PurchasingService::class);
        $this->cashRegisterService = app(CashRegisterService::class);
        $this->physicalCountService = app(PhysicalCountService::class);
        $this->stockLevelProjector = app(StockLevelProjector::class);

        $this->itemRepository = app(ItemRepositoryInterface::class);
        $this->saleRepository = app(SaleRepositoryInterface::class);
        $this->movementRepository = app(StockMovementRepositoryInterface::class);
        $this->cashRegisterRepository = app(CashRegisterRepositoryInterface::class);
        $this->supplierRepository = app(SupplierRepositoryInterface::class);
        $this->poRepository = app(PurchaseOrderRepositoryInterface::class);
        $this->countRepository = app(PhysicalCountRepositoryInterface::class);
        $this->auditRepository = app(AuditRepositoryInterface::class);
        $this->departmentRepository = app(DepartmentRepositoryInterface::class);
        $this->binRepository = app(BinRepositoryInterface::class);
        $this->lotRepository = app(LotRepositoryInterface::class);
    }
}
