<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::where('is_active', true)
            ->withCount('activeProducts')
            ->orderBy('sort_order')
            ->get();

        $products = Product::with('category')
            ->active()
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->paginate(12);

        return Inertia::render('Shop/Index', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    public function show(Product $product)
    {
        $product->load('category');
        
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->limit(4)
            ->get();

        return Inertia::render('Shop/ProductDetail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function category(ProductCategory $category)
    {
        $products = Product::where('category_id', $category->id)
            ->active()
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->paginate(12);

        return Inertia::render('Shop/Category', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
