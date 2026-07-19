<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $categories = ExpenseCategoryModel::where('company_id', $companyId)
            ->withCount('expenses')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('CMS/ExpenseCategories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'requires_approval' => 'boolean',
            'approval_limit' => 'nullable|numeric|min:0',
        ]);

        $companyId = $request->user()->cmsUser->company_id;

        // Check for duplicate
        $exists = ExpenseCategoryModel::where('company_id', $companyId)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A category with this name already exists.']);
        }

        $category = ExpenseCategoryModel::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'requires_approval' => $validated['requires_approval'] ?? false,
            'approval_limit' => $validated['approval_limit'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Expense category created successfully');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'requires_approval' => 'boolean',
            'approval_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;

        $category = ExpenseCategoryModel::where('company_id', $companyId)
            ->findOrFail($id);

        // Check for duplicate name (excluding current category)
        $exists = ExpenseCategoryModel::where('company_id', $companyId)
            ->where('name', $validated['name'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A category with this name already exists.']);
        }

        $category->update($validated);

        return back()->with('success', 'Expense category updated successfully');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $category = ExpenseCategoryModel::where('company_id', $companyId)
            ->withCount('expenses')
            ->findOrFail($id);

        if ($category->expenses_count > 0) {
            return back()->withErrors(['error' => 'Cannot delete category with existing expenses.']);
        }

        $category->delete();

        return back()->with('success', 'Expense category deleted successfully');
    }
}
