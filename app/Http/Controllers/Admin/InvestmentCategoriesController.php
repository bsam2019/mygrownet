<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvestmentCategoriesController extends Controller
{
    public function index()
    {

        return Inertia::render('Admin/InvestmentCategories/Index', [
            'categories' => InvestmentCategory::query()
                ->orderBy('name')
                ->paginate(10)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_investment' => 'required|numeric|min:0',
            'max_investment' => 'nullable|numeric|gt:min_investment',
            'interest_rate' => 'required|numeric|between:0,100',
            'expected_roi' => 'required|numeric|between:0,100',
            'lock_in_period' => 'required|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        InvestmentCategory::create($validated);

        return redirect()->back()->with('success', 'Category created successfully');
    }

    public function update(Request $request, InvestmentCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_investment' => 'required|numeric|min:0',
            'max_investment' => 'nullable|numeric|gt:min_investment',
            'interest_rate' => 'required|numeric|between:0,100',
            'expected_roi' => 'required|numeric|between:0,100',
            'lock_in_period' => 'required|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully');
    }

    public function destroy(InvestmentCategory $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully');
    }
}
