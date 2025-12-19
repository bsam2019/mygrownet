<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Domain\Marketplace\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->status,
        ];

        $orders = $this->orderService->getByBuyer(
            $request->user()->id,
            $filters,
            15
        );

        return Inertia::render('Marketplace/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $request->status ?? '',
            ],
        ]);
    }

    public function show(int $id)
    {
        $order = $this->orderService->getById($id);

        if (!$order || $order->buyer_id !== auth()->id()) {
            abort(404);
        }

        return Inertia::render('Marketplace/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function confirm(int $id)
    {
        $order = $this->orderService->getById($id);

        if (!$order || $order->buyer_id !== auth()->id()) {
            abort(404);
        }

        try {
            $this->orderService->confirmReceipt($id);

            return back()->with('success', 'Order confirmed! Funds have been released to the seller.');

        } catch (\Exception $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $order = $this->orderService->getById($id);

        if (!$order || $order->buyer_id !== auth()->id()) {
            abort(404);
        }

        try {
            $this->orderService->cancelOrder($id, $request->reason, 'buyer');

            return back()->with('success', 'Order cancelled.');

        } catch (\Exception $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }
    }

    public function dispute(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $order = $this->orderService->getById($id);

        if (!$order || $order->buyer_id !== auth()->id()) {
            abort(404);
        }

        try {
            $this->orderService->openDispute($id, $request->reason);

            return back()->with('success', 'Dispute opened. Our team will review and contact you.');

        } catch (\Exception $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }
    }
}
