<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\CategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/Categories/Index', [
            'categories' => $this->categoryService->getTree($companyId),
            'flatCategories' => array_map(fn($c) => $c->toArray(), $this->categoryService->getCategories($companyId)),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:sa_categories,id',
            'sort_order' => 'nullable|integer',
        ]);
        $this->categoryService->createCategory($companyId, $validated);
        return redirect()->back()->with('success', 'Category created.');
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:sa_categories,id',
            'sort_order' => 'nullable|integer',
        ]);
        $this->categoryService->updateCategory($id, $companyId, $validated);
        return redirect()->back()->with('success', 'Category updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $this->categoryService->deleteCategory($id, $companyId);
        return redirect()->back()->with('success', 'Category deleted.');
    }
}
