<?php

namespace App\Domain\Marketplace\Services;

use App\Domain\Marketplace\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const CART_KEY = 'marketplace_cart';

    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function getCart(): array
    {
        return Session::get(self::CART_KEY, []);
    }

    public function addItem(int $productId, int $quantity = 1): array
    {
        $product = $this->productRepository->findById($productId);
        if (!$product || !$product->isAvailable() || $product->stockQuantity < $quantity) {
            throw new \Exception('Product is not available.');
        }

        $cart = $this->getCart();
        $key = (string) $productId;

        if (isset($cart[$key])) {
            $newQuantity = $cart[$key]['quantity'] + $quantity;
            if ($newQuantity > $product->stockQuantity) {
                throw new \Exception('Not enough stock available.');
            }
            $cart[$key]['quantity'] = $newQuantity;
        } else {
            $firstImage = is_array($product->images) && count($product->images) > 0
                ? $product->images[0]
                : null;

            $cart[$key] = [
                'product_id' => $productId,
                'seller_id' => $product->sellerId,
                'name' => $product->name,
                'price' => $product->price->amount(),
                'image' => $firstImage,
                'quantity' => $quantity,
                'max_quantity' => $product->stockQuantity,
            ];
        }

        Session::put(self::CART_KEY, $cart);
        return $this->getCartSummary();
    }

    public function updateQuantity(int $productId, int $quantity): array
    {
        $cart = $this->getCart();
        $key = (string) $productId;

        if (!isset($cart[$key])) {
            throw new \Exception('Item not in cart.');
        }

        if ($quantity <= 0) {
            return $this->removeItem($productId);
        }

        $product = $this->productRepository->findById($productId);
        if ($product && $quantity > $product->stockQuantity) {
            throw new \Exception('Not enough stock available.');
        }

        $cart[$key]['quantity'] = $quantity;
        Session::put(self::CART_KEY, $cart);

        return $this->getCartSummary();
    }

    public function removeItem(int $productId): array
    {
        $cart = $this->getCart();
        unset($cart[(string) $productId]);
        Session::put(self::CART_KEY, $cart);

        return $this->getCartSummary();
    }

    public function clearCart(): void
    {
        Session::forget(self::CART_KEY);
    }

    public function getCartSummary(): array
    {
        $cart = $this->getCart();
        $items = [];
        $subtotal = 0;
        $itemCount = 0;
        $sellerIds = [];

        foreach ($cart as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $subtotal += $itemTotal;
            $itemCount += $item['quantity'];
            $sellerIds[$item['seller_id']] = true;

            $image = $item['image'] ?? null;
            $imageUrl = null;
            if ($image) {
                if (is_array($image)) {
                    $firstImage = reset($image);
                    $imageUrl = $firstImage ? asset('storage/' . $firstImage) : null;
                } else {
                    $imageUrl = asset('storage/' . $image);
                }
            }

            $items[] = [
                ...$item,
                'total' => $itemTotal,
                'image_url' => $imageUrl,
            ];
        }

        return [
            'items' => $items,
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
            'seller_count' => count($sellerIds),
            'is_multi_seller' => count($sellerIds) > 1,
        ];
    }

    public function getCartForCheckout(): array
    {
        $cart = $this->getCart();
        $items = [];

        foreach ($cart as $item) {
            $product = $this->productRepository->findById($item['product_id']);

            if (!$product || !$product->status->isActive()) {
                continue;
            }

            $quantity = min($item['quantity'], $product->stockQuantity);
            if ($quantity <= 0) {
                continue;
            }

            $items[] = [
                'product_id' => $product->id,
                'seller_id' => $product->sellerId,
                'quantity' => $quantity,
            ];
        }

        return $items;
    }
}
