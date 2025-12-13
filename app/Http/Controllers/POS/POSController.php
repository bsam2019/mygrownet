<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Domain\POS\Services\POSService;
use App\Domain\Inventory\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Standalone POS Controller
 * 
 * Handles POS operations for standalone module access
 * For integrated access (via GrowBiz, etc.), use the respective module's controller
 */
class POSController extends Controller
{
    protected POSService $posService;
    protected InventoryService $inventoryService;

    public function __construct(POSService $posService, InventoryService $inventoryService)
    {
        $this->posService = $posService->forModule('pos');
        $this->inventoryService = $inventoryService->forModule('pos');
    }

    /**
     * POS Dashboard
     */
    public function index()
    {
        $currentShift = $this->posService->getCurrentShift();
        $todayReport = $this->posService->getDailyReport();
        $settings = $this->posService->getSettings();

        return Inertia::render('POS/Dashboard', [
            'currentShift' => $currentShift,
            'todayReport' => $todayReport,
            'settings' => $settings,
        ]);
    }

    /**
     * POS Terminal
     */
    public function terminal()
    {
        $currentShift = $this->posService->getCurrentShift();
        $quickProducts = $this->posService->getQuickProducts();
        $settings = $this->posService->getSettings();
        $inventoryItems = $this->inventoryService->getItems(['is_active' => true]);

        return Inertia::render('POS/Terminal', [
            'currentShift' => $currentShift,
            'quickProducts' => $quickProducts,
            'settings' => $settings,
            'inventoryItems' => $inventoryItems,
        ]);
    }

    /**
     * Start a new shift
     */
    public function startShift(Request $request)
    {
        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'opening_notes' => 'nullable|string|max:500',
        ]);

        try {
            $shift = $this->posService->startShift($validated);
            return back()->with('success', 'Shift started successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Close current shift
     */
    public function closeShift(Request $request, int $shiftId)
    {
        $validated = $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:500',
        ]);

        try {
            $shift = $this->posService->closeShift($shiftId, $validated);
            return back()->with('success', 'Shift closed successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create a sale
     */
    public function createSale(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
            'items.*.discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,mobile_money,card,credit,split',
            'amount_paid' => 'required|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $sale = $this->posService->createSale($validated);
            return response()->json([
                'success' => true,
                'sale' => $sale,
                'message' => 'Sale completed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * List sales
     */
    public function sales(Request $request)
    {
        $filters = $request->only(['shift_id', 'status', 'date', 'date_from', 'date_to', 'payment_method', 'per_page']);
        $sales = $this->posService->getSales($filters);

        return Inertia::render('POS/Sales', [
            'sales' => $sales,
            'filters' => $filters,
        ]);
    }

    /**
     * Get sale details
     */
    public function getSale(int $saleId)
    {
        $sale = $this->posService->getSale($saleId);
        
        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        return response()->json($sale);
    }

    /**
     * Void a sale
     */
    public function voidSale(Request $request, int $saleId)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $sale = $this->posService->voidSale($saleId, $validated['reason'] ?? null);
            return back()->with('success', 'Sale voided successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * List shifts
     */
    public function shifts(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to', 'per_page']);
        $shifts = $this->posService->getShifts($filters);

        return Inertia::render('POS/Shifts', [
            'shifts' => $shifts,
            'filters' => $filters,
        ]);
    }

    /**
     * Reports overview
     */
    public function reports()
    {
        $todayReport = $this->posService->getDailyReport();
        $weeklyStats = $this->posService->getWeeklyStats();

        return Inertia::render('POS/Reports', [
            'todayReport' => $todayReport,
            'weeklyStats' => $weeklyStats ?? [],
        ]);
    }

    /**
     * Daily report
     */
    public function dailyReport(Request $request)
    {
        $date = $request->get('date');
        $report = $this->posService->getDailyReport($date);

        return Inertia::render('POS/DailyReport', [
            'report' => $report,
            'date' => $date ?? today()->toDateString(),
        ]);
    }

    /**
     * POS Settings (includes quick products management)
     */
    public function settings()
    {
        $settings = $this->posService->getSettings();
        $quickProducts = $this->posService->getQuickProducts();
        $inventoryItems = $this->inventoryService->getItems(['is_active' => true]);

        return Inertia::render('POS/Settings', [
            'settings' => $settings,
            'quickProducts' => $quickProducts,
            'inventoryItems' => $inventoryItems,
        ]);
    }

    /**
     * Update POS Settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:500',
            'business_phone' => 'nullable|string|max:20',
            'receipt_header' => 'nullable|string|max:255',
            'receipt_footer' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'enable_tax' => 'boolean',
            'require_customer' => 'boolean',
            'allow_credit_sales' => 'boolean',
            'auto_print_receipt' => 'boolean',
            'track_inventory' => 'boolean',
            'currency' => 'nullable|string|max:3',
            'currency_symbol' => 'nullable|string|max:5',
        ]);

        $settings = $this->posService->updateSettings($validated);
        return back()->with('success', 'Settings updated successfully');
    }

    /**
     * Sync quick products (called from settings page)
     */
    public function syncQuickProducts(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:inventory_items,id',
        ]);

        $this->posService->syncQuickProducts($validated['product_ids']);
        return back()->with('success', 'Quick products updated');
    }
}
