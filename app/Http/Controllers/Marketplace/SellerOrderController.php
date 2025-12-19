<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Domain\Marketplace\Services\SellerService;
use App\Domain\Marketplace\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SellerOrderController extends Controller
{
    public function __construct(
        private SellerService $sellerService,
        private OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            return redirect()->route('marketplace.seller.register');
        }

        $filters = [
            'status' => $request->status,
        ];

        $orders = $this->orderService->getBySeller($seller->id, $filters, 20);

        return Inertia::render('Marketplace/Seller/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $request->status ?? '',
            ],
        ]);
    }

    public function show(Request $request, int $id)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);
        $order = $this->orderService->getById($id);

        if (!$seller || !$order || $order->seller_id !== $seller->id) {
            abort(404);
        }

        return Inertia::render('Marketplace/Seller/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function ship(Request $request, int $id)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);
        $order = $this->orderService->getById($id);

        if (!$seller || !$order || $order->seller_id !== $seller->id) {
            abort(404);
        }

        $request->validate([
            'tracking_info' => 'nullable|string|max:500',
        ]);

        try {
            $this->orderService->markAsShipped($id, $request->tracking_info);

            return back()->with('success', 'Order marked as shipped. Buyer has been notified.');

        } catch (\Exception $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }
    }

    public function deliver(Request $request, int $id)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);
        $order = $this->orderService->getById($id);

        if (!$seller || !$order || $order->seller_id !== $seller->id) {
            abort(404);
        }

        $request->validate([
            'delivery_proof' => 'nullable|image|max:5120',
        ]);

        $proofPath = null;
        if ($request->hasFile('delivery_proof')) {
            $proofPath = $request->file('delivery_proof')
                ->store('marketplace/delivery-proofs', 'public');
        }

        try {
            $this->orderService->markAsDelivered($id, $proofPath);

            return back()->with('success', 'Order marked as delivered. Waiting for buyer confirmation.');

        } catch (\Exception $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request, int $id)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);
        $order = $this->orderService->getById($id);

        if (!$seller || !$order || $order->seller_id !== $seller->id) {
            abort(404);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->orderService->cancelOrder($id, $request->reason, 'seller');

            return back()->with('success', 'Order cancelled. Buyer will be refunded.');

        } catch (\Exception $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }
    }
}
