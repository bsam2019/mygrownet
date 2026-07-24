<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Services\OrderService;
use App\Domain\GrowMart\Repositories\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function index(Request $request)
    {
        $orders = $this->orderRepository->findAll([
            'search' => $request->q,
            'status' => $request->status,
            'payment_status' => $request->payment_status,
            'sort' => $request->sort,
        ]);

        $formatted = array_map(fn($o) => [
            'id' => $o['id'],
            'order_number' => $o['order_number'],
            'customer' => $o['user']['name'] ?? 'Unknown',
            'status' => $o['status'],
            'payment_status' => $o['payment_status'],
            'delivery_method' => $o['delivery_method'] ?? '',
            'item_count' => count($o['items'] ?? []),
            'total' => $o['total'],
            'total_formatted' => 'K' . number_format($o['total'] / 100, 2),
            'created_at' => \Carbon\Carbon::parse($o['created_at'])->format('M d, Y g:i A'),
            'created_at_diff' => \Carbon\Carbon::parse($o['created_at'])->diffForHumans(),
        ], $orders['data'] ?? []);

        return Inertia::render('GrowMart/Admin/Orders/Index', [
            'orders' => $formatted,
            'filters' => $request->only(['status', 'payment_status', 'q', 'sort']),
        ]);
    }

    public function show(int $id)
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            abort(404);
        }

        $items = array_map(fn($i) => [
            'id' => $i['id'],
            'product_name' => $i['product_name'],
            'quantity' => $i['quantity'],
            'unit_price' => $i['unit_price'],
            'unit_price_formatted' => 'K' . number_format($i['unit_price'] / 100, 2),
            'subtotal' => $i['subtotal'],
            'subtotal_formatted' => 'K' . number_format($i['subtotal'] / 100, 2),
        ], $order['items'] ?? []);

        return Inertia::render('GrowMart/Admin/Orders/Show', [
            'order' => [
                'id' => $order['id'],
                'order_number' => $order['order_number'],
                'customer' => [
                    'id' => $order['user']['id'] ?? null,
                    'name' => $order['user']['name'] ?? 'Unknown',
                    'email' => $order['user']['email'] ?? '',
                ],
                'status' => $order['status'],
                'payment_status' => $order['payment_status'],
                'payment_method' => $order['payment_method'] ?? '',
                'payment_reference' => $order['payment_reference'] ?? null,
                'payment_phone' => $order['payment_phone'] ?? null,
                'payment_notes' => $order['payment_notes'] ?? null,
                'payment_submitted_at' => isset($order['payment_submitted_at']) ? \Carbon\Carbon::parse($order['payment_submitted_at'])->format('M d, Y g:i A') : null,
                'delivery_method' => $order['delivery_method'] ?? '',
                'delivery_zone' => $order['delivery_zone'] ?? null,
                'delivery_address' => $order['delivery_address'] ?? null,
                'contact_phone' => $order['contact_phone'] ?? null,
                'special_instructions' => $order['special_instructions'] ?? null,
                'tracking_number' => $order['tracking_number'] ?? null,
                'tracking_url' => $order['tracking_url'] ?? null,
                'estimated_delivery_at' => isset($order['estimated_delivery_at']) ? \Carbon\Carbon::parse($order['estimated_delivery_at'])->format('M d, Y') : null,
                'tracking_updates' => $order['tracking_updates'] ?? [],
                'subtotal' => $order['subtotal'],
                'subtotal_formatted' => 'K' . number_format($order['subtotal'] / 100, 2),
                'delivery_fee' => $order['delivery_fee'] ?? 0,
                'delivery_fee_formatted' => 'K' . number_format(($order['delivery_fee'] ?? 0) / 100, 2),
                'discount' => $order['discount'] ?? 0,
                'discount_formatted' => 'K' . number_format(($order['discount'] ?? 0) / 100, 2),
                'total' => $order['total'],
                'total_formatted' => 'K' . number_format($order['total'] / 100, 2),
                'coupon' => isset($order['coupon']) ? ['code' => $order['coupon']['code'], 'type' => $order['coupon']['type']] : null,
                'items' => $items,
                'created_at' => \Carbon\Carbon::parse($order['created_at'])->format('M d, Y g:i A'),
                'paid_at' => isset($order['paid_at']) ? \Carbon\Carbon::parse($order['paid_at'])->format('M d, Y g:i A') : null,
                'delivered_at' => isset($order['delivered_at']) ? \Carbon\Carbon::parse($order['delivered_at'])->format('M d, Y g:i A') : null,
                'cancelled_at' => isset($order['cancelled_at']) ? \Carbon\Carbon::parse($order['cancelled_at'])->format('M d, Y g:i A') : null,
            ],
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,out_for_delivery,delivered,cancelled',
        ]);

        $this->orderService->updateStatus($id, $validated['status']);

        return back()->with('success', "Order status updated to {$validated['status']}.");
    }

    public function updatePayment(Request $request, int $id)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,pending_verification,paid,failed,refunded',
        ]);

        $this->orderService->updatePayment($id, $validated['payment_status']);

        return back()->with('success', "Payment status updated to {$validated['payment_status']}.");
    }

    public function updateTracking(Request $request, int $id)
    {
        $validated = $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:500',
            'estimated_delivery_at' => 'nullable|date',
            'tracking_status' => 'nullable|string|max:255',
            'tracking_message' => 'nullable|string|max:500',
        ]);

        $this->orderService->updateTracking($id, $validated);

        return back()->with('success', 'Tracking information updated.');
    }
}
