<?php

namespace App\Domain\GrowMart\Repositories;

interface CartRepositoryInterface
{
    public function findByUser(int $userId): ?array;

    public function createForUser(int $userId): array;

    public function firstOrCreate(int $userId): array;

    public function findItem(int $cartId, int $productId): ?array;

    public function addItem(int $cartId, array $data): array;

    public function updateItemQuantity(int $itemId, int $quantity): void;

    public function incrementItemQuantity(int $itemId, int $amount): void;

    public function deleteItem(int $itemId): void;

    public function deleteItemByProduct(int $cartId, int $productId): void;

    public function clearItems(int $cartId): void;

    public function getItems(int $cartId): array;
}
