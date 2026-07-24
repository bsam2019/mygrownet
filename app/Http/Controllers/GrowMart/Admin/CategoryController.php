<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Repositories\CategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function index()
    {
        $categories = $this->categoryRepository->findAll(['per_page' => 20]);

        return Inertia::render('GrowMart/Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $parentCategories = $this->categoryRepository->findParentCategories();

        return Inertia::render('GrowMart/Admin/Categories/Create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:growmart_categories,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:growmart_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('growmart/categories', 'public');
        } elseif ($request->filled('image_url')) {
            $validated['image'] = $request->image_url;
        } else {
            $validated['image'] = null;
        }

        unset($validated['image_url']);

        $this->categoryRepository->save($validated);

        return redirect()->route('admin.growmart.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(int $id)
    {
        $category = $this->categoryRepository->findById($id);

        $parentCategories = array_values(array_filter(
            $this->categoryRepository->findParentCategories(),
            fn($c) => $c['id'] !== $id
        ));

        return Inertia::render('GrowMart/Admin/Categories/Edit', [
            'category' => $category,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:growmart_categories,slug,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:growmart_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $category = $this->categoryRepository->findById($id);

        if ($request->hasFile('image')) {
            if ($category['image'] && !str_starts_with($category['image'], 'http')) {
                Storage::disk('public')->delete($category['image']);
            }
            $validated['image'] = $request->file('image')->store('growmart/categories', 'public');
        } elseif ($request->filled('image_url')) {
            $validated['image'] = $request->image_url;
        } else {
            unset($validated['image']);
        }

        unset($validated['image_url']);

        $this->categoryRepository->update($id, $validated);

        return redirect()->route('admin.growmart.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(int $id)
    {
        if ($this->categoryRepository->productCount($id) > 0) {
            return back()->with('error', 'Cannot delete category with existing products.');
        }

        $category = $this->categoryRepository->findById($id);
        if ($category['image'] && !str_starts_with($category['image'], 'http')) {
            Storage::disk('public')->delete($category['image']);
        }

        $this->categoryRepository->delete($id);

        return redirect()->route('admin.growmart.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
