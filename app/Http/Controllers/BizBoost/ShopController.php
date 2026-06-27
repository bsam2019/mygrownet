<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\BizBoost\Services\OrderNotificationService;
use App\Domain\BizBoost\Services\WhatsAppCatalogService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostOrderModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostOrderItemModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function shopPage(Request $request, string $slug)
    {
        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->with('profile')
            ->firstOrFail();

        $categories = $business->products()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        $query = $business->products()
            ->where('is_active', true)
            ->with('images');

        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $products = $query->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        $cart = session()->get('cart_' . $slug, []);

        return Inertia::render('BizBoost/Public/Shop', [
            'business' => $business->load('profile'),
            'products' => $products,
            'categories' => $categories,
            'cart' => $cart,
            'filters' => $request->only(['category', 'search']),
        ]);
    }

    public function productDetail(string $slug, int $productId)
    {
        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $product = $business->products()
            ->where('is_active', true)
            ->with('images')
            ->findOrFail($productId);

        $related = $business->products()
            ->where('is_active', true)
            ->where('id', '!=', $productId)
            ->where(function ($q) use ($product) {
                if ($product->category) {
                    $q->where('category', $product->category);
                }
            })
            ->with('images')
            ->inRandomOrder()
            ->take(4)
            ->get();

        $cart = session()->get('cart_' . $slug, []);

        return Inertia::render('BizBoost/Public/ProductDetail', [
            'business' => $business->load('profile'),
            'product' => $product,
            'relatedProducts' => $related,
            'cart' => $cart,
        ]);
    }

    public function addToCart(Request $request, string $slug)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $cart = session()->get('cart_' . $slug, []);
        $productId = $request->product_id;
        $quantity = $request->quantity;

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $product = BizBoostProductModel::findOrFail($productId);
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sale_price ?? $product->price,
                'image_url' => $product->image_url,
                'quantity' => $quantity,
            ];
        }

        session()->put('cart_' . $slug, $cart);

        return back()->with('success', 'Item added to cart.');
    }

    public function updateCart(Request $request, string $slug)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        $cart = session()->get('cart_' . $slug, []);
        $productId = $request->product_id;
        $quantity = $request->quantity;

        if ($quantity < 1) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['quantity'] = $quantity;
        }

        session()->put('cart_' . $slug, $cart);

        return back();
    }

    public function checkoutPage(Request $request, string $slug)
    {
        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->with('profile')
            ->firstOrFail();

        $cart = session()->get('cart_' . $slug, []);

        if (empty($cart)) {
            return redirect()->route('bizboost.public.shop', $slug)
                ->with('error', 'Your cart is empty.');
        }

        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        return Inertia::render('BizBoost/Public/Checkout', [
            'business' => $business->load('profile'),
            'cart' => $cart,
            'subtotal' => $subtotal,
            'delivery_fee' => 0,
        ]);
    }

    public function placeOrder(Request $request, string $slug)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'delivery_address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|string|in:airtel_money,mtn_money,cash_on_delivery,bank_transfer',
        ]);

        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $cart = session()->get('cart_' . $slug, []);

        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }

        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        $order = DB::transaction(function () use ($business, $cart, $subtotal, $validated) {
            $order = BizBoostOrderModel::create([
                'business_id' => $business->id,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'delivery_address' => $validated['delivery_address'],
                'notes' => $validated['notes'],
                'subtotal' => $subtotal,
                'delivery_fee' => 0,
                'total' => $subtotal,
                'currency' => $business->currency ?? 'ZMW',
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'source' => 'direct_link',
                'meta' => ['cart_snapshot' => $cart],
            ]);

            foreach ($cart as $item) {
                BizBoostOrderItemModel::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            return $order;
        });

        session()->forget('cart_' . $slug);

        // Send WhatsApp confirmation
        try {
            app(OrderNotificationService::class)->sendOrderConfirmation($order);
        } catch (\Exception $e) {
            // Notification failure shouldn't block order placement
        }

        return redirect()->route('bizboost.public.order-confirmation', [
            'slug' => $slug,
            'order' => $order->id,
        ]);
    }

    public function orderConfirmation(string $slug, int $orderId)
    {
        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $order = BizBoostOrderModel::where('business_id', $business->id)
            ->with('items')
            ->findOrFail($orderId);

        return Inertia::render('BizBoost/Public/OrderConfirmation', [
            'business' => $business->load('profile'),
            'order' => $order,
        ]);
    }

    /**
     * Business owner: view orders.
     */
    public function orders(Request $request)
    {
        $business = $this->getBusiness($request);

        $orders = $business->orders()
            ->with('items')
            ->when($request->status, fn($q, $s) => $q->where('order_status', $s))
            ->when($request->payment_status, fn($q, $s) => $q->where('payment_status', $s))
            ->when($request->search, fn($q, $s) => $q->where(function ($q2) use ($s) {
                $q2->where('order_number', 'like', "%{$s}%")
                    ->orWhere('customer_name', 'like', "%{$s}%")
                    ->orWhere('customer_phone', 'like', "%{$s}%");
            }))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending' => $business->orders()->where('order_status', 'pending')->count(),
            'confirmed' => $business->orders()->where('order_status', 'confirmed')->count(),
            'processing' => $business->orders()->where('order_status', 'processing')->count(),
            'delivered' => $business->orders()->where('order_status', 'delivered')->count(),
            'cancelled' => $business->orders()->where('order_status', 'cancelled')->count(),
        ];

        return Inertia::render('BizBoost/Shop/Orders', [
            'orders' => $orders,
            'stats' => $stats,
            'filters' => $request->only(['status', 'payment_status', 'search']),
        ]);
    }

    public function updateOrderStatus(Request $request, int $id)
    {
        $business = $this->getBusiness($request);

        $validated = $request->validate([
            'order_status' => 'required|string|in:pending,confirmed,processing,delivered,cancelled',
            'payment_status' => 'nullable|string|in:pending,paid,failed,refunded',
        ]);

        $order = $business->orders()->findOrFail($id);
        $order->update($validated);

        if ($validated['order_status'] === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }
        if ($validated['payment_status'] === 'paid' && !$order->paid_at) {
            $order->update(['paid_at' => now()]);
        }

        // Send WhatsApp status update
        try {
            app(OrderNotificationService::class)->sendOrderStatusUpdate($order);
        } catch (\Exception $e) {
            // Notification failure shouldn't block status update
        }

        return back()->with('success', "Order #{$order->order_number} updated.");
    }

    /**
     * WhatsApp Catalog management for business owners.
     */
    public function catalogSettings(Request $request)
    {
        $business = $this->getBusiness($request);
        $integration = BizBoostIntegrationModel::where('business_id', $business->id)
            ->where('provider', 'whatsapp')
            ->first();

        $catalogStatus = null;
        if ($integration) {
            $service = new WhatsAppCatalogService($integration);
            try {
                $catalogStatus = $service->getCatalogStatus();
                if ($catalogStatus['connected']) {
                    $catalogStatus['verification'] = $service->verifyCatalogSetup();
                }
            } catch (\Exception $e) {
                $catalogStatus = ['error' => $e->getMessage()];
            }
        }

        return Inertia::render('BizBoost/Shop/CatalogSettings', [
            'hasWhatsAppIntegration' => $integration !== null,
            'whatsappPhone' => $integration?->provider_page_name,
            'catalogStatus' => $catalogStatus,
        ]);
    }

    public function createCatalog(Request $request)
    {
        $business = $this->getBusiness($request);
        $integration = BizBoostIntegrationModel::where('business_id', $business->id)
            ->where('provider', 'whatsapp')
            ->where('status', 'active')
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $service = new WhatsAppCatalogService($integration);

        try {
            $result = $service->createCatalog($validated['name']);
            $service->connectCatalogToWaba();

            return back()->with('success', 'WhatsApp catalog created and connected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function syncCatalog(Request $request)
    {
        $business = $this->getBusiness($request);
        $integration = BizBoostIntegrationModel::where('business_id', $business->id)
            ->where('provider', 'whatsapp')
            ->where('status', 'active')
            ->firstOrFail();

        $service = new WhatsAppCatalogService($integration);

        try {
            $results = $service->syncAllProducts();
            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $failCount = count($results) - $successCount;

            return back()->with(
                $failCount > 0 ? 'warning' : 'success',
                "Catalog sync completed. {$successCount} synced, {$failCount} failed."
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function disconnectCatalog(Request $request)
    {
        $business = $this->getBusiness($request);
        $integration = BizBoostIntegrationModel::where('business_id', $business->id)
            ->where('provider', 'whatsapp')
            ->where('status', 'active')
            ->firstOrFail();

        $service = new WhatsAppCatalogService($integration);

        try {
            $service->disconnectCatalogFromWaba();

            $integration->update([
                'catalog_id' => null,
                'whatsapp_catalog_settings' => null,
            ]);

            return back()->with('success', 'Catalog disconnected from WhatsApp.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
