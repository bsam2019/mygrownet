<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Models\GrowStart\PartnerProvider;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    public function __construct(
        private JourneyRepositoryInterface $journeyRepository
    ) {}

    public function index(Request $request): Response
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        $query = PartnerProvider::query();

        // Filter by country
        if ($journey) {
            $query->forCountry($journey->getCountryId());
        } elseif ($request->has('country_id')) {
            $query->forCountry($request->country_id);
        }

        $providers = $query->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->get();

        $categories = [
            ['value' => 'accountant', 'label' => 'Accountants'],
            ['value' => 'designer', 'label' => 'Designers'],
            ['value' => 'pacra_agent', 'label' => 'PACRA Agents'],
            ['value' => 'marketing', 'label' => 'Marketing'],
            ['value' => 'legal', 'label' => 'Legal'],
            ['value' => 'supplier', 'label' => 'Suppliers'],
            ['value' => 'consultant', 'label' => 'Consultants'],
        ];

        // Get unique provinces from providers
        $provinces = $providers->pluck('province')->unique()->filter()->sort()->values()->toArray();

        return Inertia::render('GrowStart/Providers/Index', [
            'providers' => $providers,
            'categories' => $categories,
            'provinces' => $provinces,
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $provider = PartnerProvider::with('country')->findOrFail($id);

        return Inertia::render('GrowStart/Directory/ProviderDetail', [
            'provider' => $provider,
        ]);
    }

    // API Methods
    public function apiIndex(Request $request): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        $query = PartnerProvider::query();

        if ($journey) {
            $query->forCountry($journey->getCountryId());
        }

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        $providers = $query->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->get();

        return response()->json(['data' => $providers]);
    }
}
