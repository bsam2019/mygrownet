<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Services\CartService;
use App\Domain\GrowMart\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly CartService $cartService,
    ) {}

    public function index()
    {
        $orders = $this->orderService->getOrdersForUser(auth()->id());
        $cartSummary = $this->cartService->getSummary(auth()->id());

        $formatted = $orders->through(fn($o) => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'status' => $o->status,
            'payment_status' => $o->payment_status,
            'delivery_method' => $o->delivery_method,
            'total' => $o->total,
            'total_formatted' => 'K' . number_format($o->total / 100, 2),
            'item_count' => $o->items->sum('quantity'),
            'created_at' => $o->created_at->diffForHumans(),
        ]);

        return Inertia::render('GrowMart/Orders/Index', [
            'orders' => $formatted,
            'cartCount' => $cartSummary['item_count'],
        ]);
    }

    public function show(int $id)
    {
        $order = $this->orderService->getOrder($id, auth()->id());
        $cartSummary = $this->cartService->getSummary(auth()->id());

        return Inertia::render('GrowMart/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'payment_reference' => $order->payment_reference,
                'payment_phone' => $order->payment_phone,
                'payment_notes' => $order->payment_notes,
                'payment_submitted_at' => $order->payment_submitted_at?->format('M d, Y g:i A'),
                'delivery_method' => $order->delivery_method,
                'delivery_zone' => $order->delivery_zone,
                'delivery_address' => $order->delivery_address,
                'contact_phone' => $order->contact_phone,
                'special_instructions' => $order->special_instructions,
                'tracking_number' => $order->tracking_number,
                'tracking_url' => $order->tracking_url,
                'estimated_delivery_at' => $order->estimated_delivery_at?->format('M d, Y'),
                'tracking_updates' => $order->tracking_updates ?? [],
                'subtotal' => $order->subtotal,
                'subtotal_formatted' => 'K' . number_format($order->subtotal / 100, 2),
                'delivery_fee' => $order->delivery_fee,
                'delivery_fee_formatted' => 'K' . number_format($order->delivery_fee / 100, 2),
                'discount' => $order->discount,
                'discount_formatted' => 'K' . number_format($order->discount / 100, 2),
                'total' => $order->total,
                'total_formatted' => 'K' . number_format($order->total / 100, 2),
                'coupon' => $order->coupon ? ['code' => $order->coupon->code, 'type' => $order->coupon->type] : null,
                'items' => $order->items->map(fn($i) => [
                    'product_name' => $i->product_name,
                    'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price,
                    'unit_price_formatted' => 'K' . number_format($i->unit_price / 100, 2),
                    'subtotal' => $i->subtotal,
                    'subtotal_formatted' => 'K' . number_format($i->subtotal / 100, 2),
                ]),
                'created_at' => $order->created_at->format('M d, Y g:i A'),
                'paid_at' => $order->paid_at,
                'delivered_at' => $order->delivered_at,
            ],
            'cartCount' => $cartSummary['item_count'],
        ]);
    }

    public function cancel(int $id)
    {
        try {
            $this->orderService->cancelOrder($id, auth()->id());
            return back()->with('success', 'Order cancelled successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
