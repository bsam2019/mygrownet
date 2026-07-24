<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\CategoryRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SetupController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private CategoryRepositoryInterface $categoryRepo,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $business = $this->businessService->getBusinessByUser($user->id);

        if ($business && $business->onboardingCompleted) {
            return redirect()->route('bizboost.dashboard');
        }

        return Inertia::render('BizBoost/Setup/Index', [
            'business' => $business?->toArray(),
            'industries' => config('modules.bizboost.industry_kits', []),
            'subscriptionTier' => $request->get('subscription_tier', 'free'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        if ($this->businessService->getBusinessByUser($user->id)) {
            return redirect()->route('bizboost.dashboard')
                ->with('info', 'You already have a business profile set up.');
        }

        $this->businessService->createBusiness($user->id, $validated);

        return redirect()->route('bizboost.dashboard')
            ->with('success', 'Welcome to BizBoost! Your business profile has been created.');
    }

    public function complete(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->businessService->completeOnboarding($business->id);

        return redirect()->route('bizboost.dashboard')
            ->with('success', 'Setup complete! Welcome to BizBoost.');
    }
}