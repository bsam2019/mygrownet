<?php

namespace App\Domain\GrowMart\Services;

use App\Domain\GrowMart\Repositories\CartRepositoryInterface;
use App\Domain\GrowMart\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function getCart(int $userId): array
    {
        return $this->cartRepository->firstOrCreate($userId);
    }

    public function addItem(int $userId, int $productId, int $quantity): array
    {
        $product = $this->productRepository->findById($productId);
        if (!$product || ($product['status'] ?? '') !== 'active') {
            throw new \RuntimeException('Product not available.');
        }

        $cart = $this->getCart($userId);
        $item = $this->cartRepository->findItem($cart['id'], $productId);

        if ($item) {
            $this->cartRepository->incrementItemQuantity($item['id'], $quantity);
        } else {
            $this->cartRepository->addItem($cart['id'], [
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $this->getSummary($userId);
    }

    public function updateQuantity(int $userId, int $productId, int $quantity): array
    {
        $cart = $this->getCart($userId);
        $item = $this->cartRepository->findItem($cart['id'], $productId);

        if ($item) {
            if ($quantity <= 0) {
                $this->cartRepository->deleteItem($item['id']);
            } else {
                $this->cartRepository->updateItemQuantity($item['id'], $quantity);
            }
        }

        return $this->getSummary($userId);
    }

    public function removeItem(int $userId, int $productId): array
    {
        $cart = $this->getCart($userId);
        $this->cartRepository->deleteItemByProduct($cart['id'], $productId);

        return $this->getSummary($userId);
    }

    public function clearCart(int $userId): array
    {
        $cart = $this->getCart($userId);
        $this->cartRepository->clearItems($cart['id']);

        return $this->getSummary($userId);
    }

    public function getSummary(?int $userId): array
    {
        if ($userId === null) {
            return ['item_count' => 0, 'items' => [], 'subtotal' => 0, 'total' => 0];
        }

        $cart = $this->cartRepository->firstOrCreate($userId);
        $items = $this->cartRepository->getItems($cart['id']);

        $subtotal = 0;

        $mapped = array_map(function ($item) use (&$subtotal) {
            $product = $item['product'] ?? [];
            $price = (int) ($product['price'] ?? 0);
            $itemTotal = $price * $item['quantity'];
            $subtotal += $itemTotal;

            return [
                'id' => $item['id'],
                'product_id' => $item['product_id'],
                'name' => $product['name'] ?? 'Unknown',
                'slug' => $product['slug'] ?? '',
                'image' => $product['images'][0]['path'] ?? ($product['images'] ?? [])[0]['path'] ?? null,
                'unit' => $product['unit'] ?? '',
                'unit_price' => $price,
                'unit_price_formatted' => 'K' . number_format($price / 100, 2),
                'quantity' => $item['quantity'],
                'total' => $itemTotal,
                'total_formatted' => 'K' . number_format($itemTotal / 100, 2),
                'max_stock' => (int) ($product['total_stock'] ?? 0),
            ];
        }, $items);

        $itemCount = array_reduce($mapped, fn($carry, $i) => $carry + $i['quantity'], 0);

        return [
            'items' => $mapped,
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
            'subtotal_formatted' => 'K' . number_format($subtotal / 100, 2),
        ];
    }
}
