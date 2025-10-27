<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReceiptController extends Controller
{
    /**
     * Display all receipts
     */
    public function index(Request $request)
    {
        $query = Receipt::with('user')
            ->latest();
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Search by receipt number
        if ($request->filled('search')) {
            $query->where('receipt_number', 'like', '%' . $request->search . '%');
        }
        
        $receipts = $query->paginate(20);
        
        // Get statistics
        $stats = [
            'total_receipts' => Receipt::count(),
            'total_amount' => Receipt::sum('amount'),
            'emailed_count' => Receipt::where('emailed', true)->count(),
            'by_type' => Receipt::selectRaw('type, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('type')
                ->get(),
        ];
        
        return Inertia::render('Admin/Receipts/Index', [
            'receipts' => $receipts,
            'stats' => $stats,
            'filters' => $request->only(['type', 'user_id', 'search']),
        ]);
    }
    
    /**
     * View receipt details
     */
    public function show(Receipt $receipt)
    {
        $receipt->load('user');
        
        return Inertia::render('Admin/Receipts/Show', [
            'receipt' => $receipt,
        ]);
    }
    
    /**
     * Resend receipt email
     */
    public function resend(Receipt $receipt)
    {
        try {
            $receiptService = app(\App\Services\ReceiptService::class);
            
            $receiptService->emailReceipt(
                $receipt->user,
                $receipt->pdf_path,
                'MyGrowNet - Receipt (Resent)'
            );
            
            $receipt->update([
                'emailed' => true,
                'emailed_at' => now(),
            ]);
            
            return back()->with('success', 'Receipt email resent successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resend receipt: ' . $e->getMessage());
        }
    }
    
    /**
     * Download receipt
     */
    public function download(Receipt $receipt)
    {
        if (!file_exists($receipt->pdf_path)) {
            abort(404, 'Receipt file not found');
        }
        
        return response()->download(
            $receipt->pdf_path,
            "receipt_{$receipt->receipt_number}.pdf"
        );
    }
    
    /**
     * Bulk download receipts
     */
    public function bulkDownload(Request $request)
    {
        $request->validate([
            'receipt_ids' => 'required|array',
            'receipt_ids.*' => 'exists:receipts,id',
        ]);
        
        // For now, return a ZIP file with all receipts
        // This would require a ZIP library implementation
        
        return back()->with('info', 'Bulk download feature coming soon');
    }
}
