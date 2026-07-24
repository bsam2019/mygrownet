<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\OrderRepositoryInterface;
use App\Domain\BizBoost\Repositories\CustomerRepositoryInterface;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private OrderRepositoryInterface $orderRepo,
        private CustomerRepositoryInterface $customerRepo,
        private ProductRepositoryInterface $productRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Orders/Index', [
            'orders' => $this->orderRepo->findByBusiness($business->id),
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
            'stats' => [],
        ]);
    }

    public function create(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Orders/Create', [
            'customers' => $this->customerRepo->findByBusiness($business->id),
            'products' => $this->productRepo->findActiveByBusiness($business->id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|integer|exists:biz_boost_customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:biz_boost_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
            'payment_method' => 'nullable|string|max:50',
            'payment_status' => 'required|in:pending,paid,partial,refunded',
            'shipping_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->orderRepo->save(new \App\Domain\BizBoost\Entities\Order(
            id: null,
            businessId: $business->id,
            customerId: $validated['customer_id'] ?? null,
            orderNumber: 'ORD-' . strtoupper(\Illuminate\Support\Str::random(8)),
            items: $validated['items'],
            subtotal: (float) $validated['subtotal'],
            discount: (float) ($validated['discount'] ?? 0),
            tax: (float) ($validated['tax'] ?? 0),
            shipping: (float) ($validated['shipping'] ?? 0),
            total: (float) $validated['total'],
            status: $validated['status'],
            paymentMethod: $validated['payment_method'] ?? null,
            paymentStatus: $validated['payment_status'],
            shippingAddress: $validated['shipping_address'] ?? null,
            notes: $validated['notes'] ?? null,
            orderDate: now()->toDateTimeString(),
            createdAt: null,
            updatedAt: null,
        ));

        return redirect()->route('bizboost.orders.index')
            ->with('success', 'Order created successfully.');
    }

    public function show(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $order = $this->orderRepo->findById($business->id, $id);

        if (!$order) {
            abort(404);
        }

        return Inertia::render('BizBoost/Orders/Show', [
            'order' => $order->toArray(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'nullable|in:pending,paid,partial,refunded',
            'notes' => 'nullable|string|max:1000',
        ]);

        $existing = $this->orderRepo->findById(null, $id);
        if (!$existing) {
            abort(404);
        }

        $this->orderRepo->save(new \App\Domain\BizBoost\Entities\Order(
            id: $existing->id,
            businessId: $existing->businessId,
            customerId: $existing->customerId,
            orderNumber: $existing->orderNumber,
            items: $existing->items,
            subtotal: $existing->subtotal,
            discount: $existing->discount,
            tax: $existing->tax,
            shipping: $existing->shipping,
            total: $existing->total,
            status: $validated['status'],
            paymentMethod: $existing->paymentMethod,
            paymentStatus: $validated['payment_status'] ?? $existing->paymentStatus,
            shippingAddress: $existing->shippingAddress,
            notes: $validated['notes'] ?? $existing->notes,
            orderDate: $existing->orderDate,
            createdAt: $existing->createdAt,
            updatedAt: null,
        ));

        return back()->with('success', 'Order updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->orderRepo->delete($id);
        return redirect()->route('bizboost.orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}