<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Contracts;

use App\Domain\Core\Contracts\ProviderContract;
use App\Domain\Core\ValueObjects\PlatformContext;

interface InventoryProvider extends ProviderContract
{
    public function getItems(PlatformContext $context): array;

    public function getStockLevel(PlatformContext $context, string $itemId, ?string $warehouseId = null): int;

    public function adjustStock(PlatformContext $context, string $itemId, int $quantity, string $reason): void;

    public function getMovements(PlatformContext $context, \DateTimeImmutable $from, \DateTimeImmutable $to): array;
}
