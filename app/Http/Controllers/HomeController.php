<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;
use App\Application\UseCases\Module\GetUserModulesUseCase;

class HomeController extends Controller
{
    public function __construct(
        private GetUserModulesUseCase $getUserModulesUseCase
    ) {}

    public function index()
    {
        // Get featured modules for public display
        $moduleDTOs = $this->getUserModulesUseCase->execute(null);
        
        // Filter to only featured/primary modules
        $featuredSlugs = ['bizboost', 'growfinance', 'growbiz', 'marketplace'];
        $featuredModules = array_filter(
            array_map(fn($dto) => $dto->toArray(), $moduleDTOs),
            fn($module) => in_array($module['slug'], $featuredSlugs)
        );
        
        // Sort by the order defined
        usort($featuredModules, function($a, $b) use ($featuredSlugs) {
            return array_search($a['slug'], $featuredSlugs) - array_search($b['slug'], $featuredSlugs);
        });

        return Inertia::render('Welcome', [
            'categories' => Category::all(),
            'featuredApps' => array_values($featuredModules),
        ]);
    }

    public function about()
    {
        return Inertia::render('About');
    }

    public function investment()
    {
        return Inertia::render('Investment', [
            'categories' => Category::with('investments')->get()
        ]);
    }

    public function contact()
    {
        return Inertia::render('Contact');
    }

    public function privacy()
    {
        return Inertia::render('Privacy');
    }

    public function terms()
    {
        return Inertia::render('Terms');
    }
}
