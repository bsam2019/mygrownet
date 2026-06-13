<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Services\OrderService;
use App\Models\GrowMart\GrowMartOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $query = GrowMartOrder::with(['user', 'items'])
            ->withCount('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('order_number', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $sortField = match ($request->sort) {
            'total' => 'total',
            'oldest' => 'created_at',
            default => 'created_at',
        };
        $sortDir = $request->sort === 'oldest' ? 'asc' : 'desc';

        $orders = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        $formatted = $orders->through(fn($o) => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'customer' => $o->user?->name ?? 'Unknown',
            'status' => $o->status,
            'payment_status' => $o->payment_status,
            'delivery_method' => $o->delivery_method,
            'item_count' => $o->items_count,
            'total' => $o->total,
            'total_formatted' => 'K' . number_format($o->total / 100, 2),
            'created_at' => $o->created_at->format('M d, Y g:i A'),
            'created_at_diff' => $o->created_at->diffForHumans(),
        ]);

        return Inertia::render('GrowMart/Admin/Orders/Index', [
            'orders' => $formatted,
            'filters' => $request->only(['status', 'payment_status', 'q', 'sort']),
        ]);
    }

    public function show(int $id)
    {
        $order = GrowMartOrder::with(['user', 'items', 'coupon'])->findOrFail($id);

        return Inertia::render('GrowMart/Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => [
                    'id' => $order->user?->id,
                    'name' => $order->user?->name ?? 'Unknown',
                    'email' => $order->user?->email ?? '',
                ],
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
                    'id' => $i->id,
                    'product_name' => $i->product_name,
                    'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price,
                    'unit_price_formatted' => 'K' . number_format($i->unit_price / 100, 2),
                    'subtotal' => $i->subtotal,
                    'subtotal_formatted' => 'K' . number_format($i->subtotal / 100, 2),
                ]),
                'created_at' => $order->created_at->format('M d, Y g:i A'),
                'paid_at' => $order->paid_at?->format('M d, Y g:i A'),
                'delivered_at' => $order->delivered_at?->format('M d, Y g:i A'),
                'cancelled_at' => $order->cancelled_at?->format('M d, Y g:i A'),
            ],
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,out_for_delivery,delivered,cancelled',
        ]);

        $order = $this->orderService->updateStatus($id, $validated['status']);

        return back()->with('success', "Order status updated to {$validated['status']}.");
    }

    public function updatePayment(Request $request, int $id)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,pending_verification,paid,failed,refunded',
        ]);

        $order = $this->orderService->updatePayment($id, $validated['payment_status']);

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

        $order = $this->orderService->updateTracking($id, $validated);

        return back()->with('success', 'Tracking information updated.');
    }
}
