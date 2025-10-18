<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Notifications\InvestmentRejected;

class AdminInvestmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Investment::with(['user', 'category'])
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                return $query->where('category_id', $request->category);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                return $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                })->orWhere('amount', 'like', "%{$request->search}%")
                  ->orWhereHas('category', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                });
            })
            ->latest();

        $categories = Category::all();

        $summary = [
            'total' => Investment::sum('amount'),
            'active' => Investment::where('status', 'active')->sum('amount'),
            'pending' => Investment::where('status', 'pending')->sum('amount')
        ];

        return Inertia::render('Admin/Investments/Index', [
            'investments' => $query->paginate(10),
            'filters' => $request->only(['status', 'category', 'search']),
            'categories' => $categories,
            'summary' => $summary
        ]);
    }

    public function show($id)
    {
        $investment = Investment::with(['user', 'category', 'profitShares'])
            ->findOrFail($id);

        // Ensure category and user data is available
        $category = $investment->category ?? (object)[
            'id' => null,
            'name' => 'N/A',
            'risk_level' => 'N/A',
            'return_rate' => 0
        ];

        $user = $investment->user ?? (object)[
            'id' => null,
            'name' => 'N/A',
            'email' => 'N/A'
        ];

        // Format dates
        $investmentDate = $investment->investment_date instanceof \Carbon\Carbon 
            ? $investment->investment_date 
            : \Carbon\Carbon::parse($investment->investment_date);
            
        $lockInEnd = $investment->lock_in_period_end instanceof \Carbon\Carbon 
            ? $investment->lock_in_period_end 
            : \Carbon\Carbon::parse($investment->lock_in_period_end);
            
        $nextPaymentDate = $investment->next_payment_date instanceof \Carbon\Carbon 
            ? $investment->next_payment_date 
            : ($investment->next_payment_date ? \Carbon\Carbon::parse($investment->next_payment_date) : null);

        return Inertia::render('Admin/Investments/Show', [
            'investment' => array_merge($investment->toArray(), [
                'formatted_amount' => number_format($investment->amount, 2),
                'formatted_expected_return' => number_format($investment->expected_return, 2),
                'formatted_investment_date' => $investmentDate->format('M d, Y'),
                'formatted_lock_in_end' => $lockInEnd->format('M d, Y'),
                'status_badge' => $this->getStatusBadge($investment->status),
                'category' => $category,
                'user' => $user
            ]),
            'metrics' => $investment->getPerformanceMetrics()
        ]);
    }

    private function getStatusBadge($status)
    {
        return match($status) {
            'pending' => ['color' => 'yellow', 'text' => 'Pending Approval'],
            'active' => ['color' => 'green', 'text' => 'Active'],
            'withdrawn' => ['color' => 'gray', 'text' => 'Withdrawn'],
            'terminated' => ['color' => 'red', 'text' => 'Terminated'],
            default => ['color' => 'gray', 'text' => ucfirst($status)]
        };
    }

    public function approve($id)
    {
        try {
            DB::beginTransaction();

            $investment = Investment::findOrFail($id);
            
            if ($investment->status !== 'pending') {
                return back()->with('error', 'Only pending investments can be approved');
            }

            $investment->update([
                'status' => 'active',
                'approved_at' => now(),
                'next_payment_date' => now()->addDays(30)
            ]);

            DB::commit();

            return back()->with('success', 'Investment approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve investment');
        }
    }

    public function reject(Investment $investment)
    {
        try {
            DB::beginTransaction();

            if ($investment->status !== 'pending') {
                return back()->with('error', 'Only pending investments can be rejected.');
            }

            $validated = request()->validate([
                'reason' => 'required|string|max:500'
            ]);

            $investment->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $validated['reason']
            ]);

            // Notify the user about the rejection
            $investment->user->notify(new InvestmentRejected($investment));

            DB::commit();

            return back()->with('success', 'Investment rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject investment. ' . $e->getMessage());
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:investments,id'
        ]);

        try {
            DB::beginTransaction();

            $investments = Investment::whereIn('id', $request->ids)
                ->where('status', 'pending')
                ->get();

            foreach ($investments as $investment) {
                $investment->update([
                    'status' => 'active',
                    'approved_at' => now(),
                    'next_payment_date' => now()->addDays(30)
                ]);
            }

            DB::commit();

            return back()->with('success', 'Selected investments approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve investments');
        }
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:investments,id'
        ]);

        try {
            DB::beginTransaction();

            $investments = Investment::whereIn('id', $request->ids)
                ->where('status', 'pending')
                ->get();

            foreach ($investments as $investment) {
                // Delete payment proof files
                if ($investment->payment_proof) {
                    Storage::disk('public')->delete($investment->payment_proof);
                }

                $investment->update([
                    'status' => 'rejected',
                    'rejected_at' => now()
                ]);
            }

            DB::commit();

            return back()->with('success', 'Selected investments rejected successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject investments');
        }
    }

    public function bulkExport(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:investments,id'
        ]);

        $investments = Investment::with(['user', 'category'])
            ->whereIn('id', $validated['ids'])
            ->get()
            ->map(function ($investment) {
                return [
                    'id' => $investment->id,
                    'user' => $investment->user->name,
                    'amount' => $investment->amount,
                    'category' => $investment->category?->name,
                    'status' => $investment->status,
                    'created_at' => $investment->created_at->format('Y-m-d H:i:s'),
                    'expected_return' => $investment->expected_return,
                    'roi' => $investment->roi
                ];
            });

        return response()->json($investments);
    }
}
