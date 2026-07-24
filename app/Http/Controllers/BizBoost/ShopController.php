<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\BizBoost\Services\OrderNotificationService;
use App\Domain\BizBoost\Services\WhatsAppCatalogService;
use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Domain\BizBoost\Repositories\IntegrationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private ProductRepositoryInterface $productRepo,
        private IntegrationRepositoryInterface $integrationRepo,
    ) {}

    public function shopPage(Request $request, string $slug)
    {
        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $categories = $this->productRepo->getCategories($business->id);
        $products = $this->productRepo->findActiveByBusiness($business->id, $request->only(['category', 'search']));
        $cart = session()->get('cart_' . $slug, []);

        return Inertia::render('BizBoost/Public/Shop', [
            'business' => $business->toArray(),
            'products' => $products,
            'categories' => $categories,
            'cart' => $cart,
            'filters' => $request->only(['category', 'search']),
        ]);
    }

    public function productDetail(string $slug, int $productId)
    {
        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $product = $this->productRepo->findById($productId);
        if (!$product) {
            abort(404);
        }

        $related = $this->productRepo->findActiveByBusiness($business->id, ['exclude' => $productId]);
        $cart = session()->get('cart_' . $slug, []);

        return Inertia::render('BizBoost/Public/ProductDetail', [
            'business' => $business->toArray(),
            'product' => $product->toArray(),
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
            $product = $this->productRepo->findById($productId);
            if (!$product) {
                return back()->with('error', 'Product not found.');
            }
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->salePrice ?? $product->price,
                'image_url' => $product->imageUrl,
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

        if ($request->quantity < 1) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['quantity'] = $request->quantity;
        }

        session()->put('cart_' . $slug, $cart);
        return back();
    }

    public function checkoutPage(Request $request, string $slug)
    {
        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $cart = session()->get('cart_' . $slug, []);
        if (empty($cart)) {
            return redirect()->route('bizboost.public.shop', $slug)->with('error', 'Your cart is empty.');
        }

        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        return Inertia::render('BizBoost/Public/Checkout', [
            'business' => $business->toArray(),
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

        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $cart = session()->get('cart_' . $slug, []);
        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }

        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        $order = DB::transaction(function () use ($business, $cart, $subtotal, $validated) {
            $orderId = DB::table('bizboost_orders')->insertGetId([
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
                'meta' => json_encode(['cart_snapshot' => $cart]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($cart as $item) {
                DB::table('bizboost_order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $orderId;
        });

        session()->forget('cart_' . $slug);

        try {
            app(OrderNotificationService::class)->sendOrderConfirmation(['id' => $order]);
        } catch (\Exception $e) {
        }

        return redirect()->route('bizboost.public.order-confirmation', ['slug' => $slug, 'order' => $order]);
    }

    public function orderConfirmation(string $slug, int $orderId)
    {
        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $order = DB::table('bizboost_orders')->where('id', $orderId)->where('business_id', $business->id)->first();
        if (!$order) {
            abort(404);
        }

        $items = DB::table('bizboost_order_items')->where('order_id', $orderId)->get();

        return Inertia::render('BizBoost/Public/OrderConfirmation', [
            'business' => $business->toArray(),
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function orders(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        $orders = DB::table('bizboost_orders')
            ->where('business_id', $business->id)
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
            'pending' => DB::table('bizboost_orders')->where('business_id', $business->id)->where('order_status', 'pending')->count(),
            'confirmed' => DB::table('bizboost_orders')->where('business_id', $business->id)->where('order_status', 'confirmed')->count(),
            'processing' => DB::table('bizboost_orders')->where('business_id', $business->id)->where('order_status', 'processing')->count(),
            'delivered' => DB::table('bizboost_orders')->where('business_id', $business->id)->where('order_status', 'delivered')->count(),
            'cancelled' => DB::table('bizboost_orders')->where('business_id', $business->id)->where('order_status', 'cancelled')->count(),
        ];

        return Inertia::render('BizBoost/Shop/Orders', [
            'orders' => $orders,
            'stats' => $stats,
            'filters' => $request->only(['status', 'payment_status', 'search']),
        ]);
    }

    public function updateOrderStatus(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        $validated = $request->validate([
            'order_status' => 'required|string|in:pending,confirmed,processing,delivered,cancelled',
            'payment_status' => 'nullable|string|in:pending,paid,failed,refunded',
        ]);

        $update = $validated;
        if ($validated['order_status'] === 'delivered') {
            $update['delivered_at'] = now();
        }
        if ($validated['payment_status'] === 'paid') {
            $update['paid_at'] = now();
        }

        DB::table('bizboost_orders')->where('id', $id)->where('business_id', $business->id)->update($update + ['updated_at' => now()]);

        try {
            app(OrderNotificationService::class)->sendOrderStatusUpdate(['id' => $id]);
        } catch (\Exception $e) {
        }

        return back()->with('success', "Order #{$id} updated.");
    }

    public function catalogSettings(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $integration = $this->integrationRepo->findByBusiness($business->id, ['platform' => 'whatsapp']);

        $firstIntegration = !empty($integration) ? $integration[0] : null;
        $catalogStatus = null;

        if ($firstIntegration) {
            $service = new WhatsAppCatalogService($firstIntegration);
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
            'hasWhatsAppIntegration' => $firstIntegration !== null,
            'whatsappPhone' => $firstIntegration?->accountName,
            'catalogStatus' => $catalogStatus,
        ]);
    }

    public function createCatalog(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $integrationData = $this->integrationRepo->findByBusiness($business->id, ['platform' => 'whatsapp', 'status' => 'active']);

        if (empty($integrationData)) {
            abort(404);
        }

        $validated = $request->validate(['name' => 'required|string|max:255']);
        $integration = $integrationData[0];
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
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $integrationData = $this->integrationRepo->findByBusiness($business->id, ['platform' => 'whatsapp', 'status' => 'active']);

        if (empty($integrationData)) {
            abort(404);
        }

        $service = new WhatsAppCatalogService($integrationData[0]);

        try {
            $results = $service->syncAllProducts();
            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $failCount = count($results) - $successCount;
            return back()->with($failCount > 0 ? 'warning' : 'success', "Catalog sync completed. {$successCount} synced, {$failCount} failed.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function disconnectCatalog(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $integrationData = $this->integrationRepo->findByBusiness($business->id, ['platform' => 'whatsapp', 'status' => 'active']);

        if (empty($integrationData)) {
            abort(404);
        }

        $service = new WhatsAppCatalogService($integrationData[0]);

        try {
            $service->disconnectCatalogFromWaba();
            return back()->with('success', 'Catalog disconnected from WhatsApp.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
