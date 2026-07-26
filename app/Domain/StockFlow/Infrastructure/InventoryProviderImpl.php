<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Infrastructure;

use App\Domain\Core\ValueObjects\PlatformContext;
use App\Domain\StockFlow\Contracts\InventoryProvider;
use App\Domain\StockFlow\Services\InventoryService;

class InventoryProviderImpl implements InventoryProvider
{
    public function __construct(
        private InventoryService $inventory,
    ) {}

    public function capability(): string
    {
        return 'inventory';
    }

    public function getItems(PlatformContext $context): array
    {
        $companyId = (int) $context->organizationId;
        return $this->inventory->getAllItems($companyId);
    }

    public function getStockLevel(PlatformContext $context, string $itemId, ?string $warehouseId = null): int
    {
        $companyId = (int) $context->organizationId;
        return $this->inventory->getItemStockLevel($companyId, (int) $itemId);
    }

    public function adjustStock(PlatformContext $context, string $itemId, int $quantity, string $reason): void
    {
        $companyId = (int) $context->organizationId;
        $this->inventory->adjustStock($companyId, (int) $itemId, $quantity, $reason);
    }

    public function getMovements(PlatformContext $context, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $companyId = (int) $context->organizationId;
        return $this->inventory->getMovementsByDateRange($companyId, $from, $to);
    }
}
