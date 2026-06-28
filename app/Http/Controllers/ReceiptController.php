<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReceiptController extends Controller
{
    /**
     * Display user's receipts
     */
    public function index()
    {
        $receipts = auth()->user()->receipts()
            ->latest()
            ->paginate(20);
            
        return inertia('Receipts/Index', [
            'receipts' => $receipts,
        ]);
    }
    
    /**
     * Download receipt PDF
     */
    public function download(Receipt $receipt): BinaryFileResponse
    {
        // Ensure user owns this receipt
        if ($receipt->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }
        
        if (!file_exists($receipt->pdf_path)) {
            abort(404, 'Receipt file not found');
        }
        
        return response()->download(
            $receipt->pdf_path,
            "receipt_{$receipt->receipt_number}.pdf"
        );
    }
    
    /**
     * View receipt in browser
     */
    public function view(Receipt $receipt)
    {
        // Ensure user owns this receipt
        if ($receipt->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }
        
        if (!file_exists($receipt->pdf_path)) {
            abort(404, 'Receipt file not found');
        }
        
        return response()->file($receipt->pdf_path);
    }
}
