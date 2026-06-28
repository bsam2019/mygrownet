<?php

namespace App\Http\Controllers\Investment;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\InvestmentOpportunity;
use Illuminate\Support\Facades\Storage;
use App\Models\InvestmentCategory;

class InvestmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Investment/Index', [
            'investments' => auth()->user()->investments()
                ->with('category')
                ->latest()
                ->paginate(10),
            'categories' => Category::where('is_active', true)->get()
        ]);
    }

    public function create(?InvestmentOpportunity $opportunity = null)
    {
        if ($opportunity) {
            $opportunity->load('category');
        }
        
        $categories = InvestmentCategory::where('is_active', true)->get();
        
        return Inertia::render('Investments/Create', [
            'categories' => $categories,
            'opportunity' => $opportunity
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:investment_categories,id',
            'amount' => 'required|numeric|min:0',
            'payment_proof' => 'required|image|max:2048',
            'opportunity_id' => 'nullable|exists:investment_opportunities,id'
        ]);

        // If opportunity_id is provided, get the opportunity details
        if ($request->has('opportunity_id')) {
            $opportunity = InvestmentOpportunity::findOrFail($request->opportunity_id);
            $validated['category_id'] = $opportunity->category_id;
        }

        // Get the investment category
        $category = InvestmentCategory::findOrFail($validated['category_id']);

        // Validate amount against category limits
        if ($validated['amount'] < $category->min_investment) {
            return back()->withErrors(['amount' => "Minimum investment amount is {$category->min_investment}"]);
        }
        if ($validated['amount'] > $category->max_investment) {
            return back()->withErrors(['amount' => "Maximum investment amount is {$category->max_investment}"]);
        }

        // Store the payment proof
        $path = $request->file('payment_proof')->store('payment-proofs', 'public');

        // Create the investment
        $investment = Investment::create([
            'user_id' => auth()->id(),
            'category_id' => $validated['category_id'],
            'amount' => $validated['amount'],
            'payment_proof' => $path,
            'status' => 'pending',
            'opportunity_id' => $validated['opportunity_id'] ?? null,
            'expected_return' => $validated['amount'] * (1 + ($category->return_rate / 100)),
            'investment_date' => now(),
            'lock_in_period_end' => now()->addYear()
        ]);

        // Notify admin about new investment
        // TODO: Implement admin notification

        return redirect()->route('investments.index')
            ->with('success', 'Investment request submitted successfully. Please wait for admin approval.');
    }

    public function show(Investment $investment)
    {
        // Load relationships and calculate metrics
        $investment->load(['category', 'user']);
        
        // Ensure dates are Carbon instances
        $investmentDate = $investment->investment_date instanceof \Carbon\Carbon 
            ? $investment->investment_date 
            : \Carbon\Carbon::parse($investment->investment_date);
            
        $lockInEnd = $investment->lock_in_period_end instanceof \Carbon\Carbon 
            ? $investment->lock_in_period_end 
            : \Carbon\Carbon::parse($investment->lock_in_period_end);
            
        $nextPaymentDate = $investment->next_payment_date instanceof \Carbon\Carbon 
            ? $investment->next_payment_date 
            : ($investment->next_payment_date ? \Carbon\Carbon::parse($investment->next_payment_date) : null);
        
        $metrics = [
            'current_value' => $investment->getCurrentValue(),
            'roi' => $investment->getRoiAttribute(),
            'growth_rate' => $investment->getGrowthRate(),
            'risk_adjusted_return' => $investment->getRiskAdjustedReturn(),
            'days_remaining' => $lockInEnd ? now()->diffInDays($lockInEnd, false) : 0,
            'total_earned' => $investment->profitShares()->sum('amount'),
            'next_payout' => $nextPaymentDate ? $nextPaymentDate->format('M d, Y') : null
        ];

        $performanceHistory = $investment->profitShares()
            ->latest()
            ->take(12)
            ->get()
            ->map(function ($share) {
                return [
                    'date' => $share->created_at->format('M d, Y'),
                    'amount' => $share->amount,
                    'type' => $share->type
                ];
            });

        // Ensure category and user data is available
        $category = $investment->category ?? (object)[
            'id' => null,
            'name' => 'N/A',
            'risk_level' => 'N/A'
        ];

        $user = $investment->user ?? (object)[
            'id' => null,
            'name' => 'N/A',
            'email' => 'N/A'
        ];

        return Inertia::render('Investment/Show', [
            'investment' => array_merge($investment->toArray(), [
                'formatted_amount' => number_format($investment->amount, 2),
                'formatted_expected_return' => number_format($investment->expected_return, 2),
                'formatted_investment_date' => $investmentDate->format('M d, Y'),
                'formatted_lock_in_end' => $lockInEnd->format('M d, Y'),
                'status_badge' => $this->getStatusBadge($investment->status),
                'category' => $category,
                'user' => $user
            ]),
            'metrics' => $metrics,
            'performanceHistory' => $performanceHistory,
            'canWithdraw' => $investment->status === 'active' && now()->gt($lockInEnd)
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

    public function update(Request $request, Investment $investment)
    {
        $this->authorize('update', $investment);

        if ($investment->status !== 'pending') {
            return back()->with('error', 'Only pending investments can be updated');
        }

        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:5000',
                function ($attribute, $value, $fail) use ($request) {
                    $category = Category::find($request->category_id);
                    if ($category && $category->max_investment && $value > $category->max_investment) {
                        $fail("The investment amount exceeds the maximum allowed for this category.");
                    }
                }
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $category = Category::find($value);
                    if (!$category->is_active) {
                        $fail("This investment category is currently not available.");
                    }
                }
            ]
        ]);

        try {
            DB::beginTransaction();

            $investment->update([
                'amount' => $validated['amount'],
                'category_id' => $validated['category_id'],
                'expected_return' => $this->calculateExpectedReturn($validated['amount'])
            ]);

            DB::commit();

            return back()->with('success', 'Investment updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update investment');
        }
    }

    public function destroy(Investment $investment)
    {
        $this->authorize('delete', $investment);

        try {
            if ($investment->status !== 'pending') {
                return back()->with('error', 'Only pending investments can be deleted');
            }

            // Delete payment proof file
            if ($investment->payment_proof) {
                Storage::disk('public')->delete($investment->payment_proof);
            }

            $investment->delete();
            return redirect()->route('dashboard.investments')
                ->with('success', 'Investment deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete investment');
        }
    }

    private function calculateExpectedReturn($amount)
    {
        // Calculate based on investment tier
        $rate = match(true) {
            $amount >= 50000 => 0.45, // 45% for Elite
            $amount >= 25000 => 0.35, // 35% for Leader
            $amount >= 10000 => 0.25, // 25% for Builder
            $amount >= 5000 => 0.20,  // 20% for Starter
            default => 0.15 // 15% for Basic
        };

        return $amount * $rate;
    }

    public function opportunities()
    {
        return Inertia::render('Investment/Opportunities/Index', [
            'opportunities' => InvestmentOpportunity::where('status', 'active')
                ->with('category')
                ->latest()
                ->paginate(10)
        ]);
    }

    public function showOpportunity(InvestmentOpportunity $opportunity)
    {
        return Inertia::render('Investment/Opportunities/Show', [
            'opportunity' => $opportunity->load('category')
        ]);
    }

    public function portfolio()
    {
        $user = auth()->user();
        
        // Get portfolio data
        $portfolio = [
            'total_investment' => $user->investments()->where('status', 'active')->sum('amount'),
            'total_returns' => $user->transactions()->where('transaction_type', 'return')->where('status', 'completed')->sum('amount'),
            'active_referrals' => $user->directReferrals()->whereHas('investments', function($query) {
                $query->where('status', 'active');
            })->count(),
            'referral_earnings' => $user->transactions()->where('transaction_type', 'referral')->where('status', 'completed')->sum('amount')
        ];

        return Inertia::render('Investors/Portfolio/index', [
            'portfolio' => $portfolio,
            'investments' => [
                'data' => $user->investments()
                    ->with('category')
                    ->where('status', 'active')
                    ->latest()
                    ->get()
                    ->map(function ($investment) {
                        return [
                            'id' => $investment->id,
                            'amount' => $investment->amount,
                            'returns' => $investment->returns,
                            'created_at' => $investment->created_at->format('M d, Y'),
                            'category' => [
                                'id' => $investment->category->id,
                                'name' => $investment->category->name,
                                'risk_level' => $investment->category->risk_level,
                                'duration' => $investment->category->duration
                            ]
                        ];
                    }),
                'meta' => [
                    'next_payment_date' => $user->investments()
                        ->where('status', 'active')
                        ->orderBy('next_payment_date')
                        ->value('next_payment_date')
                ]
            ]
        ]);
    }
}
