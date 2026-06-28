<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {}

    public function index(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $status = $request->get('status');
        
        $query = GrowBuilderOrder::where('site_id', $siteId)
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20);

        $stats = [
            'pending' => GrowBuilderOrder::where('site_id', $siteId)->pending()->count(),
            'processing' => GrowBuilderOrder::where('site_id', $siteId)->where('status', 'processing')->count(),
            'completed' => GrowBuilderOrder::where('site_id', $siteId)->where('status', 'completed')->count(),
            'total_revenue' => GrowBuilderOrder::where('site_id', $siteId)->paid()->sum('total'),
        ];

        return Inertia::render('GrowBuilder/Orders/Index', [
            'site' => $this->siteToArray($site),
            'orders' => $orders,
            'stats' => $stats,
            'currentStatus' => $status,
        ]);
    }

    public function show(Request $request, int $siteId, int $orderId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $order = GrowBuilderOrder::where('site_id', $siteId)
            ->with(['payments', 'invoice'])
            ->findOrFail($orderId);

        return Inertia::render('GrowBuilder/Orders/Show', [
            'site' => $this->siteToArray($site),
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, int $siteId, int $orderId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|in:processing,shipped,delivered,completed,cancelled',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $order = GrowBuilderOrder::where('site_id', $siteId)->findOrFail($orderId);

        $updateData = ['status' => $validated['status']];

        if (isset($validated['admin_notes'])) {
            $updateData['admin_notes'] = $validated['admin_notes'];
        }

        // Set timestamps based on status
        switch ($validated['status']) {
            case 'shipped':
                $updateData['shipped_at'] = now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = now();
                break;
        }

        $order->update($updateData);

        return back()->with('success', 'Order status updated');
    }

    public function markAsPaid(Request $request, int $siteId, int $orderId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'payment_reference' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $order = GrowBuilderOrder::where('site_id', $siteId)->findOrFail($orderId);

        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $validated['payment_reference'] ?? 'MANUAL',
            'payment_method' => $validated['payment_method'] ?? 'cash',
        ]);

        return back()->with('success', 'Order marked as paid');
    }

    private function siteToArray($site): array
    {
        return [
            'id' => $site->getId()->value(),
            'name' => $site->getName(),
            'subdomain' => $site->getSubdomain()->value(),
            'plan' => $site->getPlan()->value(),
        ];
    }
}
