<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Domain\POS\Services\POSService;
use App\Domain\Inventory\Services\InventoryService;
use App\Domain\Module\Services\ModuleIntegrationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * GrowBiz POS Controller
 * 
 * Uses the shared POS service with 'growbiz' module context.
 * This allows GrowBiz to have its own POS data while using shared code.
 */
class POSController extends Controller
{
    protected POSService $posService;
    protected InventoryService $inventoryService;
    protected ModuleIntegrationService $integrationService;

    public function __construct(
        POSService $posService,
        InventoryService $inventoryService,
        ModuleIntegrationService $integrationService
    ) {
        // Configure services for GrowBiz context
        $this->posService = $posService->forModule('growbiz');
        $this->inventoryService = $inventoryService->forModule('growbiz');
        $this->integrationService = $integrationService;
    }

    /**
     * Check if POS is enabled for this user in GrowBiz
     */
    protected function checkPOSEnabled(): bool
    {
        return $this->integrationService->isEnabled(auth()->id(), 'growbiz', 'pos');
    }

    // Main POS Terminal
    public function index()
    {
        return Inertia::render('GrowBiz/POS/Terminal', [
            'activeShift' => $this->posService->getCurrentShift(),
            'quickProducts' => $this->posService->getQuickProducts(),
            'settings' => $this->posService->getSettings(),
            'todayStats' => $this->posService->getDailyReport(),
            'isPOSEnabled' => $this->checkPOSEnabled(),
        ]);
    }

    // Sales History
    public function sales(Request $request)
    {
        $filters = $request->only(['date', 'date_from', 'date_to', 'payment_method', 'search', 'per_page']);

        return Inertia::render('GrowBiz/POS/Sales', [
            'sales' => $this->posService->getSales($filters),
            'filters' => $filters,
            'todayStats' => $this->posService->getDailyReport(),
        ]);
    }

    // Create Sale
    public function storeSale(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.inventory_item_id' => 'nullable|integer',
            'items.*.discount' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_id' => 'nullable|integer',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,mobile_money,card,credit,split',
            'payment_reference' => 'nullable|string|max:100',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $sale = $this->posService->createSale($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'sale' => $sale,
                    'message' => 'Sale completed! #' . $sale->sale_number,
                ], 201);
            }

            return back()->with('success', 'Sale completed! #' . $sale->sale_number);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // Get Sale Details
    public function showSale(int $id)
    {
        $sale = $this->posService->getSale($id);

        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        return response()->json($sale);
    }

    // Void Sale
    public function voidSale(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $sale = $this->posService->voidSale($id, $validated['reason'] ?? null);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'sale' => $sale]);
            }

            return back()->with('success', 'Sale voided');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // Shifts
    public function shifts()
    {
        return Inertia::render('GrowBiz/POS/Shifts', [
            'activeShift' => $this->posService->getCurrentShift(),
            'shiftHistory' => $this->posService->getShifts(['per_page' => 20]),
        ]);
    }

    public function openShift(Request $request)
    {
        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $shift = $this->posService->startShift([
                'opening_cash' => $validated['opening_cash'],
                'opening_notes' => $validated['notes'] ?? null,
            ]);

            if ($request->wantsJson()) {
                return response()->json($shift, 201);
            }

            return back()->with('success', 'Shift opened! #' . $shift->shift_number);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function closeShift(Request $request, int $id)
    {
        $validated = $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $shift = $this->posService->closeShift($id, [
                'closing_cash' => $validated['closing_cash'],
                'closing_notes' => $validated['notes'] ?? null,
            ]);

            if ($request->wantsJson()) {
                return response()->json($shift);
            }

            return back()->with('success', 'Shift closed successfully');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // Product Search (for POS terminal)
    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');
        $products = $this->inventoryService->getItems([
            'search' => $search,
            'is_active' => true,
            'per_page' => 20,
        ]);

        return response()->json($products);
    }

    // Quick Products Management
    public function quickProducts()
    {
        return Inertia::render('GrowBiz/POS/QuickProducts', [
            'quickProducts' => $this->posService->getQuickProducts(),
            'inventoryItems' => $this->inventoryService->getItems(['is_active' => true]),
        ]);
    }

    public function storeQuickProduct(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:inventory_items,id',
        ]);

        $this->posService->syncQuickProducts($validated['product_ids']);

        return response()->json(['success' => true], 201);
    }

    // Settings
    public function settings()
    {
        return Inertia::render('GrowBiz/POS/Settings', [
            'settings' => $this->posService->getSettings(),
            'integrations' => $this->integrationService->getIntegrations(auth()->id(), 'growbiz'),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'receipt_header' => 'nullable|string|max:255',
            'receipt_footer' => 'nullable|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:500',
            'business_phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'enable_tax' => 'boolean',
            'require_customer' => 'boolean',
            'allow_credit_sales' => 'boolean',
            'auto_print_receipt' => 'boolean',
            'track_inventory' => 'boolean',
        ]);

        $settings = $this->posService->updateSettings($validated);

        if ($request->wantsJson()) {
            return response()->json($settings);
        }

        return back()->with('success', 'Settings saved');
    }

    // Daily Report
    public function dailyReport(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        return Inertia::render('GrowBiz/POS/DailyReport', [
            'date' => $date,
            'stats' => $this->posService->getDailyReport($date),
            'sales' => $this->posService->getSales(['date' => $date]),
        ]);
    }

    // Toggle POS integration
    public function toggleIntegration(Request $request)
    {
        $enabled = $this->integrationService->toggle(auth()->id(), 'growbiz', 'pos');

        return response()->json([
            'success' => true,
            'enabled' => $enabled,
            'message' => $enabled ? 'POS enabled for GrowBiz' : 'POS disabled for GrowBiz',
        ]);
    }
}
