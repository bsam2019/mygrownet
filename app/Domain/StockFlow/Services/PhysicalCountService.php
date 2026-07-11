<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\PhysicalCount;
use App\Domain\StockFlow\Entities\Audit;
use App\Domain\StockFlow\Entities\AuditItem;
use App\Domain\StockFlow\Entities\StockMovement;
use App\Domain\StockFlow\Events\StockCountFinalized;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\PhysicalCountRepositoryInterface;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\Repositories\AuditRepositoryInterface;
use App\Domain\StockFlow\Repositories\DepartmentRepositoryInterface;
use App\Domain\StockFlow\Repositories\BinRepositoryInterface;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\PhysicalCountId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\MovementType;
use App\Domain\StockFlow\ValueObjects\AuditStatus;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Throwable;

class PhysicalCountService
{
    public function __construct(
        private PhysicalCountRepositoryInterface $countRepository,
        private ItemRepositoryInterface $itemRepository,
        private AuditRepositoryInterface $auditRepository,
        private DepartmentRepositoryInterface $departmentRepository,
        private BinRepositoryInterface $binRepository,
        private StockMovementRepositoryInterface $movementRepository,
        private StockLevelProjector $stockLevelProjector,
    ) {}

    public function createCount(int $companyId, array $data, int $userId): PhysicalCount
    {
        try {
            return DB::transaction(function () use ($companyId, $data, $userId) {
                $count = PhysicalCount::create(
                    companyId: CompanyId::fromInt($companyId),
                    title: $data['title'],
                    countDate: new DateTimeImmutable($data['count_date']),
                    countedBy: $userId,
                    notes: $data['notes'] ?? null,
                );

                $savedCount = $this->countRepository->save($count);

                // Snapshot from stock levels projection (the ledger-derived truth)
                $levels = $this->stockLevelProjector->getLevelsForCompany($companyId);
                $items = $this->itemRepository->findByCompanyId(CompanyId::fromInt($companyId));
                $inserts = [];
                $now = now();

                foreach ($items as $item) {
                    $levelQty = (float) ($levels[$item->id()]['qty_on_hand'] ?? $item->getSystemQuantity());
                    $levelValue = (float) ($levels[$item->id()]['value_on_hand'] ?? ($levelQty * $item->getUnitPrice()->toFloat()));

                    $inserts[] = [
                        'sa_physical_count_id' => $savedCount->id(),
                        'sa_item_id' => $item->id(),
                        'sa_bin_id' => $item->getBinIdValue(),
                        'system_quantity' => $levelQty,
                        'physical_quantity' => 0,
                        'variance' => -$levelQty,
                        'unit_price' => $item->getUnitPrice()->toFloat(),
                        'variance_value' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($inserts)) {
                    $this->countRepository->saveCountItems($savedCount->id(), $inserts);
                }

                return $savedCount;
            });
        } catch (Throwable $e) {
            throw new OperationFailedException('create physical count', $e->getMessage());
        }
    }

    public function updateCountItems(int $physicalCountId, array $items): void
    {
        $countItems = $this->countRepository->getCountItems(PhysicalCountId::fromInt($physicalCountId));

        foreach ($items as $data) {
            foreach ($countItems as $ci) {
                if ($ci->id() === (int) $data['id']) {
                    $ci->recordPhysical((float) $data['physical_quantity']);
                    $this->countRepository->updateCountItem(
                        $ci->id(),
                        $ci->getPhysicalQuantity(),
                        $ci->getVariance(),
                        $ci->getVarianceValue()->toFloat(),
                    );
                    break;
                }
            }
        }
    }

    public function completeCount(int $physicalCountId, int $userId): void
    {
        DB::transaction(function () use ($physicalCountId, $userId) {
            $count = $this->countRepository->findById(PhysicalCountId::fromInt($physicalCountId));
            if (!$count) {
                throw new OperationFailedException('complete count', 'Physical count not found');
            }

            $count->complete($userId);
            $this->countRepository->save($count);

            $companyId = $count->getCompanyId()->toInt();

            // Record StockMovement corrections for each counted item and rebuild projection
            $countItems = $this->countRepository->getCountItemData(PhysicalCountId::fromInt($physicalCountId));
            $totalSystemValue = 0;
            $totalPhysicalValue = 0;

            foreach ($countItems as $ci) {
                $systemQty = (float) $ci['system_quantity'];
                $physicalQty = (float) $ci['physical_quantity'];
                $unitPrice = (float) $ci['unit_price'];
                $diff = $physicalQty - $systemQty;

                $totalSystemValue += $systemQty * $unitPrice;
                $totalPhysicalValue += $physicalQty * $unitPrice;

                if (abs($diff) > 0.001) {
                    $this->movementRepository->save(
                        StockMovement::create(
                            companyId: CompanyId::fromInt($companyId),
                            itemId: ItemId::fromInt($ci['sa_item_id']),
                            binId: $ci['sa_bin_id'] ? BinId::fromInt($ci['sa_bin_id']) : null,
                            type: MovementType::physicalCount(),
                            quantity: $diff,
                            unitPrice: Money::fromFloat($unitPrice),
                            quantityBefore: $systemQty,
                            quantityAfter: $physicalQty,
                            reason: "Physical count adjustment (count #{$physicalCountId})",
                            createdBy: $userId,
                        )
                    );

                    $this->stockLevelProjector->rebuildForItem($companyId, $ci['sa_item_id']);
                }
            }

            // Dispatch domain event
            Event::dispatch(new StockCountFinalized(
                companyId: $companyId,
                physicalCountId: $physicalCountId,
                finalizedBy: $userId,
                totals: [
                    'total_system_value' => $totalSystemValue,
                    'total_physical_value' => $totalPhysicalValue,
                    'total_variance' => $totalSystemValue - $totalPhysicalValue,
                ],
            ));
        });
    }

    /**
     * Derive sales during period from the ledger between last finalized count and this count.
     */
    private function deriveSalesBetweenCounts(int $companyId, string $currentCountDate): float
    {
        // Find the previous finalized count's date
        $previousCount = DB::table('sa_physical_counts')
            ->where('sa_company_id', $companyId)
            ->where('status', 'completed')
            ->whereDate('count_date', '<', $currentCountDate)
            ->orderBy('count_date', 'desc')
            ->first();

        $fromDate = $previousCount ? $previousCount->count_date : '2020-01-01';

        // Sum sale_dispense movements between the two count dates
        return (float) DB::table('sa_stock_movements')
            ->where('sa_company_id', $companyId)
            ->where('type', 'sale_out')
            ->whereDate('created_at', '>', $fromDate)
            ->whereDate('created_at', '<=', $currentCountDate)
            ->sum(DB::raw('ABS(quantity) * unit_price'));
    }

    public function generateAudit(int $physicalCountId): Audit
    {
        return DB::transaction(function () use ($physicalCountId) {
            $count = $this->countRepository->findById(PhysicalCountId::fromInt($physicalCountId));
            if (!$count) {
                throw new OperationFailedException('generate audit', 'Physical count not found');
            }

            $companyId = $count->getCompanyId();
            $countItems = $this->countRepository->getCountItemData(PhysicalCountId::fromInt($physicalCountId));

            $totalSystemValue = 0.0;
            $totalPhysicalValue = 0.0;

            foreach ($countItems as $ci) {
                $totalSystemValue += (float) $ci['system_quantity'] * (float) $ci['unit_price'];
                $totalPhysicalValue += (float) $ci['physical_quantity'] * (float) $ci['unit_price'];
            }

            // Derive recorded sales from the ledger instead of user input
            $recordedSales = $this->deriveSalesBetweenCounts(
                $companyId->toInt(),
                $count->getCountDate()->format('Y-m-d')
            );

            $audit = Audit::create(
                companyId: $companyId,
                title: 'Reconciliation Report — ' . $count->getTitle(),
                auditDate: $count->getCountDate(),
                totalSystemValue: Money::fromFloat($totalSystemValue),
                totalPhysicalValue: Money::fromFloat($totalPhysicalValue),
                preparedFor: 'Business Owner',
                preparedBy: 'StockFlow System',
                reportReference: $this->auditRepository->nextReference(),
            );

            // Pre-set recorded sales from ledger
            if ($recordedSales > 0) {
                $audit->finalize(
                    totalRecordedSales: Money::fromFloat($recordedSales),
                );
            }

            $savedAudit = $this->auditRepository->save($audit);

            // Create audit items
            $auditItems = [];
            foreach ($countItems as $ci) {
                $item = $this->itemRepository->findById(ItemId::fromInt($ci['sa_item_id']));
                $auditItems[] = [
                    'sa_audit_id' => $savedAudit->id(),
                    'sa_item_id' => $ci['sa_item_id'],
                    'sa_bin_id' => $ci['sa_bin_id'],
                    'item_name' => $item?->getName() ?? 'Unknown',
                    'unit_price' => $ci['unit_price'],
                    'system_qty' => $ci['system_quantity'],
                    'physical_qty' => $ci['physical_quantity'],
                    'system_value' => (float) $ci['system_quantity'] * (float) $ci['unit_price'],
                    'physical_value' => (float) $ci['physical_quantity'] * (float) $ci['unit_price'],
                    'gap_qty' => $ci['variance'],
                    'gap_value' => $ci['variance_value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $this->auditRepository->createAuditItems($savedAudit->id(), $auditItems);

            // Department reconciliations
            $departmentItems = [];
            foreach ($countItems as $ci) {
                $item = $this->itemRepository->findById(ItemId::fromInt($ci['sa_item_id']));
                $deptId = $item?->getDepartmentIdValue();
                if ($deptId && !isset($departmentItems[$deptId])) {
                    $departmentItems[$deptId] = ['system' => 0, 'physical' => 0];
                }
                if ($deptId) {
                    $departmentItems[$deptId]['system'] += (float) $ci['system_quantity'] * (float) $ci['unit_price'];
                    $departmentItems[$deptId]['physical'] += (float) $ci['physical_quantity'] * (float) $ci['unit_price'];
                }
            }

            foreach ($departmentItems as $deptId => $vals) {
                $deptSys = $vals['system'];
                $deptPhys = $vals['physical'];
                $deptVar = $deptSys - $deptPhys;
                $this->auditRepository->createReconciliation($savedAudit->id(), [
                    'sa_department_id' => $deptId,
                    'system_value' => $deptSys,
                    'physical_value' => $deptPhys,
                    'variance' => $deptVar,
                    'variance_percent' => $deptSys > 0 ? round(($deptVar / $deptSys) * 100, 2) : 0,
                ]);
            }

            return $savedAudit;
        });
    }

    public function getCountById(int $id, int $companyId): ?PhysicalCount
    {
        $count = $this->countRepository->findById(PhysicalCountId::fromInt($id));
        if ($count && $count->getCompanyId()->toInt() !== $companyId) {
            return null;
        }
        return $count;
    }

    public function getCountsForCompany(int $companyId): array
    {
        return $this->countRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }
}
