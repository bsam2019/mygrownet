<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\InvestmentResource;
use App\Http\Resources\CategoryResource;

class InvestmentController extends Controller
{
    public function index()
    {
        $investments = auth()->user()->investments()
            ->with(['category'])
            ->latest()
            ->paginate(10);

        return InvestmentResource::collection($investments);
    }

    public function categories()
    {
        $categories = Category::where('is_active', true)->get();
        return CategoryResource::collection($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:5000',
            'category_id' => 'required|exists:categories,id',
            'payment_proof' => 'required|image|max:2048'
        ]);

        $investment = auth()->user()->investments()->create([
            'amount' => $validated['amount'],
            'category_id' => $validated['category_id'],
            'status' => 'pending',
            'payment_proof' => $request->file('payment_proof')->store('payment_proofs', 'public'),
            'expected_return' => $this->calculateExpectedReturn($validated['amount'])
        ]);

        return new InvestmentResource($investment->load('category'));
    }

    public function show(Investment $investment)
    {
        $this->authorize('view', $investment);
        return new InvestmentResource($investment->load(['category', 'profitShares']));
    }

    private function calculateExpectedReturn($amount)
    {
        // Return rates based on investment tiers (ZMW)
        $rate = match(true) {
            $amount >= 50000 => 0.45, // Elite tier: 45%
            $amount >= 25000 => 0.35, // Leader tier: 35%
            $amount >= 10000 => 0.25, // Builder tier: 25%
            $amount >= 5000 => 0.20,  // Starter tier: 20%
            default => 0.15           // Basic tier: 15%
        };

        return $amount * $rate;
    }
}
