<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Infrastructure;

use App\Domain\Core\ValueObjects\PlatformContext;
use App\Domain\StockFlow\Contracts\InventoryProviderV2;
use App\Domain\StockFlow\Services\InventoryService;

class InventoryProviderV2Impl implements InventoryProviderV2
{
    public function __construct(
        private InventoryService $inventory,
    ) {}

    public function capability(): string
    {
        return 'inventory';
    }

    public function getItems(PlatformContext $context, array $criteria = []): array
    {
        $companyId = (int) $context->organizationId;
        return $this->inventory->getAllItems($companyId);
    }

    public function getItemDetail(PlatformContext $context, string $itemId): array
    {
        $companyId = (int) $context->organizationId;
        $item = $this->inventory->getItem($companyId, (int) $itemId);

        if (!$item) {
            return [];
        }

        $movements = $this->inventory->getMovementsByDateRange(
            $companyId,
            new \DateTimeImmutable('-30 days'),
            new \DateTimeImmutable('now')
        );

        return [
            'item' => $item,
            'stock_level' => $item['system_quantity'] ?? 0,
            'recent_movements' => array_slice($movements, 0, 20),
            'movement_count' => count($movements),
        ];
    }

    public function adjustStock(PlatformContext $context, string $itemId, int $quantity, string $reason): void
    {
        $companyId = (int) $context->organizationId;
        $this->inventory->adjustStock($companyId, (int) $itemId, $quantity, $reason);
    }

    public function reserveStock(PlatformContext $context, string $itemId, int $quantity, string $reference): string
    {
        $companyId = (int) $context->organizationId;
        $this->inventory->reserveStock($companyId, (int) $itemId, $quantity, $reference);
        return uniqid('res_', true);
    }
}
