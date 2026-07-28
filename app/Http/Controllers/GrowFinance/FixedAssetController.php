<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\FixedAssetService;
use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FixedAssetController extends Controller
{
    public function __construct(
        private FixedAssetService $assetService,
    ) {}

    public function index(Request $request): Response
    {
        $assets = $this->assetService->getAll($request->user()->id);

        $summary = [
            'total_cost' => array_sum(array_column($assets, 'cost')),
            'total_accumulated' => array_sum(array_column($assets, 'accumulated_depreciation')),
            'total_nbv' => array_sum(array_column($assets, 'net_book_value')),
            'active_count' => count(array_filter($assets, fn($a) => $a['status'] === 'active')),
        ];

        return Inertia::render('GrowFinance/FixedAssets/Index', [
            'assets' => $assets,
            'summary' => $summary,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('GrowFinance/FixedAssets/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'purchase_date' => 'required|date',
            'cost' => 'required|numeric|min:0.01',
            'residual_value' => 'nullable|numeric|min:0',
            'useful_life_months' => 'required|integer|min:1|max:600',
            'depreciation_method' => 'required|in:straight_line,reducing_balance',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'location' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->assetService->acquire($request->user()->id, $validated);

        return redirect()->route('growfinance.fixed-assets.index')
            ->with('success', 'Fixed asset acquired successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $asset = $this->assetService->findById($id);
        if (!$asset || $asset->businessId !== $request->user()->id) {
            abort(404);
        }
        $schedule = $this->assetService->getSchedule($id);

        return Inertia::render('GrowFinance/FixedAssets/Show', [
            'asset' => $asset->toArray(),
            'schedule' => $schedule,
        ]);
    }

    public function runDepreciation(Request $request, int $id): RedirectResponse
    {
        $entry = $this->assetService->runDepreciation(
            $id,
            new DateTimeImmutable($request->get('period_date', 'now'))
        );

        if ($entry) {
            return back()->with('success', 'Depreciation posted for this period.');
        }
        return back()->with('info', 'No depreciation due for this period.');
    }

    public function runAllDepreciation(Request $request): RedirectResponse
    {
        $results = $this->assetService->runAllDepreciation(
            $request->user()->id,
            new DateTimeImmutable($request->get('period_date', 'now'))
        );

        $count = count($results);
        if ($count > 0) {
            return back()->with('success', "Depreciation posted for {$count} asset(s).");
        }
        return back()->with('info', 'No depreciation due.');
    }

    public function dispose(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'disposal_date' => 'required|date',
            'disposal_proceeds' => 'nullable|numeric|min:0',
        ]);

        $this->assetService->dispose(
            $id,
            new DateTimeImmutable($validated['disposal_date']),
            isset($validated['disposal_proceeds']) ? (float) $validated['disposal_proceeds'] : null,
        );

        return redirect()->route('growfinance.fixed-assets.index')
            ->with('success', 'Asset disposed successfully.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $asset = $this->assetService->findById($id);
        if (!$asset || $asset->businessId !== $request->user()->id) {
            abort(404);
        }
        $this->assetService->delete($id);

        return redirect()->route('growfinance.fixed-assets.index')
            ->with('success', 'Asset deleted.');
    }
}
