<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Contracts;

use App\Domain\Core\Contracts\ProviderContract;
use App\Domain\Core\ValueObjects\PlatformContext;

interface InventoryProviderV2 extends ProviderContract
{
    public function getItems(PlatformContext $context, array $criteria = []): array;

    public function getItemDetail(PlatformContext $context, string $itemId): array;

    public function adjustStock(PlatformContext $context, string $itemId, int $quantity, string $reason): void;

    public function reserveStock(PlatformContext $context, string $itemId, int $quantity, string $reference): string;
}
