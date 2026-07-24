<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Domain\GrowStart\Repositories\ProviderRepositoryInterface;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    public function __construct(
        private ProviderRepositoryInterface $providerRepo,
        private JourneyRepositoryInterface $journeyRepository
    ) {}

    public function index(Request $request): Response
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);
        $countryId = $journey ? $journey->getCountryId() : null;

        $providers = $this->providerRepo->findAll(
            $countryId ?? ($request->has('country_id') ? (int) $request->country_id : null)
        );

        $categories = [
            ['value' => 'accountant', 'label' => 'Accountants'],
            ['value' => 'designer', 'label' => 'Designers'],
            ['value' => 'pacra_agent', 'label' => 'PACRA Agents'],
            ['value' => 'marketing', 'label' => 'Marketing'],
            ['value' => 'legal', 'label' => 'Legal'],
            ['value' => 'supplier', 'label' => 'Suppliers'],
            ['value' => 'consultant', 'label' => 'Consultants'],
        ];

        $provinces = $providers->pluck('province')->unique()->filter()->sort()->values()->toArray();

        return Inertia::render('GrowStart/Providers/Index', [
            'providers' => $providers,
            'categories' => $categories,
            'provinces' => $provinces,
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $provider = $this->providerRepo->findById($id);

        if (!$provider) {
            abort(404);
        }

        return Inertia::render('GrowStart/Directory/ProviderDetail', [
            'provider' => $provider,
        ]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);
        $countryId = $journey ? $journey->getCountryId() : null;

        $providers = $this->providerRepo->findAll(
            $countryId,
            $request->has('category') ? $request->category : null
        );

        return response()->json(['data' => $providers]);
    }
}