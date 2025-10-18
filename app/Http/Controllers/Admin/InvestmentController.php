<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvestmentController extends Controller
{
    // public function __construct()
    // {
    //     $this->authorizeResource(Investment::class, 'investment');
    // }

    public function index(Request $request)
    {
        $query = Investment::with(['user', 'category'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest();

        $summary = [
            'total' => Investment::sum('amount'),
            'active' => Investment::where('status', 'active')->sum('amount'),
            'pending' => Investment::where('status', 'pending')->sum('amount')
        ];

        return Inertia::render('Admin/Investments/Index', [
            'investments' => $query->paginate(10),
            'filters' => $request->only('status'),
            'summary' => $summary
        ]);
    }

    public function show(Investment $investment)
    {
        return Inertia::render('Admin/Investments/Show', [
            'investment' => $investment->load(['user', 'category']),
            'returns' => $investment->profitShares()
                ->latest()
                ->paginate(10)
        ]);
    }

    public function approve(Investment $investment)
    {
        $this->authorize('approve', $investment);

        try {
            DB::beginTransaction();

            $investment->update([
                'status' => 'active',
                'approved_at' => Carbon::now(),
                'approved_by' => auth()->id()
            ]);

            // Generate first profit share schedule
            $investment->generateProfitShareSchedule();

            DB::commit();

            return back()->with('success', 'Investment approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve investment');
        }
    }

    public function reject(Request $request, Investment $investment)
    {
        $this->authorize('reject', $investment);

        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $investment->update([
                'status' => 'rejected',
                'rejected_at' => Carbon::now(),
                'rejected_by' => auth()->id(),
                'rejection_reason' => $validated['reason']
            ]);

            // Notify user about rejection
            $investment->user->notify(new InvestmentRejected($investment));

            DB::commit();

            return back()->with('success', 'Investment rejected successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject investment');
        }
    }
}
