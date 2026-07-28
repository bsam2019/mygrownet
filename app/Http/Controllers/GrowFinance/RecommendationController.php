<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AutomatedRecommendationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecommendationController extends Controller
{
    public function __construct(
        private AutomatedRecommendationService $recommendationService,
    ) {}

    public function index(Request $request)
    {
        $businessId = $request->user()->id;
        $recommendations = $this->recommendationService->generateAll($businessId);

        return Inertia::render('GrowFinance/Reports/Recommendations', [
            'recommendations' => $recommendations,
        ]);
    }
}
