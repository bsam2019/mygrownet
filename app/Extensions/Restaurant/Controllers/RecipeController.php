<?php

namespace App\Extensions\Restaurant\Controllers;

use App\Http\Controllers\Controller;
use App\Extensions\Restaurant\Services\RestaurantService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecipeController extends Controller
{
    public function __construct(private RestaurantService $service) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/Restaurant/Recipes/Index', [
            'recipes' => $this->service->getRecipes($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'sa_item_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'yield_quantity' => 'required|numeric|min:0',
            'yield_uom' => 'nullable|string|max:20',
            'ingredients' => 'nullable|array',
            'ingredients.*.sa_item_id' => 'required|integer',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.uom' => 'nullable|string|max:20',
            'ingredients.*.waste_factor' => 'nullable|numeric|min:0|max:100',
            'instructions' => 'nullable|string',
        ]);
        $this->service->createRecipe($companyId, $validated['sa_item_id'], $validated['name'], $validated['yield_quantity'], $validated['ingredients'] ?? []);
        return redirect()->back()->with('success', 'Recipe created.');
    }
}
